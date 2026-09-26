<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Support\WiboostCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixSmartfrenUnlimitedMapping extends Command
{
    protected $signature = 'wiboost:fix-smartfren-unlimited
        {--dry-run : Preview tanpa mengubah database}';

    protected $description = 'Pindahkan produk Digiflazz Smartfren Unlimited dari sub kategori Umum ke sub kategori Unlimited yang sudah ada.';

    protected Collection $categories;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->categories = Category::query()
            ->with('parent.parent.parent.parent')
            ->withCount('children')
            ->get()
            ->keyBy('id');

        $target = $this->smartfrenUnlimitedCategory();

        if (! $target) {
            $this->error('Sub kategori Smartfren Unlimited tidak ditemukan. Pastikan sub kategori manual Smartfren > Unlimited masih ada.');

            return self::FAILURE;
        }

        $targetMeta = WiboostCatalog::targetMetaForTopCategory('kuota-murah') ?? [
            'label' => null,
            'placeholder' => null,
            'hint' => null,
        ];

        $products = Product::query()
            ->with('category.parent.parent.parent.parent')
            ->where(function ($query) {
                $query->where('provider_source', 'digiflazz')
                    ->orWhere('provider_id', 'digiflazz');
            })
            ->orderBy('id')
            ->get()
            ->filter(fn (Product $product) => $this->shouldMove($product, $target))
            ->values();

        if ($products->isEmpty()) {
            $this->info('Tidak ada produk Smartfren Unlimited di sub kategori Umum yang perlu dipindahkan.');
            $this->line('Target: ' . $target->breadcrumb_name);

            return self::SUCCESS;
        }

        $examples = $products
            ->take(40)
            ->map(fn (Product $product) => [
                $product->id,
                $product->provider_product_id ?: '-',
                Str::limit($product->name, 48),
                $product->category?->breadcrumb_name ?: '-',
                $target->breadcrumb_name,
            ])
            ->all();

        if (! $dryRun) {
            DB::transaction(function () use ($products, $target, $targetMeta) {
                foreach ($products as $product) {
                    $product->forceFill([
                        'category_id' => $target->id,
                        'target_label' => $targetMeta['label'],
                        'target_placeholder' => $targetMeta['placeholder'],
                        'target_hint' => $targetMeta['hint'],
                        'updated_at' => now(),
                    ])->saveQuietly();
                }
            });
        }

        $this->info($dryRun ? 'Mode preview: belum ada data diubah.' : 'Produk Smartfren Unlimited sudah dipindahkan.');
        $this->line('Target   : ' . $target->breadcrumb_name);
        $this->line('Dipindah : ' . $products->count());
        $this->newLine();
        $this->table(['ID', 'SKU', 'Produk', 'Dari', 'Ke'], $examples);

        return self::SUCCESS;
    }

    protected function shouldMove(Product $product, Category $target): bool
    {
        if ((int) $product->category_id === (int) $target->id) {
            return false;
        }

        $productText = $this->normalizeText(implode(' ', array_filter([
            $product->name,
            $product->provider_product_id,
            $product->description,
        ])));
        $categoryText = $this->normalizeText(implode(' ', array_filter([
            $product->category?->breadcrumb_name,
            $product->category?->name,
            $product->category?->slug,
        ])));
        $categoryOwnText = $this->normalizeText(implode(' ', array_filter([
            $product->category?->name,
            $product->category?->slug,
        ])));

        return $this->containsAnyKeyword($productText, $this->unlimitedKeywords())
            && ($this->containsAnyKeyword($categoryText, $this->smartfrenKeywords()) || $this->containsAnyKeyword($productText, $this->smartfrenKeywords()))
            && $this->containsAnyKeyword($categoryOwnText, $this->generalKeywords());
    }

    protected function smartfrenUnlimitedCategory(): ?Category
    {
        return $this->categories
            ->values()
            ->filter(function (Category $category) {
                $breadcrumb = $this->normalizeText($category->breadcrumb_name . ' ' . $category->slug);
                $ownText = $this->normalizeText($category->name . ' ' . $category->slug);

                return $this->containsAnyKeyword($breadcrumb, $this->smartfrenKeywords())
                    && $this->containsAnyKeyword($ownText, $this->unlimitedKeywords());
            })
            ->sortByDesc(function (Category $category) {
                $score = (int) $category->depth * 10;

                if (($category->children_count ?? 0) === 0) {
                    $score += 100;
                }

                if (filled($category->image) || filled($category->emote)) {
                    $score += 60;
                }

                if (! str_starts_with((string) $category->image, 'categories/generated/')) {
                    $score += 40;
                }

                return $score;
            })
            ->first();
    }

    protected function smartfrenKeywords(): array
    {
        return [' smartfren ', ' smart ', ' sf '];
    }

    protected function unlimitedKeywords(): array
    {
        return [' unlimited ', ' unlimited nonstop ', ' nonstop ', ' unlimited max ', ' unlimited harian ', ' unlimited booster '];
    }

    protected function generalKeywords(): array
    {
        return [' umum ', ' general ', ' reguler ', ' regular ', ' normal '];
    }

    protected function containsAnyKeyword(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeText(?string $text): string
    {
        $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = Str::lower($text);
        $text = preg_replace('/[^\pL\pN.]+/u', ' ', $text) ?? $text;

        return ' ' . trim(preg_replace('/\s+/u', ' ', $text) ?? $text) . ' ';
    }
}
