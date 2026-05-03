<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DedupePremiumProducts extends Command
{
    protected $signature = 'wiboost:dedupe-premium-products
        {--execute : Jalankan penghapusan duplikat. Tanpa opsi ini hanya preview.}
        {--force-with-related-data : Tetap hapus duplikat yang punya transaksi/stok. Tidak disarankan.}';

    protected $description = 'Bersihkan produk Aplikasi Premium yang dobel dengan nama dan kategori yang sama.';

    public function handle(): int
    {
        $categoryIds = $this->premiumCategoryIds();

        if ($categoryIds === []) {
            $this->warn('Kategori Aplikasi Premium tidak ditemukan.');

            return self::SUCCESS;
        }

        $groups = $this->duplicateGroups($categoryIds);
        $deleteIds = collect();
        $blocked = collect();
        $rows = [];

        foreach ($groups as $group) {
            $sorted = $this->sortGroupForKeeping($group);
            $keep = $sorted->first();
            $duplicates = $sorted->slice(1)->values();

            foreach ($duplicates as $duplicate) {
                $relatedCount = (int) $duplicate->transactions_count + (int) $duplicate->credentials_count;

                if ($relatedCount > 0 && ! $this->option('force-with-related-data')) {
                    $blocked->push($duplicate);
                    continue;
                }

                $deleteIds->push($duplicate->id);
            }

            $rows[] = [
                $keep->category?->breadcrumb_name ?? '-',
                $keep->name,
                '#' . $keep->id,
                $duplicates->map(fn (Product $product) => '#' . $product->id)->implode(', ') ?: '-',
                $duplicates->sum(fn (Product $product) => (int) $product->transactions_count + (int) $product->credentials_count),
            ];
        }

        $this->table(
            ['Kategori', 'Nama produk', 'Disimpan', 'Duplikat', 'Data terkait duplikat'],
            $rows ?: [['-', '-', '-', '-', 0]]
        );

        if (! $this->option('execute')) {
            $this->warn('Preview saja. Tambahkan --execute untuk menghapus duplikat yang aman.');

            return self::SUCCESS;
        }

        if ($blocked->isNotEmpty()) {
            $this->error('Dibatalkan: ada duplikat yang punya transaksi atau stok kredensial.');
            $this->warn('Ini sengaja diblok supaya riwayat/stok tidak hilang. Cek produk ID: ' . $blocked->pluck('id')->implode(', '));

            return self::FAILURE;
        }

        if ($deleteIds->isEmpty()) {
            $this->info('Tidak ada duplikat Aplikasi Premium yang aman untuk dihapus.');

            return self::SUCCESS;
        }

        Product::query()->whereIn('id', $deleteIds)->delete();

        $this->info('Duplikat Aplikasi Premium berhasil dihapus: ' . $deleteIds->implode(', '));

        return self::SUCCESS;
    }

    protected function premiumCategoryIds(): array
    {
        $rootIds = Category::query()
            ->where('slug', 'aplikasi-premium')
            ->pluck('id')
            ->all();

        $ids = $rootIds;
        $queue = $rootIds;

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

    protected function duplicateGroups(array $categoryIds): Collection
    {
        return Product::query()
            ->with('category.parent.parent')
            ->withCount(['transactions', 'credentials'])
            ->whereIn('category_id', $categoryIds)
            ->where(function ($query) {
                $query->whereNull('provider_source')
                    ->orWhere('provider_source', '');
            })
            ->where(function ($query) {
                $query->whereNull('provider_product_id')
                    ->orWhere('provider_product_id', '');
            })
            ->get()
            ->groupBy(fn (Product $product) => $product->category_id . '|' . Str::lower(trim($product->name)))
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->values();
    }

    protected function sortGroupForKeeping(Collection $group): Collection
    {
        return $group
            ->sort(function (Product $left, Product $right) {
                $leftRelated = (int) $left->transactions_count + (int) $left->credentials_count;
                $rightRelated = (int) $right->transactions_count + (int) $right->credentials_count;

                if ($leftRelated !== $rightRelated) {
                    return $rightRelated <=> $leftRelated;
                }

                if ((bool) $left->is_active !== (bool) $right->is_active) {
                    return (int) $right->is_active <=> (int) $left->is_active;
                }

                return $left->id <=> $right->id;
            })
            ->values();
    }
}
