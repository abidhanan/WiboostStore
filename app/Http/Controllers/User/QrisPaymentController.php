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
    public function show(
        Request $request,
        string $invoice_number,
        TransactionPaymentSyncService $transactionSyncService,
        TripayDepositSyncService $depositSyncService
    )
    {
        $transaction = Transaction::with('product')
            ->where('invoice_number', $invoice_number)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($transaction) {
            if (in_array($transaction->payment_status, ['unpaid', 'pending'], true)) {
                $this->syncOnce(
                    'qris-transaction-show:' . $transaction->id,
                    fn () => $transactionSyncService->sync($transaction)
                );
                $transaction->refresh();
            }

            return $this->showTransaction($transaction);
        }

        $deposit = Deposit::where('invoice_number', $invoice_number)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (in_array($deposit->payment_status, ['unpaid', 'pending'], true)) {
            $this->syncOnce(
                'qris-deposit-show:' . $deposit->id,
                fn () => $depositSyncService->syncDepositFromProvider($deposit)
            );
            $deposit->refresh();
        }

        return $this->showDeposit($deposit);
    }

    protected function showTransaction(Transaction $transaction)
    {
        $raw = (array) ($transaction->response_data ?? []);
        $isMembershipPayment = $transaction->isMembershipPayment();

        return view('user.qris.show', [
            'kind' => 'transaction',
            'invoice' => $transaction->invoice_number,
            'title' => $transaction->product?->name ?: ($isMembershipPayment ? 'Membership Wiboost 30 Hari' : 'Pesanan Wiboost'),
            'baseAmount' => (int) round((float) $transaction->amount),
            'adminFee' => $transaction->tripay_admin_fee,
            'totalPayable' => $transaction->display_total_payable,
            'status' => $transaction->cancelled_due_to_qris_expiry ? 'cancelled' : ($transaction->payment_status ?: 'pending'),
            'qrisImageUrl' => $this->resolveQrisImageUrl($raw),
            'qrisText' => $this->resolveQrisText($raw),
            'expiresAt' => $transaction->qris_expires_at,
            'backUrl' => $isMembershipPayment ? route('user.membership.index') : route('user.history'),
            'backLabel' => $isMembershipPayment ? 'Kembali ke Membership' : 'Cek Riwayat',
            'statusUrl' => route('user.qris.status', $transaction->invoice_number),
            'redirectUrl' => $isMembershipPayment ? route('user.membership.index') : route('user.history', ['auto_open' => $transaction->id]),
        ]);
    }

    protected function showDeposit(Deposit $deposit)
    {
        $raw = (array) ($deposit->payment_response ?? []);
        $isMembershipPayment = $deposit->isMembershipPayment();

        return view('user.qris.show', [
            'kind' => 'deposit',
            'invoice' => $deposit->invoice_number,
            'title' => $isMembershipPayment ? 'Membership Wiboost 30 Hari' : 'Top Up Saldo Wiboost',
            'baseAmount' => (int) round((float) $deposit->amount),
            'adminFee' => $deposit->tripay_admin_fee,
            'totalPayable' => $deposit->tripay_total_payable,
            'status' => $deposit->payment_status ?: 'pending',
            'qrisImageUrl' => $this->resolveQrisImageUrl($raw),
            'qrisText' => $this->resolveQrisText($raw),
            'expiresAt' => $this->resolveExpiresAt($raw, $deposit->created_at),
            'backUrl' => $isMembershipPayment ? route('user.membership.index') : route('user.wallet.index'),
            'backLabel' => $isMembershipPayment ? 'Kembali ke Membership' : 'Kembali ke Dompet',
            'statusUrl' => route('user.qris.status', $deposit->invoice_number),
            'redirectUrl' => $isMembershipPayment ? route('user.membership.index') : route('user.dashboard'),
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
                redirectUrl: $transaction->isMembershipPayment() ? route('user.membership.index') : route('user.history', ['auto_open' => $transaction->id])
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
            redirectUrl: $deposit->isMembershipPayment() ? route('user.membership.index') : route('user.dashboard')
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
            'data.qris',
            'data.qris_url',
            'data.qris_image',
            'data.qris_image_url',
            'data.qris_base64',
            'data.qr',
            'data.qrcode',
            'data.qrCode',
            'data.qr_url',
            'data.qr_image',
            'data.qr_image_url',
            'data.qr_image_base64',
            'data.qr_base64',
            'data.image',
            'data.image_url',
            'data.qris.image_url',
            'data.qris.url',
            'data.qris.image',
            'qris',
            'qris_url',
            'qris_image',
            'qris_image_url',
            'qris_base64',
            'qr',
            'qrcode',
            'qrCode',
            'qr_url',
            'qr_image',
            'qr_image_url',
            'qr_image_base64',
            'qr_base64',
            'image',
            'image_url',
            'qris.image_url',
            'qris.url',
            'qris.image',
        ]);

        if ($image) {
            $normalizedImage = $this->normalizeQrisImageSource($image);

            if ($normalizedImage) {
                return $normalizedImage;
            }
        }

        $text = $this->resolveQrisText($raw);

        if (! $text) {
            return null;
        }

        return 'https://api.qrserver.com/v1/create-qr-code/?size=420x420&margin=1&data=' . rawurlencode($text);
    }

    protected function normalizeQrisImageSource(string $value): ?string
    {
        $value = trim($value);

        if (preg_match('/^https?:\/\//i', $value) || preg_match('/^data:image\//i', $value)) {
            return $value;
        }

        if (str_starts_with($value, '//')) {
            return 'https:' . $value;
        }

        $compact = preg_replace('/\s+/', '', $value);

        if (preg_match('/^(iVBORw0KGgo|\/9j\/|R0lGOD|PHN2Z)/', $compact) === 1) {
            return 'data:image/png;base64,' . $compact;
        }

        return null;
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
            'data.qris',
            'data.qris_text',
            'data.qris_string',
            'data.raw_qris',
            'data.emv',
            'data.qr',
            'data.qrcode',
            'data.qrCode',
            'data.qr_string',
            'data.qr_content',
            'data.qr_code',
            'data.qris_data',
            'data.qris_payload',
            'raw_qris',
            'emv',
            'qr',
            'qrcode',
            'qrCode',
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

        $leafKeys = array_values(array_unique(array_map(function (string $key): string {
            $position = strrpos($key, '.');

            return $position === false ? $key : substr($key, $position + 1);
        }, $keys)));

        return $this->recursivePayloadValue($raw, $leafKeys);
    }

    protected function recursivePayloadValue(mixed $value, array $keys): ?string
    {
        if (! is_array($value)) {
            return null;
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $value)) {
                $normalized = $this->normalizePayloadValue($value[$key]);

                if ($normalized !== null) {
                    return $normalized;
                }
            }
        }

        foreach ($value as $item) {
            $normalized = $this->recursivePayloadValue($item, $keys);

            if ($normalized !== null) {
                return $normalized;
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

        foreach ([
            'qris_text',
            'qris_string',
            'qr_string',
            'qr_content',
            'qr_code',
            'raw_qris',
            'emv',
            'text',
            'string',
            'payload',
            'content',
            'code',
            'data',
            'url',
            'image_url',
            'image',
            'base64',
            'qr_image',
            'qris_image',
        ] as $key) {
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
