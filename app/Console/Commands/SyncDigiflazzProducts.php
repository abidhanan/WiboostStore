<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Services\DigiflazzService;
use App\Support\WiboostCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncDigiflazzProducts extends Command
{
    protected $signature = 'sync:digiflazz';

    protected $description = 'Sync produk dari Digiflazz ke database Wiboost dengan mapping kategori otomatis';

    public function handle(DigiflazzService $digiService): int
    {
        $this->info('--- MEMULAI PROSES SINKRONISASI ---');
        $this->info('Mengambil data dari Digiflazz...');

        $defaultCategoryId = Category::query()->orderBy('id')->value('id');

        if (! $defaultCategoryId) {
            $this->error('Gagal sync: belum ada kategori sama sekali di database.');

            return self::FAILURE;
        }

        $response = $digiService->getPriceList();
        $rawData = $response['raw']['data'] ?? [];

        if (! ($response['success'] ?? false) || ! is_array($rawData) || $rawData === []) {
            $msg = $response['message'] ?? 'Unknown Error';
            $this->error("Gagal ambil data! Pesan: {$msg}");

            return self::FAILURE;
        }

        $products = collect($rawData)
            ->filter(fn ($item) => is_array($item) && filled($item['buyer_sku_code'] ?? null))
            ->values();

        $total = $products->count();
        $this->info("Ditemukan {$total} produk. Memulai mapping...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($products as $item) {
            try {
                $topCategorySlug = WiboostCatalog::resolveDigiflazzTopCategorySlug(
                    (string) ($item['product_name'] ?? ''),
                    (string) ($item['brand'] ?? ''),
                    (string) ($item['category'] ?? ''),
                    (string) ($item['desc'] ?? ''),
                );

                if ($topCategorySlug === null) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $hargaModal = (float) ($item['price'] ?? 0);
                $markup = 1.10;
                $hargaJual = ceil(($hargaModal * $markup) / 100) * 100;

                $subcategorySlug = WiboostCatalog::resolveDigiflazzSubcategorySlug(
                    $topCategorySlug,
                    (string) ($item['product_name'] ?? ''),
                    (string) ($item['brand'] ?? ''),
                    (string) ($item['desc'] ?? ''),
                );
                $categoryId = $subcategorySlug
                    ? (Category::query()->where('slug', $subcategorySlug)->value('id')
                        ?? $this->categoryIdBySlug($topCategorySlug, $defaultCategoryId))
                    : $this->categoryIdBySlug($topCategorySlug, $defaultCategoryId);
                $targetMeta = WiboostCatalog::targetMetaForTopCategory($topCategorySlug) ?? [
                    'label' => null,
                    'placeholder' => null,
                    'hint' => null,
                ];

                $product = Product::firstOrNew([
                    'provider_source' => 'digiflazz',
                    'provider_product_id' => (string) $item['buyer_sku_code'],
                ]);

                if (! $product->exists && blank($product->slug)) {
                    $product->slug = Str::slug((string) ($item['product_name'] ?? 'produk-digiflazz')) . '-' . Str::random(5);
                }

                $isActive = (bool) ($item['buyer_product_status'] ?? false) && (bool) ($item['seller_product_status'] ?? false);

                $product->fill([
                    'category_id' => $categoryId,
                    'provider_id' => 'digiflazz',
                    'provider_source' => 'digiflazz',
                    'process_type' => 'api',
                    'name' => (string) ($item['product_name'] ?? 'Produk Digiflazz'),
                    'description' => (string) ($item['desc'] ?? '-'),
                    'price' => $hargaJual,
                    'provider_product_id' => (string) $item['buyer_sku_code'],
                    'provider_quantity' => 1,
                    'target_label' => $targetMeta['label'],
                    'target_placeholder' => $targetMeta['placeholder'],
                    'target_hint' => $targetMeta['hint'],
                    'is_active' => $isActive,
                    'status' => $isActive ? 'active' : 'inactive',
                    'stock_reminder' => $product->stock_reminder ?? 0,
                ]);

                $product->save();
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Gagal sync SKU ' . ($item['buyer_sku_code'] ?? 'Unknown') . ': ' . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\n--- HASIL SINKRONISASI ---");
        $this->info("Total Produk: {$total}");
        $this->info("Berhasil   : {$success}");
        $this->info("Dilewati   : {$skipped}");

        if ($failed > 0) {
            $this->warn("Gagal      : {$failed} (Cek storage/logs/laravel.log untuk detail)");
        }

        $this->info('--- SELESAI ---');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function categoryIdBySlug(string $slug, int $fallbackId): int
    {
        return Category::query()->where('slug', $slug)->value('id') ?? $fallbackId;
    }
}
