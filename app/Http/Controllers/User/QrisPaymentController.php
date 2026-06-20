<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Services\TransactionPaymentSyncService;
use App\Services\TripayDepositSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class QrisPaymentController extends Controller
{
    public function show(Request $request, string $invoice_number)
    {
        $transaction = Transaction::with('product')
            ->where('invoice_number', $invoice_number)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($transaction) {
            return $this->showTransaction($transaction);
        }

        $deposit = Deposit::where('invoice_number', $invoice_number)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return $this->showDeposit($deposit);
    }

    protected function showTransaction(Transaction $transaction)
    {
        $raw = (array) (
            data_get($transaction->response_data, 'xoftware_create_response')
            ?: data_get($transaction->response_data, 'tripay_create_response')
            ?: []
        );

        return view('user.qris.show', [
            'kind' => 'transaction',
            'invoice' => $transaction->invoice_number,
            'title' => $transaction->product?->name ?: 'Pesanan Wiboost',
            'baseAmount' => (int) round((float) $transaction->amount),
            'adminFee' => $transaction->tripay_admin_fee,
            'totalPayable' => $transaction->display_total_payable,
            'status' => $transaction->cancelled_due_to_qris_expiry ? 'cancelled' : ($transaction->payment_status ?: 'pending'),
            'qrisImageUrl' => $this->resolveQrisImageUrl($raw),
            'qrisText' => $this->resolveQrisText($raw),
            'expiresAt' => $transaction->qris_expires_at,
            'backUrl' => route('user.history'),
            'backLabel' => 'Cek Riwayat',
            'statusUrl' => route('user.qris.status', $transaction->invoice_number),
            'redirectUrl' => route('user.history', ['auto_open' => $transaction->id]),
        ]);
    }

    protected function showDeposit(Deposit $deposit)
    {
        $raw = (array) ($deposit->payment_response ?? []);

        return view('user.qris.show', [
            'kind' => 'deposit',
            'invoice' => $deposit->invoice_number,
            'title' => 'Top Up Saldo Wiboost',
            'baseAmount' => (int) round((float) $deposit->amount),
            'adminFee' => $deposit->tripay_admin_fee,
            'totalPayable' => $deposit->tripay_total_payable,
            'status' => $deposit->payment_status ?: 'pending',
            'qrisImageUrl' => $this->resolveQrisImageUrl($raw),
            'qrisText' => $this->resolveQrisText($raw),
            'expiresAt' => $this->resolveExpiresAt($raw, $deposit->created_at),
            'backUrl' => route('user.wallet.index'),
            'backLabel' => 'Kembali ke Dompet',
            'statusUrl' => route('user.qris.status', $deposit->invoice_number),
            'redirectUrl' => route('user.dashboard'),
        ]);
    }

    public function status(
        Request $request,
        string $invoice_number,
        TransactionPaymentSyncService $transactionSyncService,
        TripayDepositSyncService $depositSyncService
    ) {
        $transaction = Transaction::with('product')
            ->where('invoice_number', $invoice_number)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($transaction) {
            if (in_array($transaction->payment_status, ['unpaid', 'pending'], true)) {
                $this->syncOnce(
                    'qris-transaction-status:' . $transaction->id,
                    fn () => $transactionSyncService->sync($transaction)
                );
                $transaction->refresh();
            }

            return response()->json($this->statusPayload(
                status: $transaction->cancelled_due_to_qris_expiry ? 'cancelled' : (string) $transaction->payment_status,
                redirectUrl: route('user.history', ['auto_open' => $transaction->id])
            ));
        }

        $deposit = Deposit::where('invoice_number', $invoice_number)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (in_array($deposit->payment_status, ['unpaid', 'pending'], true)) {
            $this->syncOnce(
                'qris-deposit-status:' . $deposit->id,
                fn () => $depositSyncService->syncDepositFromProvider($deposit)
            );
            $deposit->refresh();
        }

        return response()->json($this->statusPayload(
            status: (string) $deposit->payment_status,
            redirectUrl: route('user.dashboard')
        ));
    }

    protected function syncOnce(string $key, callable $callback): void
    {
        if (! Cache::add($key, true, now()->addSeconds(5))) {
            return;
        }

        try {
            $callback();
        } finally {
            Cache::forget($key);
        }
    }

    protected function statusPayload(string $status, string $redirectUrl): array
    {
        $normalized = strtolower($status);
        $isSuccess = in_array($normalized, ['paid', 'settlement', 'success', 'berhasil'], true);
        $isFailed = in_array($normalized, ['failed', 'failure', 'error', 'expired', 'cancel', 'cancelled', 'canceled', 'gagal'], true);
        $isCancelled = in_array($normalized, ['expired', 'cancel', 'cancelled', 'canceled'], true);

        return [
            'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending'),
            'label' => $isSuccess ? 'Sukses' : ($isCancelled ? 'Batal' : ($isFailed ? 'Gagal' : 'Menunggu')),
            'redirect_url' => $isSuccess ? $redirectUrl : null,
        ];
    }

    protected function resolveQrisImageUrl(array $raw): ?string
    {
        $image = $this->firstPayloadValue($raw, [
            'data.qris_url',
            'data.qr_url',
            'data.qr_image',
            'data.qr_image_url',
            'data.image_url',
            'data.qris.image_url',
            'data.qris.url',
            'qris_url',
            'qr_url',
            'qr_image',
            'qr_image_url',
            'image_url',
            'qris.image_url',
            'qris.url',
        ]);

        if ($image && preg_match('/^https?:\/\//i', $image)) {
            return $image;
        }

        $text = $this->resolveQrisText($raw);

        if (! $text) {
            return null;
        }

        return 'https://api.qrserver.com/v1/create-qr-code/?size=420x420&margin=1&data=' . rawurlencode($text);
    }

    protected function resolveExpiresAt(array $raw, mixed $createdAt = null): ?Carbon
    {
        $expiresAt = $this->firstPayloadValue($raw, [
            'data.expires_at',
            'data.expired_at',
            'data.expiry_at',
            'data.expiry',
            'expires_at',
            'expired_at',
            'expiry_at',
            'expiry',
        ]);

        if ($expiresAt) {
            try {
                return Carbon::parse($expiresAt);
            } catch (\Throwable) {
                // Fall back to the local transaction timestamp below.
            }
        }

        if ($createdAt instanceof Carbon) {
            return $createdAt->copy()->addMinutes(max(1, (int) config('xoftware.expired_minutes', 60)));
        }

        return null;
    }

    protected function resolveQrisText(array $raw): ?string
    {
        $text = $this->firstPayloadValue($raw, [
            'data.qris_text',
            'data.qris_string',
            'data.qr_string',
            'data.qr_content',
            'data.qr_code',
            'data.qris_data',
            'data.qris_payload',
            'qris_text',
            'qris_string',
            'qr_string',
            'qr_content',
            'qr_code',
            'qris_data',
            'qris_payload',
            'data.qris',
            'qris',
        ]);

        if (! $text || preg_match('/^https?:\/\//i', $text)) {
            return null;
        }

        return $text;
    }

    protected function firstPayloadValue(array $raw, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->normalizePayloadValue(data_get($raw, $key));

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    protected function normalizePayloadValue(mixed $value): ?string
    {
        if (is_scalar($value) && filled((string) $value)) {
            return trim((string) $value);
        }

        if (! is_array($value)) {
            return null;
        }

        foreach (['text', 'string', 'payload', 'code', 'data', 'url', 'image_url'] as $key) {
            if (array_key_exists($key, $value)) {
                $normalized = $this->normalizePayloadValue($value[$key]);

                if ($normalized !== null) {
                    return $normalized;
                }
            }
        }

        foreach ($value as $item) {
            $normalized = $this->normalizePayloadValue($item);

            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }
}
