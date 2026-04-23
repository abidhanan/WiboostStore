<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Services\OrderSosmedGuestCatalogService;
use App\Services\OrderSosmedService;
use App\Support\WiboostCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncOrderSosmedServices extends Command
{
    protected $signature = 'sync:ordersosmed';

    protected $description = 'Sync layanan dari OrderSosmed ke database Wiboost';

    public function handle(
        OrderSosmedService $orderSosmedService,
        OrderSosmedGuestCatalogService $guestCatalogService
    ): int
    {
        $defaultCategoryId = Category::query()
            ->where('slug', 'suntik-sosmed')
            ->value('id') ?? Category::query()->orderBy('id')->value('id');

        if (! $defaultCategoryId) {
            $this->error('Gagal sync: belum ada kategori untuk menampung layanan OrderSosmed.');

            return self::FAILURE;
        }

        $this->info('Mengambil daftar layanan dari OrderSosmed...');
        $response = $orderSosmedService->getServices();
        $syncMode = 'api';

        if (! ($response['success'] ?? false) || empty($response['data'])) {
            $this->warn('Endpoint API private OrderSosmed belum merespons daftar layanan. Beralih ke katalog publik...');
            $response = $guestCatalogService->getServices(['sosmed']);
            $syncMode = 'guest';
        }

        $services = collect($response['data'] ?? [])
            ->filter(fn (array $service) => $this->shouldImportService($service))
            ->values();

        if (! ($response['success'] ?? false) || $services->isEmpty()) {
            $this->error('Gagal ambil layanan OrderSosmed: ' . ($response['message'] ?? 'Unknown error'));

            return self::FAILURE;
        }

        if ($syncMode === 'guest') {
            $this->warn('Mode fallback aktif: katalog publik berhasil diimpor. Produk OrderSosmed akan disiapkan sebagai pesanan manual sampai endpoint API private dikonfirmasi.');
        }

        $total = $services->count();
        $success = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($services as $service) {
            try {
                $providerProductId = $this->resolveProviderProductId($service);
                $name = $this->normalizeProductName(
                    (string) ($service['name'] ?? $service['service_name'] ?? $service['service'] ?? '')
                );

                if ($providerProductId === null || $name === '') {
                    $failed++;
                    $bar->advance();
                    continue;
                }

                $providerQuantity = $this->resolveProviderQuantity($service);
                $basePrice = $this->resolveBasePrice($service, $providerQuantity);
                $sellingPrice = $basePrice > 0
                    ? ceil(($basePrice * 1.10) / 100) * 100
                    : 0;

                $categoryId = $this->resolveCategoryId($service, $defaultCategoryId);
                $targetMeta = $this->resolveTargetMeta($service, $syncMode);
                $processType = $syncMode === 'api' ? 'api' : 'manual';

                $product = $this->findExistingProduct($providerProductId, $name, $syncMode);

                if (! $product->exists && blank($product->slug)) {
                    $product->slug = Str::slug($name) . '-' . Str::random(5);
                }

                $product->fill([
                    'category_id' => $categoryId,
                    'provider_id' => 'ordersosmed',
                    'provider_source' => 'ordersosmed',
                    'provider_quantity' => $providerQuantity,
                    'process_type' => $processType,
                    'name' => $name,
                    'description' => $this->buildDescription($service, $syncMode),
                    'price' => $sellingPrice,
                    'provider_product_id' => $providerProductId,
                    'target_label' => $targetMeta['label'],
                    'target_placeholder' => $targetMeta['placeholder'],
                    'target_hint' => $targetMeta['hint'],
                    'is_active' => $sellingPrice > 0,
                    'status' => $sellingPrice > 0 ? 'active' : 'inactive',
                    'stock_reminder' => 0,
                ]);

                $product->save();
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Gagal sync service OrderSosmed: ' . $e->getMessage(), [
                    'service' => $service,
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nLayanan OrderSosmed selesai disinkronkan.");
        $this->info("Berhasil: {$success}");
        $this->info('Mode sync: ' . ($syncMode === 'api' ? 'API private' : 'Katalog publik (manual order)'));

        if ($failed > 0) {
            $this->warn("Gagal: {$failed}");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function resolveProviderProductId(array $service): ?string
    {
        $id = $service['id'] ?? $service['service'] ?? $service['service_id'] ?? null;

        if (! filled($id)) {
            return null;
        }

        $catalogType = $service['_ordersosmed_catalog_type'] ?? null;

        if (filled($catalogType)) {
            return $catalogType . ':' . $id;
        }

        return (string) $id;
    }

    protected function resolveProviderQuantity(array $service): int
    {
        $min = (int) ($service['min'] ?? $service['minimum'] ?? 0);

        return max(1, $min);
    }

    protected function resolveBasePrice(array $service, int $providerQuantity): float
    {
        if (($service['_ordersosmed_pricing_mode'] ?? null) === 'per_1000') {
            $rate = (float) ($service['rate'] ?? 0);

            return $rate > 0 ? max(0, ($rate / 1000) * $providerQuantity) : 0;
        }

        if (isset($service['price']) && is_numeric($service['price'])) {
            return (float) $service['price'];
        }

        $rate = (float) ($service['rate'] ?? 0);

        if ($rate <= 0) {
            return 0;
        }

        return max(0, ($rate / 1000) * $providerQuantity);
    }

    protected function resolveCategoryId(array $service, int $defaultCategoryId): int
    {
        $subcategorySlug = WiboostCatalog::resolveOrdersosmedSubcategorySlug(
            (string) ($service['name'] ?? $service['service_name'] ?? ''),
            (string) ($service['category'] ?? $service['type'] ?? ''),
            (string) ($service['description'] ?? $service['note'] ?? ''),
        );

        return Category::query()->where('slug', $subcategorySlug)->value('id')
            ?? Category::query()->where('slug', 'suntik-sosmed')->value('id')
            ?? $defaultCategoryId;
    }

    protected function buildDescription(array $service, string $syncMode): string
    {
        $chunks = array_filter([
            $service['description'] ?? null,
            $service['note'] ?? null,
            isset($service['category']) ? 'Kategori provider: ' . $service['category'] : null,
            isset($service['min']) ? 'Minimum order: ' . $service['min'] : null,
            isset($service['max']) ? 'Maximum order: ' . $service['max'] : null,
            $syncMode === 'guest'
                ? 'Sinkron dari katalog publik OrderSosmed. Pemrosesan pesanan sementara dilakukan manual oleh admin.'
                : null,
        ]);

        return $chunks !== [] ? implode("\n", $chunks) : 'Layanan otomatis dari provider OrderSosmed.';
    }

    protected function resolveTargetMeta(array $service, string $syncMode): array
    {
        $meta = WiboostCatalog::targetMetaForTopCategory('suntik-sosmed') ?? [
            'label' => 'Username akun / link postingan',
            'placeholder' => 'Contoh: @username atau https://instagram.com/p/abc123',
            'hint' => 'Masukkan username akun atau link postingan yang aktif sesuai layanan suntik sosmed yang dipilih.',
        ];

        if ($syncMode === 'guest') {
            $meta['hint'] .= ' Saat ini pesanan OrderSosmed diproses manual oleh admin sambil menunggu API private provider pulih.';
        }

        return $meta;
    }

    protected function shouldImportService(array $service): bool
    {
        $catalogType = strtolower((string) ($service['_ordersosmed_catalog_type'] ?? 'sosmed'));
        $category = strtolower((string) ($service['category'] ?? $service['type'] ?? ''));
        $name = strtolower((string) ($service['name'] ?? $service['service_name'] ?? ''));

        if ($catalogType !== 'sosmed') {
            return false;
        }

        if (str_contains($category, 'buzzer') || str_contains($name, 'buzzer')) {
            return false;
        }

        return true;
    }

    protected function categoryIdBySlug(string $slug, int $fallbackId): int
    {
        return Category::query()->where('slug', $slug)->value('id') ?? $fallbackId;
    }

    protected function normalizeProductName(string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            return '';
        }

        return trim(Str::substr($name, 0, 255));
    }

    protected function findExistingProduct(string $providerProductId, string $name, string $syncMode): Product
    {
        $exactMatch = Product::query()
            ->where('provider_source', 'ordersosmed')
            ->where('provider_product_id', $providerProductId)
            ->first();

        if ($exactMatch) {
            return $exactMatch;
        }

        if ($syncMode === 'api') {
            $suffixMatches = Product::query()
                ->where('provider_source', 'ordersosmed')
                ->where('provider_product_id', 'like', '%:' . $providerProductId)
                ->get();

            if ($suffixMatches->count() === 1) {
                return $suffixMatches->first();
            }

            $nameMatch = Product::query()
                ->where('provider_source', 'ordersosmed')
                ->where('process_type', 'manual')
                ->where('name', $name)
                ->orderByDesc('updated_at')
                ->first();

            if ($nameMatch) {
                return $nameMatch;
            }
        }

        return new Product([
            'provider_source' => 'ordersosmed',
            'provider_product_id' => $providerProductId,
        ]);
    }
}
