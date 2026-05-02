<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RemoveDigiflazzExcludedCategories extends Command
{
    protected $signature = 'wiboost:remove-digiflazz-excluded-categories
        {--execute : Jalankan penghapusan. Tanpa opsi ini hanya preview.}
        {--force-with-transactions : Tetap hapus produk walau punya transaksi terkait. Tidak disarankan.}';

    protected $description = 'Hapus kategori Digiflazz yang tidak dipakai beserta layanan di dalamnya.';

    public function handle(): int
    {
        $targets = $this->targetCategories();

        if ($targets->isEmpty()) {
            $this->warn('Tidak ada kategori target yang ditemukan.');

            return self::SUCCESS;
        }

        $categoryIds = $this->categoryIdsWithDescendants($targets);
        $productIds = Product::query()
            ->whereIn('category_id', $categoryIds)
            ->where('provider_source', 'digiflazz')
            ->pluck('id');
        $otherProductCount = Product::query()
            ->whereIn('category_id', $categoryIds)
            ->where(function ($query) {
                $query->whereNull('provider_source')
                    ->orWhere('provider_source', '!=', 'digiflazz');
            })
            ->count();
        $transactionCount = $productIds->isEmpty()
            ? 0
            : DB::table('transactions')->whereIn('product_id', $productIds)->count();

        $this->table(
            ['Kategori target', 'Produk Digiflazz', 'Produk non-Digiflazz', 'Transaksi terkait'],
            [[
                $targets->map(fn (Category $category) => $category->breadcrumb_name)->implode("\n"),
                $productIds->count(),
                $otherProductCount,
                $transactionCount,
            ]]
        );

        if (! $this->option('execute')) {
            $this->warn('Preview saja. Tambahkan --execute untuk benar-benar menghapus.');

            return self::SUCCESS;
        }

        if ($otherProductCount > 0) {
            $this->error('Dibatalkan: ada produk non-Digiflazz di kategori target. Pindahkan/hapus manual dulu agar tidak ikut terhapus.');

            return self::FAILURE;
        }

        if ($transactionCount > 0 && ! $this->option('force-with-transactions')) {
            $this->error('Dibatalkan: ada transaksi terkait produk target.');
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
                ->each(fn (Category $category) => $category->delete());
        });

        $this->info('Kategori target dan layanan Digiflazz di dalamnya berhasil dihapus.');

        return self::SUCCESS;
    }

    protected function targetCategories()
    {
        return Category::query()
            ->with('parent.parent.parent.parent')
            ->get()
            ->filter(fn (Category $category) => $this->isTargetCategory($category))
            ->values();
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

    protected function isTargetCategory(Category $category): bool
    {
        $root = $category;

        while ($root->parent) {
            $root = $root->parent;
        }

        $rootText = $this->normalizeText($root->name . ' ' . $root->slug);
        $ownText = $this->normalizeText($category->name . ' ' . $category->slug);
        $breadcrumbText = $this->normalizeText($category->breadcrumb_name . ' ' . $category->slug);

        if (! $this->containsAny($rootText, [' top up game ', ' topup game ', ' game ', ' games '])) {
            return false;
        }

        $isGenshin = $this->containsAny($breadcrumbText, [' genshin impact ', ' genshin ']);
        $isLeague = $this->containsAny($breadcrumbText, [
            ' lol pc ',
            ' league of legends ',
            ' league of legend ',
            ' league of legends pc ',
            ' league of legend pc ',
        ]);

        if ($isGenshin) {
            return $this->containsAny($ownText, [' indonesia ', ' malaysia ']);
        }

        if ($isLeague) {
            return $this->containsAny($ownText, [' umum ', ' malaysia ']);
        }

        return false;
    }

    protected function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = Str::lower($text);
        $text = preg_replace('/[^\pL\pN]+/u', ' ', $text) ?? $text;

        return ' ' . trim(preg_replace('/\s+/u', ' ', $text) ?? $text) . ' ';
    }
}
