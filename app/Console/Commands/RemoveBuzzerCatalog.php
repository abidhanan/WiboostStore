<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RemoveBuzzerCatalog extends Command
{
    protected $signature = 'wiboost:remove-buzzer
        {--execute : Jalankan penghapusan. Tanpa opsi ini hanya preview.}
        {--force-with-transactions : Tetap hapus walau ada transaksi terkait. Tidak disarankan.}';

    protected $description = 'Hapus kategori Buzzer beserta produk manual terkait tanpa menyentuh produk API.';

    protected const CATEGORY_SLUGS = [
        'buzzer',
        'buzzer-instagram',
        'buzzer-tiktok',
        'buzzer-youtube',
        'buzzer-twitter-x',
        'buzzer-lainnya',
    ];

    protected const API_PROVIDERS = ['digiflazz', 'ordersosmed'];

    public function handle(): int
    {
        $targets = Category::query()
            ->with('parent.parent.parent')
            ->whereIn('slug', self::CATEGORY_SLUGS)
            ->get();

        $categoryIds = $this->categoryIdsWithDescendants($targets);
        $targetedProducts = $this->targetedProducts($categoryIds);
        $protectedApiProducts = $this->protectedApiProducts($categoryIds);
        $productIds = $targetedProducts->pluck('id');
        $transactionCount = $productIds->isEmpty()
            ? 0
            : DB::table('transactions')->whereIn('product_id', $productIds)->count();

        $this->table(
            ['Kategori target', 'Produk Buzzer/manual', 'Produk API dilindungi', 'Transaksi terkait'],
            [[
                $targets->map(fn (Category $category) => $category->breadcrumb_name)->implode("\n") ?: '-',
                $targetedProducts->count(),
                $protectedApiProducts->count(),
                $transactionCount,
            ]]
        );

        if (! $this->option('execute')) {
            $this->warn('Preview saja. Tambahkan --execute untuk benar-benar menghapus.');

            return self::SUCCESS;
        }

        if ($protectedApiProducts->isNotEmpty()) {
            $this->error('Dibatalkan: ada produk API Digiflazz/OrderSosmed di kategori target.');
            $this->warn('Pindahkan produk API itu dulu agar sambungan provider yang sudah ada tidak ikut terhapus.');

            return self::FAILURE;
        }

        if ($transactionCount > 0 && ! $this->option('force-with-transactions')) {
            $this->error('Dibatalkan: ada transaksi terkait produk Buzzer.');
            $this->warn('Ini sengaja diblok supaya riwayat order tidak terhapus. Kalau tetap ingin hapus semua, jalankan ulang dengan --force-with-transactions.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($categoryIds, $productIds) {
            if ($productIds->isNotEmpty()) {
                Product::query()->whereIn('id', $productIds)->delete();
            }

            Category::query()
                ->whereIn('id', $categoryIds)
                ->get()
                ->sortByDesc(fn (Category $category) => $category->depth)
                ->each(function (Category $category) {
                    if ($category->products()->exists() || $category->children()->exists()) {
                        return;
                    }

                    if ($category->image && str_starts_with((string) $category->image, 'categories/generated/')) {
                        Storage::disk('public')->delete($category->image);
                    }

                    $category->delete();
                });
        });

        $this->info('Kategori dan produk Buzzer berhasil dihapus dari website.');

        return self::SUCCESS;
    }

    protected function targetedProducts(array $categoryIds)
    {
        return Product::query()
            ->where(function ($query) use ($categoryIds) {
                if ($categoryIds !== []) {
                    $query->whereIn('category_id', $categoryIds);
                }

                $query->orWhere(function ($nameQuery) {
                    $nameQuery->where('process_type', 'manual')
                        ->where(function ($textQuery) {
                            $textQuery->where('name', 'like', '%buzzer%')
                                ->orWhere('description', 'like', '%buzzer%');
                        });
                });
            })
            ->where(function ($query) {
                $query->whereNull('provider_source')
                    ->orWhere('provider_source', '')
                    ->orWhereNotIn('provider_source', self::API_PROVIDERS);
            })
            ->where(function ($query) {
                $query->whereNull('provider_id')
                    ->orWhere('provider_id', '')
                    ->orWhereNotIn('provider_id', self::API_PROVIDERS);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'category_id', 'process_type']);
    }

    protected function protectedApiProducts(array $categoryIds)
    {
        if ($categoryIds === []) {
            return collect();
        }

        return Product::query()
            ->whereIn('category_id', $categoryIds)
            ->where(function ($query) {
                $query->whereIn('provider_source', self::API_PROVIDERS)
                    ->orWhereIn('provider_id', self::API_PROVIDERS);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'provider_source', 'provider_id']);
    }

    protected function categoryIdsWithDescendants($targets): array
    {
        $ids = $targets->pluck('id')->all();
        $queue = $ids;

        while ($queue !== []) {
            $childIds = Category::query()
                ->whereIn('parent_id', $queue)
                ->pluck('id')
                ->all();
            $childIds = array_values(array_diff($childIds, $ids));

            if ($childIds === []) {
                break;
            }

            $ids = array_values(array_unique(array_merge($ids, $childIds)));
            $queue = $childIds;
        }

        return $ids;
    }
}
