<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Services\DigiflazzService;
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

        foreach ($products as $item) {
            try {
                $hargaModal = (float) ($item['price'] ?? 0);
                $markup = 1.10;
                $hargaJual = ceil(($hargaModal * $markup) / 100) * 100;

                $categoryId = $this->resolveCategoryId($item, $defaultCategoryId);

                $product = Product::firstOrNew([
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

        if ($failed > 0) {
            $this->warn("Gagal      : {$failed} (Cek storage/logs/laravel.log untuk detail)");
        }

        $this->info('--- SELESAI ---');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function resolveCategoryId(array $item, int $defaultCategoryId): int
    {
        $brand = strtolower((string) ($item['brand'] ?? ''));
        $category = strtolower((string) ($item['category'] ?? ''));
        $preferredCategoryId = $defaultCategoryId;

        if (str_contains($brand, 'mobile legends')) {
            $preferredCategoryId = 8;
        } elseif (str_contains($brand, 'free fire')) {
            $preferredCategoryId = 9;
        } elseif (str_contains($brand, 'pubg')) {
            $preferredCategoryId = 10;
        } elseif (str_contains($category, 'pulsa')) {
            $preferredCategoryId = 2;
        } elseif (str_contains($category, 'e-money') || str_contains($brand, 'dana') || str_contains($brand, 'gopay')) {
            $preferredCategoryId = 3;
        }

        return Category::query()->whereKey($preferredCategoryId)->exists()
            ? $preferredCategoryId
            : $defaultCategoryId;
    }
}
