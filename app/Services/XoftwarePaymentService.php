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

        $productName = $transaction->product?->name ?: 'Pesanan Wiboost';
        $paymentUser = $user ?: $transaction->user;

        return $this->createPayment(
            merchantRef: $transaction->invoice_number,
            amount: (int) round((float) $transaction->amount),
            note: 'Pembayaran pesanan ' . $productName,
            returnUrl: $this->publicUrl('user/history'),
            buyerFeeShareOverride: $paymentUser?->isMember() ? 0.0 : null,
            metadata: $this->paymentMetadata(
                user: $paymentUser,
                productCode: (string) ($transaction->product?->provider_product_id ?: $transaction->product_id ?: $transaction->invoice_number),
                productName: $productName,
                productUrl: $this->publicUrl('user/history'),
            ),
        );
    }

    public function createDepositPayment(Deposit $deposit, ?User $user = null): array
    {
        $deposit->loadMissing('user');
        $isMembershipPayment = $deposit->isMembershipPayment();

        return $this->createPayment(
            merchantRef: $deposit->invoice_number,
            amount: (int) round((float) $deposit->amount),
            note: $isMembershipPayment ? 'Pembayaran Membership Wiboost' : 'Top Up Saldo Wiboost',
            returnUrl: $this->publicUrl($isMembershipPayment ? 'user/membership' : 'user/dashboard'),
            buyerFeeShareOverride: $isMembershipPayment ? $this->membershipBuyerFeeShare() : null,
            feeDirectionOverride: $isMembershipPayment ? 'merchant' : null,
            metadata: $this->paymentMetadata(
                user: $user ?: $deposit->user,
                productCode: $isMembershipPayment ? 'MEMBERSHIP-WIBOOST' : 'TOPUP-SALDO',
                productName: $isMembershipPayment ? 'Membership Wiboost 30 Hari' : 'Top Up Saldo Wiboost',
                productUrl: $this->publicUrl($isMembershipPayment ? 'user/membership' : 'user/wallet'),
            ),
        );
    }

    public function transactionDetail(string $reference): array
    {
        return $this->request('POST', '/v1/api/transactions/status', [
            'ref_id' => $reference,
        ]);
    }

    public function paymentChannels(): array
    {
        return $this->request('GET', '/v1/api/channels');
    }

    public function balance(): array
    {
        return $this->request('POST', '/v1/api/balance', null);
    }

    protected function membershipBuyerFeeShare(): float
    {
        $share = (float) config('xoftware.buyer_fee_share', 0.5);

        return $share > 0 ? min(1.0, $share) : 0.5;
    }

    protected function createPayment(
        string $merchantRef,
        int $amount,
        string $note,
        ?string $returnUrl = null,
        ?float $buyerFeeShareOverride = null,
        ?string $feeDirectionOverride = null,
        array $metadata = []
    ): array {
        $this->ensureConfigured();

        if ($amount < 1000) {
            throw new RuntimeException('QRIS hanya tersedia untuk pesanan minimal Rp 1.000');
        }

        $channelCode = $this->normalizeChannelCode((string) config('xoftware.default_channel', 'QRISREALTIME'));
        $paymentAmounts = $this->resolveProviderPaymentAmounts($amount, $channelCode, $buyerFeeShareOverride, $feeDirectionOverride);

        $payload = [
            'merchant_id' => (int) config('xoftware.merchant_id'),
            'channel_code' => $channelCode,
            'amount' => $paymentAmounts['provider_amount'],
            'ref_id' => $merchantRef,
            'fee_direction' => $paymentAmounts['fee_direction'],
            'expires_in_minutes' => max(1, (int) config('xoftware.expired_minutes', 60)),
            'notify_url' => $this->publicUrl('xoftware/callback'),
            'return_url' => $returnUrl ?: $this->publicUrl('user/dashboard'),
            'note' => $note,
        ];

        $metadataCandidates = $this->metadataCandidates(
            $metadata !== [] ? $metadata : $this->fallbackMetadata($merchantRef),
            $merchantRef
        );
        $body = null;

        foreach ($metadataCandidates as $index => $candidate) {
            $payload['metadata'] = $candidate['metadata'];

            try {
                $body = $this->request('POST', '/v1/api/transactions', $payload);
                break;
            } catch (RuntimeException $exception) {
                $isLastCandidate = $index === count($metadataCandidates) - 1;

                if (! $this->isMetadataValidationError($exception) || $isLastCandidate) {
                    throw $exception;
                }

                Log::warning('Xoftware payment retrying with compatible metadata', [
                    'ref_id' => $merchantRef,
                    'from_variant' => $candidate['name'],
                    'next_variant' => $metadataCandidates[$index + 1]['name'],
                ]);
            }
        }

        if (! is_array($body)) {
            throw new RuntimeException('Xoftware tidak mengembalikan respons pembayaran yang valid.');
        }

        $raw = $this->normalizeRawResponse($body, $merchantRef, $amount, $paymentAmounts);
        $checkoutUrl = $this->resolveLocalQrisUrl($raw, $merchantRef)
            ?: (str_starts_with($merchantRef, 'MEM-') ? $this->publicUrl('user/qris/' . rawurlencode($merchantRef)) : null)
            ?: $this->resolveCheckoutUrl($raw);

        if (! $checkoutUrl) {
            throw new RuntimeException('Xoftware tidak mengembalikan URL atau data QRIS.');
        }

        return [
            'reference' => (string) (
                data_get($raw, 'data.ref_id')
                ?: data_get($raw, 'data.transaction_id')
                ?: data_get($raw, 'data.reference')
                ?: $merchantRef
            ),
            'payment_method' => (string) (data_get($raw, 'data.channel_code') ?: $channelCode),
            'payment_name' => $this->channelDisplayName((string) (data_get($raw, 'data.channel_code') ?: $channelCode)),
            'checkout_url' => $checkoutUrl,
            'raw' => $raw,
        ];
    }

    protected function request(string $method, string $path, ?array $payload = []): array
    {
        $this->ensureConfigured();

        $method = strtoupper($method);
        $rawBody = $method === 'GET' || $payload === null
            ? ''
            : (json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}');

        $timestamp = (string) now()->timestamp;
        $signaturePayload = $timestamp . "\n" . $method . "\n" . $path . "\n" . $rawBody;
        $signature = base64_encode(hash_hmac('sha256', $signaturePayload, (string) config('xoftware.signature_secret'), true));

        $url = rtrim((string) config('xoftware.base_url'), '/') . $path;
        $request = Http::acceptJson()
            ->connectTimeout(8)
            ->timeout(15)
            ->withHeaders([
                'X-API-Key' => (string) config('xoftware.api_key'),
                'X-Timestamp' => $timestamp,
                'X-Signature' => $signature,
            ]);

        $response = $method === 'GET'
            ? $request->get($url, $payload ?? [])
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
                'payload' => $this->safePayloadForLog($payload ?? []),
            ]);

            throw new RuntimeException($this->providerFailureMessage($message));
        }

        return $body;
    }

    protected function providerFailureMessage(string $message): string
    {
        $normalized = strtolower($message);

        if (str_contains($normalized, 'err:54344') || str_contains($normalized, 'create qris gagal')) {
            return 'Xoftware menerima request, tetapi provider QRIS gagal membuat transaksi (ERR:54344). '
                . 'Pastikan channel QRIS merchant aktif dan Pengajuan Integrasi sudah disetujui. '
                . 'Jika keduanya aktif, kirim Merchant ID, waktu kejadian, dan kode ERR:54344 melalui tiket Xoftware.';
        }

        return 'Xoftware gagal memproses pembayaran. ' . $message;
    }

    protected function normalizeRawResponse(array $body, string $merchantRef, int $baseAmount, array $paymentAmounts): array
    {
        $data = data_get($body, 'data');

        // Xoftware may return transaction fields at the response root.
        if (! is_array($data)) {
            $data = $body;
            $body['data'] = $data;
        }

        $feePreview = (array) data_get($data, 'fee_preview', []);
        $providerAmount = (int) ($paymentAmounts['provider_amount'] ?? $baseAmount);
        $buyerAdminFee = (int) ($paymentAmounts['buyer_admin_fee'] ?? 0);
        $gross = (int) round((float) data_get($feePreview, 'gross', data_get($data, 'total_amount', $providerAmount)));
        $totalPayable = max($gross, $providerAmount);
        $channelCode = (string) (data_get($data, 'channel_code') ?: ($paymentAmounts['channel_code'] ?? 'QRIS'));

        data_set($body, 'data.reference', data_get($data, 'transaction_id') ?: data_get($data, 'ref_id') ?: $merchantRef);
        data_set($body, 'data.merchant_ref', data_get($data, 'ref_id') ?: $merchantRef);
        data_set($body, 'data.payment_method', $channelCode);
        data_set($body, 'data.payment_name', $this->channelDisplayName($channelCode));
        data_set($body, 'data.total_amount', $totalPayable > 0 ? $totalPayable : $baseAmount);
        data_set($body, 'data.admin_fee', $buyerAdminFee);
        data_set($body, 'data.wiboost_base_amount', $baseAmount);
        data_set($body, 'data.wiboost_buyer_admin_fee', $buyerAdminFee);
        data_set($body, 'data.wiboost_total_payable', $totalPayable > 0 ? $totalPayable : $baseAmount);
        data_set($body, 'data.wiboost_fee_split', [
            'buyer_share' => (float) ($paymentAmounts['buyer_fee_share'] ?? 0),
            'fee_direction' => $paymentAmounts['fee_direction'] ?? 'merchant',
            'fee_percent' => (float) ($paymentAmounts['fee_percent'] ?? 0),
            'fee_fixed' => (int) ($paymentAmounts['fee_fixed'] ?? 0),
        ]);

        return $body;
    }

    protected function resolveCheckoutUrl(array $body): ?string
    {
        foreach ([
            'data.url',
            'data.checkout_url',
            'data.payment_url',
            'data.payment_link',
            'data.pay_url',
            'data.invoice_url',
            'data.invoice_link',
            'data.redirect_url',
            'data.checkout',
            'data.payment_page_url',
            'data.links.checkout',
            'data.links.payment',
            'url',
            'checkout_url',
            'payment_url',
            'payment_link',
            'pay_url',
            'invoice_url',
            'invoice_link',
            'redirect_url',
            'checkout',
            'payment_page_url',
        ] as $key) {
            $url = data_get($body, $key);

            if (is_scalar($url) && filled((string) $url)) {
                return (string) $url;
            }
        }

        return null;
    }

    protected function resolveLocalQrisUrl(array $body, string $merchantRef): ?string
    {
        if (! $this->hasQrisPayload($body)) {
            return null;
        }

        return $this->publicUrl('user/qris/' . rawurlencode($merchantRef));
    }

    protected function hasQrisPayload(array $body): bool
    {
        foreach ($this->qrisPayloadKeys() as $key) {
            if ($this->containsFilledScalar(data_get($body, $key))) {
                return true;
            }
        }

        return false;
    }

    protected function containsFilledScalar(mixed $value): bool
    {
        if (is_scalar($value)) {
            return filled((string) $value);
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->containsFilledScalar($item)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function qrisPayloadKeys(): array
    {
        return [
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
            'data.qris',
            'data.qris_data',
            'data.qris_payload',
            'data.qris_url',
            'data.qris_image',
            'data.qris_image_url',
            'data.qris_base64',
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
            'qris',
            'qris_data',
            'qris_payload',
            'qris_url',
            'qris_image',
            'qris_image_url',
            'qris_base64',
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
        ];
    }

    protected function publicUrl(string $path): string
    {
        $baseUrl = rtrim((string) (config('wiboost.public_url') ?: config('app.url')), '/');

        return $baseUrl . '/' . ltrim($path, '/');
    }

    protected function resolveFeeDirection(?string $channelCode = null): string
    {
        $direction = strtolower(trim((string) config('xoftware.fee_direction', 'merchant')) ?: 'merchant');

        return in_array($direction, ['merchant', 'user'], true) ? $direction : 'merchant';
    }

    protected function normalizeChannelCode(string $channelCode): string
    {
        $channelCode = strtoupper(trim($channelCode)) ?: 'QRISREALTIME';

        return match ($channelCode) {
            'QRIS REALTIME', 'QRIS_REALTIME', 'QRIS-REALTIME', 'QRISRT', 'QRIS_RT' => 'QRISREALTIME',
            'QRIS STANDARD', 'QRIS_STANDART', 'QRIS-STANDART', 'QRIS_STANDARD', 'QRIS-STANDARD' => 'QRIS',
            default => $channelCode,
        };
    }

    protected function channelDisplayName(string $channelCode): string
    {
        return $this->normalizeChannelCode($channelCode) === 'QRISREALTIME' ? 'QRIS Real Time' : 'QRIS';
    }

    protected function resolveProviderPaymentAmounts(
        int $baseAmount,
        string $channelCode,
        ?float $buyerFeeShareOverride = null,
        ?string $feeDirectionOverride = null
    ): array
    {
        $feeDirection = $feeDirectionOverride && in_array($feeDirectionOverride, ['merchant', 'user'], true)
            ? $feeDirectionOverride
            : $this->resolveFeeDirection($channelCode);
        $feeConfig = $this->feeConfigForChannel($channelCode);
        $buyerFeeShare = $buyerFeeShareOverride !== null
            ? max(0.0, min(1.0, $buyerFeeShareOverride))
            : max(0.0, min(1.0, (float) config('xoftware.buyer_fee_share', 0.5)));
        $buyerAdminFee = 0;

        if ($feeDirection === 'merchant' && $buyerFeeShare > 0) {
            $buyerAdminFee = $this->estimateBuyerAdminFee(
                baseAmount: $baseAmount,
                buyerFeeShare: $buyerFeeShare,
                feePercent: $feeConfig['percent'],
                feeFixed: $feeConfig['fixed'],
            );
        }

        return [
            'channel_code' => $channelCode,
            'base_amount' => $baseAmount,
            'provider_amount' => $baseAmount + $buyerAdminFee,
            'buyer_admin_fee' => $buyerAdminFee,
            'buyer_fee_share' => $buyerFeeShare,
            'fee_direction' => $feeDirection,
            'fee_percent' => $feeConfig['percent'],
            'fee_fixed' => $feeConfig['fixed'],
        ];
    }

    protected function feeConfigForChannel(string $channelCode): array
    {
        $channelCode = $this->normalizeChannelCode($channelCode);

        if ($channelCode === 'QRISREALTIME') {
            return [
                'percent' => max(0.0, (float) config('xoftware.qris_realtime_fee_percent', 0.007)),
                'fixed' => max(0, (int) config('xoftware.qris_realtime_fee_fixed', 250)),
            ];
        }

        return [
            'percent' => max(0.0, (float) config('xoftware.qris_fee_percent', 0.007)),
            'fixed' => max(0, (int) config('xoftware.qris_fee_fixed', 150)),
        ];
    }

    protected function estimateBuyerAdminFee(int $baseAmount, float $buyerFeeShare, float $feePercent, int $feeFixed): int
    {
        if ($baseAmount <= 0 || $buyerFeeShare <= 0) {
            return 0;
        }

        $denominator = 1 - ($feePercent * $buyerFeeShare);

        if ($denominator <= 0) {
            $denominator = 1;
        }

        return (int) ceil(($buyerFeeShare * (($baseAmount * $feePercent) + $feeFixed)) / $denominator);
    }

    protected function customerMetadata(?User $user): array
    {
        return array_filter([
            'id' => $user?->id ? (string) $user->id : null,
            'name' => $user?->name,
            'email' => $user?->email,
        ], fn ($value) => filled($value));
    }

    protected function paymentMetadata(
        ?User $user,
        string $productCode,
        string $productName,
        string $productUrl
    ): array
    {
        $customer = array_filter([
            'id' => $user?->id ? (string) $user->id : null,
            'name' => $user?->name ?: 'Pelanggan Wiboost',
            'email' => filter_var($user?->email, FILTER_VALIDATE_EMAIL)
                ? (string) $user->email
                : 'customer@wiboostore.com',
            'phone' => preg_replace('/\D+/', '', (string) ($user?->whatsapp ?? '')) ?: null,
        ], static fn ($value) => filled($value));

        return [
            'customer' => $customer,
            'products' => [[
                'product_code' => $productCode ?: 'WIBOOST',
                'product_name' => $productName ?: 'Produk Wiboost',
                'product_thumbnail' => $this->publicUrl('images/wiboost-logo.png'),
                'product_url' => $productUrl,
            ]],
        ];
    }

    protected function fallbackMetadata(?string $merchantRef = null): array
    {
        return [
            'customer' => [
                'id' => 'guest',
                'name' => 'Pelanggan Wiboost',
            ],
            'products' => [
                [
                    'product_code' => $merchantRef ?: 'WIBOOST',
                    'product_name' => 'Produk Wiboost',
                    'product_thumbnail' => $this->publicUrl('images/wiboost-logo.png'),
                    'product_url' => $this->publicUrl('user/history'),
                ],
            ],
        ];
    }

    protected function metadataCandidates(array $metadata, string $merchantRef): array
    {
        $fullMetadata = $this->normalizeMetadata($metadata);
        $customer = (array) ($fullMetadata['customer'] ?? []);
        $product = (array) data_get($fullMetadata, 'products.0', []);

        if ($customer === []) {
            $customer = [
                'id' => $merchantRef,
                'name' => 'Pelanggan Wiboost',
            ];
        }

        if ($product === []) {
            $product = (array) data_get($this->fallbackMetadata($merchantRef), 'products.0', []);
        }

        $minimalProduct = array_filter([
            'product_code' => $product['product_code'] ?? $merchantRef,
            'product_name' => $product['product_name'] ?? 'Produk Wiboost',
        ], static fn ($value) => filled($value));

        $candidates = [
            [
                'name' => 'customer_and_products',
                'metadata' => $fullMetadata,
            ],
            [
                'name' => 'minimal_customer_and_product',
                'metadata' => [
                    'customer' => array_filter([
                        'id' => $customer['id'] ?? $merchantRef,
                        'name' => $customer['name'] ?? 'Pelanggan Wiboost',
                    ], static fn ($value) => filled($value)),
                    'products' => [$minimalProduct],
                ],
            ],
            [
                'name' => 'fallback_customer_and_product',
                'metadata' => $this->fallbackMetadata($merchantRef),
            ],
        ];

        $unique = [];

        foreach ($candidates as $candidate) {
            $key = json_encode($candidate['metadata'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($key !== false && ! isset($unique[$key])) {
                $unique[$key] = $candidate;
            }
        }

        return array_values($unique);
    }

    protected function isMetadataValidationError(RuntimeException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'invalid metadata')
            || str_contains($message, 'metadata wajib')
            || str_contains($message, 'minimal 1 item');
    }

    protected function normalizeMetadata(array $metadata): array
    {
        $normalized = $this->normalizeMetadataValue($metadata);

        if (! is_array($normalized) || $normalized === []) {
            return $this->fallbackMetadata();
        }

        $customer = (array) ($normalized['customer'] ?? []);
        $products = array_values(array_filter(
            (array) ($normalized['products'] ?? []),
            static fn ($product) => is_array($product)
                && filled($product['product_code'] ?? null)
                && filled($product['product_name'] ?? null)
        ));

        if ($products === []) {
            return $this->fallbackMetadata();
        }

        $products = array_map(function (array $product): array {
            return array_filter([
                'product_code' => $product['product_code'],
                'product_name' => $product['product_name'],
                'product_thumbnail' => $product['product_thumbnail']
                    ?? $this->publicUrl('images/wiboost-logo.png'),
                'product_url' => $product['product_url'] ?? $this->publicUrl('user/history'),
            ], static fn ($value) => filled($value));
        }, $products);

        return [
            'customer' => $customer !== [] ? $customer : [
                'id' => 'guest',
                'name' => 'Pelanggan Wiboost',
            ],
            'products' => $products,
        ];
    }

    protected function normalizeMetadataValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $isList = array_keys($value) === range(0, count($value) - 1);

            if ($isList) {
                $items = [];

                foreach ($value as $item) {
                    $normalizedItem = $this->normalizeMetadataValue($item);

                    if (filled($normalizedItem)) {
                        $items[] = $normalizedItem;
                    }
                }

                return $items;
            }

            $normalized = [];

            foreach ($value as $key => $item) {
                $normalizedItem = $this->normalizeMetadataValue($item);

                if (filled($normalizedItem)) {
                    $normalized[(string) $key] = $normalizedItem;
                }
            }

            return $normalized;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_scalar($value) || $value === null) {
            return $this->metadataText((string) $value);
        }

        return $this->metadataText(json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: 'WIBOOST');
    }

    protected function metadataText(string $value, int $limit = 120): string
    {
        $value = trim(strip_tags($value));
        $value = preg_replace('/\s+/', ' ', $value) ?: 'Produk Wiboost';

        if (function_exists('mb_strlen') && mb_strlen($value) > $limit) {
            return mb_substr($value, 0, $limit);
        }

        return strlen($value) > $limit ? substr($value, 0, $limit) : $value;
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
