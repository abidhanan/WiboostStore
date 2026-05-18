<?php

namespace App\Services;

use App\Mail\OrderSuccessMail;
use App\Models\ProductCredential;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderFulfillmentService
{
    public function __construct(
        protected DigiflazzService $digiflazzService,
        protected OrderSosmedService $orderSosmedService,
        protected DiscordWebhookService $discordWebhookService,
    ) {
    }

    public function handlePaidTransaction(Transaction $transaction): void
    {
        $transaction->loadMissing('product.category', 'user');

        if ($transaction->payment_status !== 'paid') {
            return;
        }

        if (! $transaction->product) {
            $this->markAsFailed($transaction, 'Produk untuk transaksi ini tidak ditemukan.');

            return;
        }

        if ($transaction->product->process_type === 'api') {
            $this->handleApiOrderOnce($transaction);

            return;
        }

        match ($transaction->product->process_type) {
            'account', 'number' => $this->handleCredentialOrder($transaction),
            'manual' => $this->handleManualOrder($transaction),
            default => $this->markAsFailed($transaction, 'Tipe proses produk tidak dikenali.'),
        };
    }

    public function syncRecentProviderStatuses(?int $userId = null, int $limit = 5, int $cooldownSeconds = 60): int
    {
        $transactions = Transaction::query()
            ->with(['product', 'user'])
            ->where('payment_status', 'paid')
            ->whereIn('order_status', ['pending', 'processing'])
            ->when($userId !== null, fn ($query) => $query->where('user_id', $userId))
            ->whereHas('product', function ($query) {
                $query->where('process_type', 'api')
                    ->where(function ($providerQuery) {
                        $providerQuery->whereIn('provider_source', ['digiflazz', 'ordersosmed'])
                            ->orWhereIn('provider_id', ['digiflazz', 'ordersosmed']);
                    });
            })
            ->oldest('updated_at')
            ->limit(max(1, $limit))
            ->get();

        $synced = 0;

        foreach ($transactions as $transaction) {
            $lockKey = 'provider-status-sync:' . $transaction->id;

            if (! Cache::add($lockKey, true, now()->addSeconds(max(15, $cooldownSeconds)))) {
                continue;
            }

            $this->syncProviderStatus($transaction);
            $synced++;
        }

        return $synced;
    }

    public function assignCredential(Transaction $transaction, ProductCredential $credential): void
    {
        $transaction->loadMissing('product', 'user');
        $product = $transaction->product;
        $previousStatus = $transaction->order_status;

        $credential->increment('current_usage');

        $transaction->update([
            'order_status' => 'success',
            'credential_data' => [
                'email' => ($credential->data_1 !== '-' && $credential->data_1 !== null) ? $credential->data_1 : null,
                'password' => $credential->data_2,
                'profile' => $credential->data_3,
                'pin' => $credential->data_4,
                'link' => $credential->data_5,
                'tutorial_link' => $credential->tutorial_link,
                'needs_otp' => $credential->needs_otp,
                'type' => $product?->process_type,
            ],
            'target_notes' => $credential->needs_otp
                ? 'Pesanan ini membutuhkan bantuan admin atau link tutorial tambahan untuk OTP / akses login.'
                : 'Data pesanan berhasil dikirim ke akun kamu.',
        ]);

        $this->rewardUserIfNeeded($transaction, $previousStatus, 'success');
        $this->sendSuccessEmail($transaction, $previousStatus);
        $this->discordWebhookService->sendOrderAlert(
            $transaction,
            'Kredensial otomatis terkirim',
            'Pesanan akun atau nomor berhasil dikirim ke pelanggan.',
            4968121
        );

        if ($product && $product->stock_reminder > 0) {
            $availableStock = (int) ($product->fresh()->available_stock ?? 0);

            if ($availableStock <= $product->stock_reminder) {
                $this->discordWebhookService->sendLowStockAlert($product->fresh(), $availableStock);
            }
        }
    }

    public function markManualOrderCompleted(Transaction $transaction, ?string $notes = null): void
    {
        $previousStatus = $transaction->order_status;

        $transaction->loadMissing('product', 'user');
        $transaction->update([
            'order_status' => 'success',
            'target_notes' => $notes ?: 'Pesanan manual telah selesai diproses oleh admin.',
        ]);

        $this->rewardUserIfNeeded($transaction, $previousStatus, 'success');
        $this->sendSuccessEmail($transaction, $previousStatus);
        $this->discordWebhookService->sendOrderAlert(
            $transaction,
            'Pesanan manual diselesaikan',
            'Admin telah menandai pesanan manual sebagai selesai.',
            4968121
        );
    }

    public function markOrderSuccessful(Transaction $transaction, ?string $notes = null): void
    {
        $previousStatus = $transaction->order_status;

        $transaction->loadMissing('product', 'user');
        $transaction->update([
            'order_status' => 'success',
            'target_notes' => $notes ?: ($transaction->target_notes ?: 'Pesanan telah selesai diproses.'),
        ]);

        $this->rewardUserIfNeeded($transaction, $previousStatus, 'success');
        $this->sendSuccessEmail($transaction, $previousStatus);
        $this->discordWebhookService->sendOrderAlert(
            $transaction,
            'Pesanan ditandai sukses',
            'Pesanan telah selesai diproses dan email sukses dikirim ke pelanggan.',
            4968121
        );
    }

    public function syncProviderStatus(Transaction $transaction): string
    {
        $transaction->loadMissing('product', 'user');
        $product = $transaction->product;

        if (! $product || $product->process_type !== 'api') {
            return 'skipped';
        }

        $providerSource = $product->provider_source ?: $product->provider_id;

        if ($providerSource === 'digiflazz') {
            $response = $this->digiflazzService->checkOrderStatus(
                (string) $product->provider_product_id,
                (string) $transaction->provider_customer_no,
                (string) $transaction->invoice_number
            );
        } elseif ($providerSource === 'ordersosmed') {
            $providerOrderId = $transaction->provider_order_id;

            if (! $providerOrderId) {
                return 'missing_provider_order_id';
            }

            $response = $this->orderSosmedService->checkOrderStatus($providerOrderId);
        } else {
            return 'unsupported_provider';
        }

        $responseData = array_merge($transaction->response_data ?? [], [
            'provider_order_id' => $response['provider_order_id'] ?? $transaction->provider_order_id,
            'provider_order_status' => $response['order_status'] ?? null,
            'provider_status_message' => $response['message'] ?? null,
            'raw' => $response['raw'] ?? $response,
            'last_status_check_at' => now()->toDateTimeString(),
        ]);

        $transaction->update([
            'response_data' => $responseData,
            'target_notes' => $response['message'] ?? $transaction->target_notes,
        ]);

        $nextStatus = $response['order_status'] ?? 'processing';

        if ($nextStatus === 'success') {
            $this->markOrderSuccessful($transaction->fresh(['product', 'user']), $response['message'] ?? null);

            return 'success';
        }

        if ($nextStatus === 'failed') {
            $this->markAsFailed($transaction->fresh(['product', 'user']), $response['message'] ?? 'Provider menandai pesanan gagal.', $response['raw'] ?? $response);

            return 'failed';
        }

        if ($transaction->order_status !== 'processing') {
            $transaction->update([
                'order_status' => 'processing',
                'target_notes' => $response['message'] ?? $transaction->target_notes,
            ]);
        }

        return 'processing';
    }

    protected function handleApiOrder(Transaction $transaction): void
    {
        if (in_array($transaction->order_status, ['success', 'failed'], true)) {
            return;
        }

        if ($transaction->provider_order_id || $transaction->order_status === 'processing') {
            $this->syncProviderStatus($transaction);

            return;
        }

        $product = $transaction->product;
        $providerSource = $product->provider_source ?: $product->provider_id;

        if (! filled($providerSource) || ! filled($product->provider_product_id)) {
            $this->markAsFailed($transaction, 'Produk API belum memiliki konfigurasi provider yang lengkap.');

            return;
        }

        $response = $providerSource === 'digiflazz'
            ? $this->digiflazzService->placeOrder(
                (string) $product->provider_product_id,
                (string) $transaction->provider_customer_no,
                (string) $transaction->invoice_number
            )
            : $this->orderSosmedService->placeOrder(
                (string) $product->provider_product_id,
                (string) $transaction->target_data,
                max(1, (int) ($transaction->provider_order_quantity ?: $product->provider_quantity ?: 1))
            );

        $previousStatus = $transaction->order_status;
        $transaction->update([
            'response_data' => array_merge($transaction->response_data ?? [], [
                'provider_order_id' => $response['provider_order_id'] ?? null,
                'raw' => $response['raw'] ?? $response,
                'provider_submitted_at' => now()->toDateTimeString(),
            ]),
            'target_notes' => $response['message'] ?? $transaction->target_notes,
        ]);

        if (! ($response['success'] ?? false)) {
            $this->markAsFailed($transaction, $response['message'] ?? 'Provider menolak pesanan.', $response['raw'] ?? $response);

            return;
        }

        $nextStatus = $response['order_status'] ?? 'processing';

        $transaction->update([
            'order_status' => $nextStatus,
            'target_notes' => $response['message'] ?? $transaction->target_notes,
        ]);

        $this->rewardUserIfNeeded($transaction, $previousStatus, $nextStatus);
        if ($nextStatus === 'success') {
            $this->sendSuccessEmail($transaction, $previousStatus);
        }
        $this->discordWebhookService->sendOrderAlert(
            $transaction,
            $nextStatus === 'success' ? 'Pesanan otomatis berhasil diproses' : 'Pesanan otomatis masuk ke provider',
            $response['message'] ?? 'Provider telah menerima pesanan dari website.',
            $nextStatus === 'success' ? 4968121 : 5922504
        );
    }

    protected function handleApiOrderOnce(Transaction $transaction): void
    {
        if (in_array($transaction->order_status, ['success', 'failed'], true)) {
            return;
        }

        $lockKey = 'provider-fulfillment:' . $transaction->id;

        if (! Cache::add($lockKey, true, now()->addMinutes(5))) {
            return;
        }

        try {
            $freshTransaction = Transaction::with(['product.category', 'user'])->find($transaction->id);

            if (! $freshTransaction || $freshTransaction->payment_status !== 'paid') {
                return;
            }

            $this->handleApiOrder($freshTransaction);
        } finally {
            Cache::forget($lockKey);
        }
    }

    protected function handleCredentialOrder(Transaction $transaction): void
    {
        if ($transaction->product?->process_type === 'account' && $transaction->product?->requires_buyer_email) {
            $transaction->update([
                'order_status' => 'pending',
                'target_notes' => 'Menunggu admin memproses aplikasi premium untuk email pembeli: ' . $transaction->target_data,
            ]);

            $this->discordWebhookService->sendOrderAlert(
                $transaction,
                'Pesanan aplikasi premium butuh proses admin',
                'Pembeli mengisi email aplikasi. Status pesanan perlu diupdate manual oleh admin.',
                16433050
            );

            return;
        }

        $credential = ProductCredential::where('product_id', $transaction->product_id)
            ->where('is_active', true)
            ->whereColumn('current_usage', '<', 'max_usage')
            ->orderBy('id')
            ->first();

        if (! $credential) {
            $transaction->update([
                'order_status' => 'pending',
                'target_notes' => 'Stok saat ini habis. Pesanan akan otomatis diproses setelah admin menambah stok.',
            ]);

            $this->discordWebhookService->sendOrderAlert(
                $transaction,
                'Pesanan menunggu restock',
                'Produk akun atau nomor belum memiliki stok yang tersedia.',
                16433050
            );

            return;
        }

        $this->assignCredential($transaction, $credential);
    }

    protected function handleManualOrder(Transaction $transaction): void
    {
        $previousStatus = $transaction->order_status;

        $transaction->update([
            'order_status' => 'processing',
            'target_notes' => 'Pesanan manual sudah masuk ke antrean admin.',
        ]);

        $this->rewardUserIfNeeded($transaction, $previousStatus, 'processing');
        $this->discordWebhookService->sendOrderAlert(
            $transaction,
            'Pesanan manual butuh tindak lanjut',
            'Silakan cek panel manual order untuk menyelesaikan pesanan ini.',
            16433050
        );
    }

    public function markAsFailed(Transaction $transaction, string $message, array $responseData = []): void
    {
        $transaction->loadMissing('product', 'user');
        $previousStatus = $transaction->order_status;

        $transaction->update([
            'order_status' => 'failed',
            'target_notes' => $message,
            'response_data' => $responseData !== [] ? $responseData : $transaction->response_data,
        ]);

        $this->refundToWalletIfNeeded($transaction);
        $this->discordWebhookService->sendOrderAlert(
            $transaction,
            'Pesanan gagal diproses',
            $message,
            16738680
        );

        if (in_array($previousStatus, ['processing', 'success'], true)) {
            // Tidak mengurangi poin agar histori loyalty user tetap stabil.
        }
    }

    protected function refundToWalletIfNeeded(Transaction $transaction): void
    {
        if ($transaction->payment_status !== 'paid') {
            return;
        }

        if (! $this->isRefundableApiTransaction($transaction)) {
            return;
        }

        $alreadyRefunded = WalletHistory::where('invoice_number', $transaction->invoice_number)
            ->where('type', 'refund')
            ->exists();

        if ($alreadyRefunded) {
            return;
        }

        $user = User::find($transaction->user_id);

        if (! $user) {
            return;
        }

        $user->increment('balance', (float) $transaction->amount);

        WalletHistory::create([
            'user_id' => $user->id,
            'type' => 'refund',
            'amount' => $transaction->amount,
            'description' => 'Refund otomatis untuk pesanan gagal: ' . ($transaction->product->name ?? 'Produk'),
            'invoice_number' => $transaction->invoice_number,
        ]);
    }

    protected function isRefundableApiTransaction(Transaction $transaction): bool
    {
        $transaction->loadMissing('product');

        $product = $transaction->product;
        $provider = $product?->provider_source ?: $product?->provider_id;

        return $product?->process_type === 'api'
            && in_array($provider, ['digiflazz', 'ordersosmed'], true);
    }

    protected function rewardUserIfNeeded(Transaction $transaction, ?string $previousStatus, string $nextStatus): void
    {
        if ($nextStatus !== 'success') {
            return;
        }

        if ($previousStatus === 'success') {
            return;
        }

        DB::transaction(function () use ($transaction) {
            $user = User::query()->lockForUpdate()->find($transaction->user_id);

            if (! $user) {
                return;
            }

            $newPoints = (int) $user->points + 1;
            $bonusCount = intdiv($newPoints, 5);
            $remainingPoints = $newPoints % 5;
            $bonusAmount = $bonusCount * 1000;

            $user->forceFill([
                'points' => $remainingPoints,
                'balance' => (float) $user->balance + $bonusAmount,
            ])->save();

            for ($i = 0; $i < $bonusCount; $i++) {
                WalletHistory::create([
                    'user_id' => $user->id,
                    'type' => 'topup',
                    'amount' => 1000,
                    'description' => 'Bonus otomatis 5 order sukses',
                    'invoice_number' => 'BONUS-' . strtoupper(Str::random(10)),
                ]);
            }
        });
    }

    protected function sendSuccessEmail(Transaction $transaction, ?string $previousStatus, bool $onlyWhenSuccess = true): void
    {
        if ($onlyWhenSuccess && $transaction->order_status !== 'success') {
            return;
        }

        if ($previousStatus === 'success') {
            return;
        }

        if (! $transaction->user?->email) {
            return;
        }

        try {
            Mail::to($transaction->user->email)->send(new OrderSuccessMail($transaction->fresh(['product', 'user'])));
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email sukses pesanan.', [
                'transaction_id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'email' => $transaction->user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
