<?php

namespace App\Services;

use App\Mail\OrderSuccessMail;
use App\Models\Product;
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

        if (in_array($transaction->order_status, ['success', 'failed'], true)) {
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

        if ($this->shouldQueuePremiumBuyerInputOrder($transaction)) {
            $this->handlePremiumBuyerInputOrder($transaction);

            return;
        }

        match ($transaction->product->process_type) {
            'account', 'number' => $this->handleCredentialOrderOnce($transaction),
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

    public function assignCredential(Transaction $transaction, ProductCredential $credential): bool
    {
        $assignment = DB::transaction(function () use ($transaction, $credential) {
            $lockedTransaction = Transaction::query()
                ->with(['product', 'user'])
                ->lockForUpdate()
                ->find($transaction->id);

            if (! $lockedTransaction) {
                return false;
            }

            if ($lockedTransaction->order_status === 'success' || ! empty($lockedTransaction->credential_data)) {
                return [
                    'transaction_id' => $lockedTransaction->id,
                    'previous_status' => 'success',
                    'already_fulfilled' => true,
                ];
            }

            $lockedCredential = ProductCredential::query()
                ->lockForUpdate()
                ->find($credential->id);

            if (! $lockedCredential
                || ! $lockedCredential->is_active
                || $lockedCredential->current_usage >= $lockedCredential->max_usage) {
                return false;
            }

            $previousStatus = $lockedTransaction->order_status;
            $product = $lockedTransaction->product;

            $lockedCredential->increment('current_usage');
            $lockedTransaction->update([
                'order_status' => 'success',
                'credential_data' => [
                    'credential_id' => $lockedCredential->id,
                    'email' => ($lockedCredential->data_1 !== '-' && $lockedCredential->data_1 !== null) ? $lockedCredential->data_1 : null,
                    'password' => $lockedCredential->data_2,
                    'profile' => $lockedCredential->data_3,
                    'pin' => $lockedCredential->data_4,
                    'link' => $lockedCredential->data_5,
                    'tutorial_link' => $lockedCredential->tutorial_link,
                    'needs_otp' => $lockedCredential->needs_otp,
                    'type' => $product?->process_type,
                ],
                'target_notes' => $lockedCredential->needs_otp
                    ? 'Pesanan ini membutuhkan bantuan admin atau link tutorial tambahan untuk OTP / akses login.'
                    : 'Data pesanan berhasil dikirim ke akun kamu.',
            ]);

            return [
                'transaction_id' => $lockedTransaction->id,
                'previous_status' => $previousStatus,
                'already_fulfilled' => false,
            ];
        });

        if ($assignment === false) {
            return false;
        }

        if ($assignment['already_fulfilled']) {
            return true;
        }

        $transaction = Transaction::with(['product', 'user'])->find($assignment['transaction_id']);

        if (! $transaction) {
            return false;
        }

        $product = $transaction->product;
        $previousStatus = $assignment['previous_status'];

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

        return true;
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

    protected function validateOrderSosmedProviderProduct(Product $product): array
    {
        $providerProductId = $this->orderSosmedService->normalizeServiceId((string) $product->provider_product_id);

        if ($providerProductId === '') {
            return [
                'success' => false,
                'message' => 'Produk OrderSosmed belum memiliki ID layanan provider.',
                'disable_product' => true,
                'response_data' => [
                    'provider_product_id' => $product->provider_product_id,
                    'provider_product_name' => $product->name,
                ],
            ];
        }

        $cacheKey = 'ordersosmed:service-validation-list';
        $servicesResponse = Cache::get($cacheKey);

        if (! is_array($servicesResponse)) {
            $servicesResponse = $this->orderSosmedService->getServices();

            if (($servicesResponse['success'] ?? false) && is_array($servicesResponse['data'] ?? null)) {
                Cache::put($cacheKey, $servicesResponse, now()->addMinutes(10));
            }
        }

        if (! ($servicesResponse['success'] ?? false) || ! is_array($servicesResponse['data'] ?? null)) {
            return [
                'success' => false,
                'message' => 'Validasi layanan OrderSosmed gagal. Saldo sudah dikembalikan otomatis.',
                'response_data' => [
                    'provider_product_id' => $providerProductId,
                    'provider_product_name' => $product->name,
                    'provider_validation_message' => $servicesResponse['message'] ?? 'Daftar layanan provider tidak tersedia.',
                ],
            ];
        }

        $originalProviderProductId = $providerProductId;
        $providerService = $this->findOrderSosmedProviderService($servicesResponse['data'], $providerProductId);
        $providerServiceName = $providerService ? $this->resolveOrderSosmedProviderServiceName($providerService) : '';
        $missingTokens = $providerService
            ? $this->missingOrderSosmedGuardTokens((string) $product->name, $providerServiceName)
            : [];

        if (! $providerService || $missingTokens !== []) {
            $matchedService = $this->findOrderSosmedProviderServiceForProduct(
                $servicesResponse['data'],
                $product,
                $providerProductId
            );

            if ($matchedService) {
                $matchedProviderProductId = $this->resolveOrderSosmedProviderServiceId($matchedService);

                if ($matchedProviderProductId !== '') {
                    $providerProductId = $matchedProviderProductId;
                    $providerService = $matchedService;
                    $providerServiceName = $this->resolveOrderSosmedProviderServiceName($providerService);
                    $missingTokens = $this->missingOrderSosmedGuardTokens((string) $product->name, $providerServiceName);

                    if ($missingTokens === []) {
                        $this->rememberOrderSosmedProviderProductId($product, $providerProductId);
                    }
                }
            }
        }

        if (! $providerService) {
            return [
                'success' => false,
                'message' => 'ID layanan OrderSosmed tidak ditemukan di daftar provider. Saldo sudah dikembalikan otomatis.',
                'disable_product' => true,
                'response_data' => [
                    'provider_product_id' => $providerProductId,
                    'provider_product_name' => $product->name,
                ],
            ];
        }

        if ($missingTokens !== []) {
            return [
                'success' => false,
                'message' => 'Layanan OrderSosmed tidak cocok dengan produk yang dipilih. Pesanan dibatalkan dan saldo sudah dikembalikan otomatis.',
                'disable_product' => true,
                'response_data' => [
                    'provider_product_id' => $originalProviderProductId,
                    'provider_product_name' => $product->name,
                    'provider_service_name' => $providerServiceName,
                    'provider_mismatch_tokens' => $missingTokens,
                ],
            ];
        }

        $providerServiceSnapshot = [
            'id' => $providerProductId,
            'name' => $providerServiceName,
            'category' => data_get($providerService, 'category') ?? data_get($providerService, 'category_name'),
            'previous_id' => $originalProviderProductId !== $providerProductId ? $originalProviderProductId : null,
        ];

        return [
            'success' => true,
            'provider_product_id' => $providerProductId,
            'provider_service' => $providerServiceSnapshot,
        ];
    }

    protected function findOrderSosmedProviderService(array $services, string $providerProductId): ?array
    {
        foreach ($services as $service) {
            if (! is_array($service)) {
                continue;
            }

            foreach (['id', 'service_id', 'service', 'sid', 'code'] as $idKey) {
                $candidate = data_get($service, $idKey);

                if (is_scalar($candidate)
                    && $this->orderSosmedService->normalizeServiceId((string) $candidate) === $providerProductId) {
                    return $service;
                }
            }

            foreach (['data', 'services', 'items'] as $childrenKey) {
                $children = $service[$childrenKey] ?? null;

                if (is_array($children)) {
                    $match = $this->findOrderSosmedProviderService($children, $providerProductId);

                    if ($match) {
                        return $match;
                    }
                }
            }
        }

        return null;
    }

    protected function findOrderSosmedProviderServiceForProduct(array $services, Product $product, string $currentProviderProductId): ?array
    {
        $productName = (string) $product->name;
        $productText = $this->normalizeOrderSosmedComparableText($productName);

        if ($productText === '') {
            return null;
        }

        $bestMatch = null;
        $bestScore = 0.0;

        foreach ($this->flattenOrderSosmedProviderServices($services) as $service) {
            $serviceId = $this->resolveOrderSosmedProviderServiceId($service);
            $serviceName = $this->resolveOrderSosmedProviderServiceName($service);
            $serviceText = $this->normalizeOrderSosmedComparableText($serviceName);

            if ($serviceId === '' || $serviceName === '' || $serviceText === '') {
                continue;
            }

            if ($serviceId === $currentProviderProductId) {
                continue;
            }

            if ($this->missingOrderSosmedGuardTokens($productName, $serviceName) !== []) {
                continue;
            }

            if ($productText === $serviceText) {
                return $service;
            }

            $score = 0.0;

            if (str_contains(' ' . $serviceText . ' ', ' ' . $productText . ' ')
                || str_contains(' ' . $productText . ' ', ' ' . $serviceText . ' ')) {
                $score = 95.0;
            } else {
                similar_text($productText, $serviceText, $score);
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $service;
            }
        }

        return $bestScore >= 88.0 ? $bestMatch : null;
    }

    protected function flattenOrderSosmedProviderServices(array $services): array
    {
        $flat = [];

        foreach ($services as $service) {
            if (! is_array($service)) {
                continue;
            }

            if ($this->resolveOrderSosmedProviderServiceId($service) !== ''
                || $this->resolveOrderSosmedProviderServiceName($service) !== '') {
                $flat[] = $service;
            }

            foreach (['data', 'services', 'items'] as $childrenKey) {
                $children = $service[$childrenKey] ?? null;

                if (is_array($children)) {
                    $flat = array_merge($flat, $this->flattenOrderSosmedProviderServices($children));
                }
            }
        }

        return $flat;
    }

    protected function resolveOrderSosmedProviderServiceId(array $service): string
    {
        foreach (['id', 'service_id', 'service', 'sid', 'code'] as $idKey) {
            $candidate = data_get($service, $idKey);

            if (is_scalar($candidate) && trim((string) $candidate) !== '') {
                return $this->orderSosmedService->normalizeServiceId((string) $candidate);
            }
        }

        return '';
    }

    protected function resolveOrderSosmedProviderServiceName(array $service): string
    {
        foreach (['name', 'service_name', 'title', 'nama_layanan', 'nama'] as $key) {
            $value = data_get($service, $key);

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        $serviceValue = data_get($service, 'service');

        return is_scalar($serviceValue) && ! is_numeric($serviceValue) ? trim((string) $serviceValue) : '';
    }

    protected function missingOrderSosmedGuardTokens(string $productName, string $providerServiceName): array
    {
        if (trim($providerServiceName) === '') {
            return [];
        }

        $providerText = ' ' . $this->normalizeOrderSosmedComparableText($providerServiceName) . ' ';

        return array_values(array_filter(
            $this->extractOrderSosmedGuardTokens($productName),
            fn (string $token) => ! str_contains($providerText, ' ' . $token . ' ')
        ));
    }

    protected function extractOrderSosmedGuardTokens(string $text): array
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $ascii = Str::lower(Str::ascii($text));
        $tokens = [];

        if (preg_match_all('/\b\d+(?:[.,]\d+)?\s*(?:k|m|b|d)\b/u', $ascii, $matches)) {
            foreach ($matches[0] as $match) {
                $tokens[] = preg_replace('/[^a-z0-9]+/u', '', $match) ?: '';
            }
        }

        $normalized = $this->normalizeOrderSosmedComparableText($text);

        foreach ([
            'instagram', 'tiktok', 'youtube', 'facebook', 'telegram', 'twitter',
            'like', 'likes', 'follower', 'followers', 'view', 'views', 'comment', 'comments',
            'bot', 'profile', 'profiles', 'hq', 'account', 'accounts', 'user', 'users',
            'indo', 'indonesia', 'real', 'active', 'premium', 'private', 'sharing',
        ] as $keyword) {
            if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/u', $normalized)) {
                $tokens[] = $keyword;
            }
        }

        if (str_contains($normalized, ' no refill ')) {
            $tokens[] = 'no refill';
        } elseif (str_contains($normalized, ' refill ')) {
            $tokens[] = 'refill';
        }

        return collect($tokens)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function normalizeOrderSosmedComparableText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = Str::lower(Str::ascii($text));
        $text = preg_replace('/[^a-z0-9]+/u', ' ', $text) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    protected function disableOrderSosmedProduct(Product $product, string $reason): void
    {
        $product->forceFill([
            'is_active' => false,
            'status' => 'inactive',
        ])->saveQuietly();

        Log::warning('Produk OrderSosmed dinonaktifkan karena validasi layanan gagal.', [
            'product_id' => $product->id,
            'provider_product_id' => $product->provider_product_id,
            'name' => $product->name,
            'reason' => $reason,
        ]);
    }

    protected function rememberOrderSosmedProviderProductId(Product $product, string $providerProductId): void
    {
        if ((string) $product->provider_product_id === $providerProductId) {
            return;
        }

        $product->forceFill([
            'provider_product_id' => $providerProductId,
        ])->saveQuietly();
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

        try {
            $providerProductId = (string) $product->provider_product_id;
            $providerServiceSnapshot = null;

            if ($providerSource === 'ordersosmed') {
                $validation = $this->validateOrderSosmedProviderProduct($product);

                if (! ($validation['success'] ?? false)) {
                    if (($validation['disable_product'] ?? false) === true) {
                        $this->disableOrderSosmedProduct(
                            $product,
                            $validation['message'] ?? 'Validasi layanan OrderSosmed gagal.'
                        );
                    }

                    $this->markAsFailed(
                        $transaction,
                        $validation['message'] ?? 'Validasi layanan OrderSosmed gagal. Saldo sudah dikembalikan otomatis.',
                        $validation['response_data'] ?? []
                    );

                    return;
                }

                $providerProductId = $validation['provider_product_id'] ?? $providerProductId;
                $providerServiceSnapshot = $validation['provider_service'] ?? null;
            }

            $response = $providerSource === 'digiflazz'
                ? $this->digiflazzService->placeOrder(
                    $providerProductId,
                    (string) $transaction->provider_customer_no,
                    (string) $transaction->invoice_number
                )
                : $this->orderSosmedService->placeOrder(
                    $providerProductId,
                    (string) $transaction->target_data,
                    max(1, (int) ($transaction->provider_order_quantity ?: $product->provider_quantity ?: 1))
                );
        } catch (\Throwable $exception) {
            Log::error('Provider gagal menerima pesanan berbayar.', [
                'transaction_id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'provider' => $providerSource,
                'error' => $exception->getMessage(),
            ]);

            $this->markAsFailed(
                $transaction,
                'Provider tidak dapat memproses pesanan. Saldo sudah dikembalikan otomatis.',
                ['provider_error' => $exception->getMessage()]
            );

            return;
        }

        $previousStatus = $transaction->order_status;
        $transaction->update([
            'response_data' => array_merge($transaction->response_data ?? [], [
                'provider_order_id' => $response['provider_order_id'] ?? null,
                'provider_service_id' => $providerProductId,
                'provider_service_name' => $providerServiceSnapshot['name'] ?? null,
                'provider_service' => $providerServiceSnapshot,
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
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $credential = $this->selectCredentialForTransaction($transaction);

            if ($credential && $this->assignCredential($transaction, $credential)) {
                return;
            }
        }

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
    }

    protected function shouldQueuePremiumBuyerInputOrder(Transaction $transaction): bool
    {
        $product = $transaction->product;

        if (! $product || ! in_array($product->process_type, ['account', 'number'], true)) {
            return false;
        }

        if (! $this->isAplikasiPremiumProduct($product)) {
            return false;
        }

        return (bool) $product->requires_buyer_email || $transaction->has_order_input;
    }

    protected function isAplikasiPremiumProduct(Product $product): bool
    {
        if (! $product->relationLoaded('category')) {
            $product->loadMissing('category.parent.parent.parent');
        } else {
            $product->category?->loadMissing('parent.parent.parent');
        }

        $category = $product->category;

        while ($category) {
            if ($category->slug === 'aplikasi-premium') {
                return true;
            }

            $category = $category->parent;
        }

        return false;
    }

    protected function handlePremiumBuyerInputOrder(Transaction $transaction): void
    {
        $previousStatus = $transaction->order_status;

        $transaction->update([
            'order_status' => 'processing',
            'target_notes' => 'Pesanan sudah dibayar dan menunggu admin memproses data buyer.',
        ]);

        if ($previousStatus !== 'processing') {
            $this->discordWebhookService->sendOrderAlert(
                $transaction,
                'Pesanan aplikasi premium butuh tindak lanjut',
                'Data buyer sudah diterima. Silakan proses pesanan premium ini dari panel admin.',
                16433050
            );
        }
    }

    protected function handleCredentialOrderOnce(Transaction $transaction): void
    {
        $lockKey = 'credential-fulfillment:' . $transaction->id;

        if (! Cache::add($lockKey, true, now()->addMinutes(5))) {
            return;
        }

        try {
            $freshTransaction = Transaction::with(['product', 'user'])->find($transaction->id);

            if (! $freshTransaction
                || $freshTransaction->payment_status !== 'paid'
                || in_array($freshTransaction->order_status, ['success', 'failed'], true)) {
                return;
            }

            $this->handleCredentialOrder($freshTransaction);
        } finally {
            Cache::forget($lockKey);
        }
    }

    protected function selectCredentialForTransaction(Transaction $transaction): ?ProductCredential
    {
        $availableCredentials = ProductCredential::where('product_id', $transaction->product_id)
            ->where('is_active', true)
            ->whereColumn('current_usage', '<', 'max_usage')
            ->orderBy('current_usage')
            ->orderBy('id')
            ->get();

        if ($availableCredentials->isEmpty()) {
            return null;
        }

        if ($availableCredentials->count() === 1) {
            return $availableCredentials->first();
        }

        $usedCredentialIds = $this->previousCredentialIdsForUserProduct($transaction, $availableCredentials);

        $neverUsedByThisUser = $availableCredentials
            ->reject(fn (ProductCredential $credential) => in_array((int) $credential->id, $usedCredentialIds, true))
            ->values();

        if ($neverUsedByThisUser->isNotEmpty()) {
            return $neverUsedByThisUser->first();
        }

        $lastCredentialId = $usedCredentialIds[0] ?? null;
        $notLastUsed = $availableCredentials
            ->reject(fn (ProductCredential $credential) => (int) $credential->id === $lastCredentialId)
            ->values();

        return ($notLastUsed->isNotEmpty() ? $notLastUsed : $availableCredentials)->first();
    }

    protected function previousCredentialIdsForUserProduct(Transaction $transaction, $availableCredentials): array
    {
        $availableByFingerprint = $availableCredentials
            ->mapWithKeys(fn (ProductCredential $credential) => [
                $this->credentialFingerprint([
                    'email' => $credential->data_1 !== '-' ? $credential->data_1 : null,
                    'password' => $credential->data_2,
                    'profile' => $credential->data_3,
                    'pin' => $credential->data_4,
                    'link' => $credential->data_5,
                ]) => (int) $credential->id,
            ])
            ->all();

        return Transaction::query()
            ->where('user_id', $transaction->user_id)
            ->where('product_id', $transaction->product_id)
            ->where('id', '<>', $transaction->id)
            ->where('order_status', 'success')
            ->whereNotNull('credential_data')
            ->latest()
            ->limit(50)
            ->get(['credential_data'])
            ->map(function (Transaction $history) use ($availableByFingerprint) {
                $credentialData = $history->credential_data ?? [];
                $credentialId = data_get($credentialData, 'credential_id');

                if (filled($credentialId)) {
                    return (int) $credentialId;
                }

                return $availableByFingerprint[$this->credentialFingerprint($credentialData)] ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function credentialFingerprint(array $credentialData): string
    {
        return collect(['email', 'password', 'profile', 'pin', 'link'])
            ->map(fn (string $key) => trim((string) ($credentialData[$key] ?? '')))
            ->implode('|');
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

        DB::transaction(function () use ($transaction) {
            $lockedTransaction = Transaction::query()
                ->with('product')
                ->lockForUpdate()
                ->find($transaction->id);

            if (! $lockedTransaction
                || $lockedTransaction->payment_status !== 'paid'
                || $lockedTransaction->order_status !== 'failed'
                || $lockedTransaction->refunded_at !== null) {
                return;
            }

            $alreadyRefunded = WalletHistory::where('invoice_number', $lockedTransaction->invoice_number)
                ->where('type', 'refund')
                ->exists();

            if ($alreadyRefunded) {
                $lockedTransaction->forceFill(['refunded_at' => now()])->save();

                return;
            }

            $user = User::query()->lockForUpdate()->find($lockedTransaction->user_id);

            if (! $user) {
                return;
            }

            $refundAmount = (float) $lockedTransaction->amount;

            WalletHistory::create([
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $refundAmount,
                'description' => 'Refund otomatis untuk pesanan gagal: ' . ($lockedTransaction->product->name ?? 'Produk'),
                'invoice_number' => $lockedTransaction->invoice_number,
            ]);

            $user->increment('balance', $refundAmount);
            $lockedTransaction->forceFill(['refunded_at' => now()])->save();
        });
    }

    protected function rewardUserIfNeeded(Transaction $transaction, ?string $previousStatus, string $nextStatus): void
    {
        if ($nextStatus !== 'success') {
            return;
        }

        DB::transaction(function () use ($transaction) {
            $lockedTransaction = Transaction::query()
                ->lockForUpdate()
                ->find($transaction->id);

            if (! $lockedTransaction
                || $lockedTransaction->order_status !== 'success'
                || $lockedTransaction->points_awarded_at !== null) {
                return;
            }

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

            $lockedTransaction->forceFill(['points_awarded_at' => now()])->save();
        });
    }

    protected function sendSuccessEmail(Transaction $transaction, ?string $previousStatus, bool $onlyWhenSuccess = true): void
    {
        if ($onlyWhenSuccess && $transaction->order_status !== 'success') {
            return;
        }

        if ($transaction->payment_status !== 'paid') {
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
