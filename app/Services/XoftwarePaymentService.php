<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class XoftwarePaymentService
{
    public function createTransactionPayment(Transaction $transaction, ?User $user = null): array
    {
        $transaction->loadMissing('product', 'user');

        $buyer = $user ?: $transaction->user;
        $productName = $transaction->product?->name ?: 'Pesanan Wiboost';

        return $this->createPayment(
            merchantRef: $transaction->invoice_number,
            amount: (int) round((float) $transaction->amount),
            note: 'Pembayaran pesanan ' . $productName,
            metadata: [
                'customer' => $this->customerMetadata($buyer),
                'products' => [[
                    'product_code' => (string) ($transaction->product_id ?: $transaction->invoice_number),
                    'product_name' => $productName,
                    'product_url' => $this->publicUrl('/user/history'),
                ]],
            ],
        );
    }

    public function createDepositPayment(Deposit $deposit, ?User $user = null): array
    {
        $deposit->loadMissing('user');

        $buyer = $user ?: $deposit->user;

        return $this->createPayment(
            merchantRef: $deposit->invoice_number,
            amount: (int) round((float) $deposit->amount),
            note: 'Top Up Saldo Wiboost',
            metadata: [
                'customer' => $this->customerMetadata($buyer),
                'products' => [[
                    'product_code' => 'TOPUP-SALDO',
                    'product_name' => 'Top Up Saldo Wiboost',
                    'product_url' => $this->publicUrl('/user/wallet'),
                ]],
            ],
        );
    }

    public function transactionDetail(string $reference): array
    {
        return $this->request('POST', '/v1/api/transactions/status', [
            'ref_id' => $reference,
        ]);
    }

    protected function createPayment(
        string $merchantRef,
        int $amount,
        string $note,
        array $metadata = []
    ): array {
        $this->ensureConfigured();

        if ($amount < 1000) {
            throw new RuntimeException('QRIS hanya tersedia untuk pesanan minimal Rp 1.000');
        }

        $channelCode = $this->normalizeChannelCode((string) config('xoftware.default_channel', 'QRIS'));

        $payload = [
            'merchant_id' => (int) config('xoftware.merchant_id'),
            'channel_code' => $channelCode,
            'amount' => $amount,
            'ref_id' => $merchantRef,
            'fee_direction' => $this->resolveFeeDirection(),
            'expires_in_minutes' => max(1, (int) config('xoftware.expired_minutes', 60)),
            'note' => $note,
            'metadata' => $this->normalizeMetadata($metadata),
        ];

        $body = $this->request('POST', '/v1/api/transactions', $payload);
        $raw = $this->normalizeRawResponse($body, $merchantRef, $amount);
        $checkoutUrl = $this->resolveCheckoutUrl($raw);

        if (! filled($checkoutUrl)) {
            throw new RuntimeException('Xoftware tidak mengembalikan URL pembayaran QRIS.');
        }

        return [
            'reference' => (string) (
                data_get($raw, 'data.ref_id')
                ?: data_get($raw, 'data.transaction_id')
                ?: data_get($raw, 'data.reference')
                ?: $merchantRef
            ),
            'payment_method' => (string) (data_get($raw, 'data.channel_code') ?: 'QRIS'),
            'payment_name' => 'QRIS',
            'checkout_url' => $checkoutUrl,
            'raw' => $raw,
        ];
    }

    protected function request(string $method, string $path, array $payload = []): array
    {
        $this->ensureConfigured();

        $method = strtoupper($method);
        $rawBody = $method === 'GET'
            ? ''
            : (json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}');

        $timestamp = (string) now()->timestamp;
        $signaturePayload = $timestamp . "\n" . $method . "\n" . $path . "\n" . $rawBody;
        $signature = base64_encode(hash_hmac('sha256', $signaturePayload, (string) config('xoftware.signature_secret'), true));

        $url = rtrim((string) config('xoftware.base_url'), '/') . $path;
        $request = Http::acceptJson()
            ->timeout(30)
            ->withHeaders([
                'X-API-Key' => (string) config('xoftware.api_key'),
                'X-Timestamp' => $timestamp,
                'X-Signature' => $signature,
            ]);

        $response = $method === 'GET'
            ? $request->get($url, $payload)
            : $request->withBody($rawBody, 'application/json')->send($method, $url);

        $body = $response->json();

        if (! is_array($body)) {
            $body = ['message' => $response->body()];
        }

        if (! $response->successful() || data_get($body, 'success') === false || data_get($body, 'error') === true) {
            $message = (string) (data_get($body, 'message') ?: data_get($body, 'error') ?: 'Tidak ada detail error dari Xoftware.');

            Log::warning('Xoftware payment request failed', [
                'status' => $response->status(),
                'path' => $path,
                'message' => $message,
                'response' => $body,
                'payload' => $this->safePayloadForLog($payload),
            ]);

            throw new RuntimeException('Xoftware gagal memproses pembayaran. ' . $message);
        }

        return $body;
    }

    protected function normalizeRawResponse(array $body, string $merchantRef, int $amount): array
    {
        $feePreview = (array) data_get($body, 'data.fee_preview', []);
        $gross = (int) round((float) data_get($feePreview, 'gross', data_get($body, 'data.total_amount', $amount)));
        $adminFee = max(0, $gross - $amount);

        data_set($body, 'data.reference', data_get($body, 'data.transaction_id') ?: data_get($body, 'data.ref_id') ?: $merchantRef);
        data_set($body, 'data.merchant_ref', data_get($body, 'data.ref_id') ?: $merchantRef);
        data_set($body, 'data.payment_method', data_get($body, 'data.channel_code') ?: 'QRIS');
        data_set($body, 'data.payment_name', 'QRIS');
        data_set($body, 'data.total_amount', $gross > 0 ? $gross : $amount);
        data_set($body, 'data.admin_fee', $adminFee);

        return $body;
    }

    protected function resolveCheckoutUrl(array $body): ?string
    {
        foreach ([
            'data.url',
            'data.checkout_url',
            'data.payment_url',
            'data.pay_url',
            'data.invoice_url',
            'url',
            'checkout_url',
            'payment_url',
            'pay_url',
        ] as $key) {
            $url = data_get($body, $key);

            if (filled($url)) {
                return (string) $url;
            }
        }

        return null;
    }

    protected function publicUrl(string $path): string
    {
        $baseUrl = rtrim((string) (config('wiboost.public_url') ?: config('app.url')), '/');

        return $baseUrl . '/' . ltrim($path, '/');
    }

    protected function resolveFeeDirection(): string
    {
        $channelCode = $this->normalizeChannelCode((string) config('xoftware.default_channel', 'QRIS'));
        $direction = strtolower(trim((string) config('xoftware.fee_direction', 'merchant')) ?: 'merchant');

        // The official Xoftware PHP SDK sends QRIS requests with merchant-borne fees.
        // Keeping QRIS on this value avoids "request invalid" from stricter validation.
        if ($channelCode === 'QRIS') {
            return 'merchant';
        }

        return in_array($direction, ['merchant', 'user'], true) ? $direction : 'merchant';
    }

    protected function normalizeChannelCode(string $channelCode): string
    {
        $channelCode = strtoupper(trim($channelCode)) ?: 'QRIS';

        return match ($channelCode) {
            'QRISREALTIME', 'QRIS_REALTIME', 'QRIS-REALTIME' => 'QRIS',
            default => $channelCode,
        };
    }

    protected function customerMetadata(?User $user): array
    {
        return array_filter([
            'id' => $user?->id ? (string) $user->id : null,
            'name' => $user?->name,
            'email' => $user?->email,
        ], fn ($value) => filled($value));
    }

    protected function normalizeMetadata(array $metadata): array
    {
        $customer = (array) ($metadata['customer'] ?? []);
        $products = array_values(array_filter((array) ($metadata['products'] ?? [])));

        return [
            'customer' => array_filter($customer, fn ($value) => filled($value)),
            'products' => $products,
        ];
    }

    protected function safePayloadForLog(array $payload): array
    {
        return [
            'merchant_id' => $payload['merchant_id'] ?? null,
            'channel_code' => $payload['channel_code'] ?? null,
            'amount' => $payload['amount'] ?? null,
            'ref_id' => $payload['ref_id'] ?? null,
            'fee_direction' => $payload['fee_direction'] ?? null,
            'expires_in_minutes' => $payload['expires_in_minutes'] ?? null,
            'note' => $payload['note'] ?? null,
            'metadata' => $payload['metadata'] ?? null,
        ];
    }

    protected function ensureConfigured(): void
    {
        $missing = [];

        foreach ([
            'XOFTWARE_API_KEY' => config('xoftware.api_key'),
            'XOFTWARE_MERCHANT_ID' => config('xoftware.merchant_id'),
        ] as $key => $value) {
            if (! filled($value)) {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw new RuntimeException('Konfigurasi Xoftware belum lengkap. Isi ' . implode(', ', $missing) . '.');
        }
    }
}
