<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RemoveManualAdminCatalog extends Command
{
    protected $signature = 'wiboost:remove-manual-admin
        {--execute : Jalankan penghapusan. Tanpa opsi ini hanya preview.}
        {--force-with-transactions : Tetap hapus walau ada transaksi terkait. Tidak disarankan.}';

    protected $description = 'Hapus produk dan kategori Manual Admin tanpa menyentuh API OrderSosmed, API Digiflazz, atau stok Aplikasi Premium.';

    protected const API_PROVIDERS = ['digiflazz', 'ordersosmed'];

    public function handle(): int
    {
        $manualCategories = Category::query()
            ->with('parent.parent.parent')
            ->where('fulfillment_type', 'manual_action')
            ->get();

        $categoryIds = $this->categoryIdsWithDescendants($manualCategories);
        $manualProducts = $this->manualProducts($categoryIds);
        $protectedProducts = $this->protectedProducts($categoryIds);
        $productIds = $manualProducts->pluck('id');
        $transactionCount = $productIds->isEmpty()
            ? 0
            : DB::table('transactions')->whereIn('product_id', $productIds)->count();

        $this->table(
            ['Kategori manual', 'Produk manual', 'Produk API/stok dilindungi', 'Transaksi terkait'],
            [[
                $manualCategories->map(fn (Category $category) => $category->breadcrumb_name)->implode("\n") ?: '-',
                $manualProducts->count(),
                $protectedProducts->count(),
                $transactionCount,
            ]]
        );

        if (! $this->option('execute')) {
            $this->warn('Preview saja. Tambahkan --execute untuk benar-benar menghapus.');

            return self::SUCCESS;
        }

        if ($protectedProducts->isNotEmpty()) {
            $this->error('Dibatalkan: ada produk API atau stok premium di kategori manual.');
            $this->warn('Pindahkan produk tersebut dulu agar sambungan provider dan stok premium tidak ikut terhapus.');

            return self::FAILURE;
        }

        if ($transactionCount > 0 && ! $this->option('force-with-transactions')) {
            $this->error('Dibatalkan: ada transaksi terkait produk Manual Admin.');
            $this->warn('Ini sengaja diblok supaya riwayat transaksi tidak rusak. Jika tetap ingin hapus, jalankan ulang dengan --force-with-transactions.');

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

        $this->info('Produk dan kategori Manual Admin berhasil dihapus dari website.');

        return self::SUCCESS;
    }

    protected function manualProducts(array $categoryIds)
    {
        return Product::query()
            ->where(function ($query) use ($categoryIds) {
                $query->where('process_type', 'manual');

                if ($categoryIds !== []) {
                    $query->orWhereIn('category_id', $categoryIds);
                }
            })
            ->where(function ($query) {
                $query->whereNull('provider_source')
                    ->orWhere('provider_source', '')
                    ->orWhereNotIn('provider_source', self::API_PROVIDERS);
            })
            ->where('process_type', 'manual')
            ->orderBy('name')
            ->get(['id', 'name', 'category_id', 'process_type']);
    }

    protected function protectedProducts(array $categoryIds)
    {
        if ($categoryIds === []) {
            return collect();
        }

        return Product::query()
            ->whereIn('category_id', $categoryIds)
            ->where(function ($query) {
                $query->whereIn('provider_source', self::API_PROVIDERS)
                    ->orWhereIn('provider_id', self::API_PROVIDERS)
                    ->orWhere('process_type', 'account');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'provider_source', 'provider_id', 'process_type']);
    }

    protected function categoryIdsWithDescendants($targets): array
    {
        $ids = $targets->pluck('id')->all();
        $cursor = $ids;

        while ($cursor !== []) {
            $children = Category::query()
                ->whereIn('parent_id', $cursor)
                ->pluck('id')
                ->all();

            $children = array_values(array_diff($children, $ids));

            if ($children === []) {
                break;
            }

            $ids = array_merge($ids, $children);
            $cursor = $children;
        }

        return array_values(array_unique($ids));
    }
}
