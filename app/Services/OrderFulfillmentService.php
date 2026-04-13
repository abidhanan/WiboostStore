<?php

namespace App\Services;

use App\Mail\OrderSuccessMail;
use App\Models\ProductCredential;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\Mail;

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

        if (! $transaction->product) {
            $this->markAsFailed($transaction, 'Produk untuk transaksi ini tidak ditemukan.');

            return;
        }

        match ($transaction->product->process_type) {
            'api' => $this->handleApiOrder($transaction),
            'account', 'number' => $this->handleCredentialOrder($transaction),
            'manual' => $this->handleManualOrder($transaction),
            default => $this->markAsFailed($transaction, 'Tipe proses produk tidak dikenali.'),
        };
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
                ? 'Silakan hubungi admin untuk meminta OTP atau akses tambahan.'
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

    protected function handleApiOrder(Transaction $transaction): void
    {
        $product = $transaction->product;
        $providerSource = $product->provider_source ?: $product->provider_id;

        if (! filled($providerSource) || ! filled($product->provider_product_id)) {
            $this->markAsFailed($transaction, 'Produk API belum memiliki konfigurasi provider yang lengkap.');

            return;
        }

        $response = $providerSource === 'digiflazz'
            ? $this->digiflazzService->placeOrder(
                (string) $product->provider_product_id,
                (string) $transaction->target_data,
                (string) $transaction->invoice_number
            )
            : $this->orderSosmedService->placeOrder(
                (string) $product->provider_product_id,
                (string) $transaction->target_data,
                max(1, (int) ($product->provider_quantity ?: 1))
            );

        $previousStatus = $transaction->order_status;
        $transaction->update([
            'response_data' => $response['raw'] ?? $response,
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

    protected function handleCredentialOrder(Transaction $transaction): void
    {
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

    protected function rewardUserIfNeeded(Transaction $transaction, ?string $previousStatus, string $nextStatus): void
    {
        if ($nextStatus !== 'success') {
            return;
        }

        if ($previousStatus === 'success') {
            return;
        }

        User::find($transaction->user_id)?->increment('points', 1);
    }

    protected function sendSuccessEmail(Transaction $transaction, ?string $previousStatus, bool $onlyWhenSuccess = true): void
    {
        if ($onlyWhenSuccess && $transaction->order_status !== 'success') {
            return;
        }

        if ($previousStatus === 'success') {
            return;
        }

        if ($transaction->user?->email) {
            Mail::to($transaction->user->email)->send(new OrderSuccessMail($transaction->fresh(['product', 'user'])));
        }
    }
}
