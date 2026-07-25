<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TripayService
{
    public function createTransactionPayment(Transaction $transaction, ?User $user = null): array
    {
        $user ??= $transaction->user;
        $amount = $this->moneyToInt($transaction->amount);
        $productName = (string) ($transaction->display_product_name ?? 'Pesanan Wiboost');

        return $this->createClosedPayment(
            merchantRef: $transaction->invoice_number,
            amount: $amount,
            customerName: $user?->name,
            customerEmail: $user?->email,
            customerPhone: $user?->whatsapp,
            returnPath: '/user/history?source=tripay&auto_open=' . $transaction->id,
            orderItems: [[
                'sku' => (string) ($transaction->product_id ?? $transaction->invoice_number),
                'name' => $productName,
                'price' => $amount,
                'quantity' => 1,
            ]]
        );
    }

    public function createDepositPayment(Deposit $deposit, ?User $user = null): array
    {
        $user ??= $deposit->user;
        $amount = $this->moneyToInt($deposit->amount);

        return $this->createClosedPayment(
            merchantRef: $deposit->invoice_number,
            amount: $amount,
            customerName: $user?->name,
            customerEmail: $user?->email,
            customerPhone: $user?->whatsapp,
            returnPath: '/user/wallet?source=tripay',
            orderItems: [[
                'sku' => 'TOPUP-WIBOOST',
                'name' => 'Top Up Saldo Wiboost',
                'price' => $amount,
                'quantity' => 1,
            ]]
        );
    }

    public function transactionDetail(string $reference): array
    {
        $this->ensureConfigured();

        $response = Http::acceptJson()
            ->withToken((string) config('tripay.api_key'))
            ->timeout(20)
            ->get(rtrim((string) config('tripay.base_url'), '/') . '/transaction/detail', [
                'reference' => $reference,
            ]);

        $body = $response->json() ?? [];

        if (! $response->successful() || ! data_get($body, 'success')) {
            throw new RuntimeException((string) data_get($body, 'message', 'Tripay gagal mengecek status pembayaran.'));
        }

        return $body;
    }

    protected function createClosedPayment(
        string $merchantRef,
        int $amount,
        ?string $customerName,
        ?string $customerEmail,
        ?string $customerPhone,
        string $returnPath,
        array $orderItems
    ): array {
        $this->ensureConfigured();

        $merchantCode = (string) config('tripay.merchant_code');
        $privateKey = (string) config('tripay.private_key');
        $signature = hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey);
        $method = trim((string) config('tripay.default_method', 'QRIS')) ?: 'QRIS';

        $payload = [
            'method' => $method,
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => $customerName ?: 'Pelanggan Wiboost',
            'customer_email' => $customerEmail ?: 'buyer@wiboost.local',
            'customer_phone' => $this->normalizePhone($customerPhone),
            'order_items' => $orderItems,
            'callback_url' => $this->publicUrl('/tripay/callback'),
            'return_url' => $this->publicUrl($returnPath),
            'expired_time' => now()->addMinutes(max(1, (int) config('tripay.expired_minutes', 60)))->timestamp,
            'signature' => $signature,
        ];

        $response = Http::asForm()
            ->acceptJson()
            ->withToken((string) config('tripay.api_key'))
            ->timeout(30)
            ->post(rtrim((string) config('tripay.base_url'), '/') . '/transaction/create', $payload);

        $body = $response->json();
        $message = (string) data_get($body, 'message', 'Tripay tidak mengembalikan pesan error.');

        if (! $response->successful() || ! data_get($body, 'success')) {
            throw new RuntimeException(trim('Tripay gagal membuat pembayaran. ' . $message));
        }

        $checkoutUrl = data_get($body, 'data.checkout_url') ?: data_get($body, 'data.pay_url');

        if (! $checkoutUrl) {
            throw new RuntimeException('Tripay tidak mengembalikan checkout_url pembayaran.');
        }

        return [
            'reference' => data_get($body, 'data.reference'),
            'payment_method' => data_get($body, 'data.payment_method'),
            'payment_name' => data_get($body, 'data.payment_name'),
            'checkout_url' => $checkoutUrl,
            'raw' => $body,
        ];
    }

    protected function ensureConfigured(): void
    {
        foreach (['api_key', 'private_key', 'merchant_code'] as $key) {
            if (! filled(config("tripay.{$key}"))) {
                throw new RuntimeException('Konfigurasi Tripay belum lengkap. Isi TRIPAY_API_KEY, TRIPAY_PRIVATE_KEY, dan TRIPAY_MERCHANT_CODE.');
            }
        }
    }

    protected function moneyToInt($amount): int
    {
        return max(0, (int) round((float) $amount));
    }

    protected function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return '081234567890';
        }

        if (str_starts_with($digits, '62')) {
            return '0' . substr($digits, 2);
        }

        return $digits;
    }

    protected function publicUrl(string $path): string
    {
        $baseUrl = rtrim((string) config('wiboost.public_url', config('app.url')), '/');

        return $baseUrl . '/' . ltrim($path, '/');
    }
}
