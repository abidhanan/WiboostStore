<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Support\WiboostCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MapDigiflazzQuotaLocalCategories extends Command
{
    protected $signature = 'wiboost:map-digiflazz-quota-local-categories
        {--execute : Simpan pemindahan produk kuota Digiflazz ke sub kategori manual}
        {--cleanup-auto : Hapus kategori kuota otomatis setelah produk dipindahkan}';

    protected $description = 'Pindahkan produk Kuota Murah Digiflazz ke sub kategori manual, tanpa membuat kategori kuota-* otomatis.';

    protected Collection $categories;

    protected Collection $roots;

    protected Collection $candidates;

    public function handle(): int
    {
        $execute = (bool) $this->option('execute');
        $cleanupAuto = (bool) $this->option('cleanup-auto');
        $this->categories = Category::query()
            ->with('parent.parent.parent.parent')
            ->withCount('children')
            ->get()
            ->keyBy('id');
        $this->roots = $this->categories
            ->values()
            ->filter(fn (Category $category) => in_array((string) $category->slug, ['kuota-murah', 'paket-data'], true))
            ->values();

        if ($this->roots->isEmpty()) {
            $this->error('Kategori utama Kuota Murah/Paket Data tidak ditemukan.');

            return self::FAILURE;
        }

        $this->candidates = $this->manualQuotaCandidates();

        if ($this->candidates->isEmpty()) {
            $this->error('Tidak ada sub kategori manual di bawah Kuota Murah/Paket Data.');
            $this->warn('Buat dulu sub kategori manual dari halaman kategori, lalu jalankan command ini lagi.');

            return self::FAILURE;
        }

        $products = Product::query()
            ->with('category.parent.parent.parent')
            ->where(function ($query) {
                $query->where('provider_source', 'digiflazz')
                    ->orWhere('provider_id', 'digiflazz');
            })
            ->orderBy('id')
            ->get()
            ->filter(fn (Product $product) => $this->isQuotaProduct($product))
            ->values();

        if ($products->isEmpty()) {
            $this->info('Tidak ada produk Kuota Murah Digiflazz yang perlu dipindahkan.');

            return self::SUCCESS;
        }

        $moved = 0;
        $unchanged = 0;
        $unmatched = 0;
        $examples = [];
        $targetMeta = WiboostCatalog::targetMetaForTopCategory('kuota-murah') ?? [
            'label' => null,
            'placeholder' => null,
            'hint' => null,
        ];

        $runner = function () use ($products, $execute, $cleanupAuto, $targetMeta, &$moved, &$unchanged, &$unmatched, &$examples) {
            foreach ($products as $product) {
                $target = $this->bestManualCategoryForProduct($product);

                if (! $target) {
                    $target = $this->fallbackQuotaCategory() ?: $this->roots->first();
                    $unmatched++;
                }

                $categoryChanged = (int) $product->category_id !== (int) $target->id;
                $metaChanged = $product->target_label !== $targetMeta['label']
                    || $product->target_placeholder !== $targetMeta['placeholder']
                    || $product->target_hint !== $targetMeta['hint'];

                if (! $categoryChanged && ! $metaChanged) {
                    $unchanged++;
                    continue;
                }

                $moved++;

                if (count($examples) < 30) {
                    $examples[] = [
                        $product->id,
                        $product->provider_product_id ?: '-',
                        Str::limit($product->name, 42),
                        $product->category?->breadcrumb_name ?: '-',
                        $target->breadcrumb_name,
                    ];
                }

                if ($execute) {
                    $product->forceFill([
                        'category_id' => $target->id,
                        'target_label' => $targetMeta['label'],
                        'target_placeholder' => $targetMeta['placeholder'],
                        'target_hint' => $targetMeta['hint'],
                        'updated_at' => now(),
                    ])->saveQuietly();
                }
            }

            if ($execute && $cleanupAuto) {
                $this->cleanupAutoQuotaCategories();
            }
        };

        if ($execute) {
            DB::transaction($runner);
        } else {
            $runner();
        }

        $this->info($execute ? 'Mode execute: produk Kuota Murah Digiflazz sudah dipindahkan.' : 'Mode preview: belum ada data diubah.');
        $this->line('Dicek     : ' . $products->count());
        $this->line("Dipindah  : {$moved}");
        $this->line("Tetap     : {$unchanged}");
        $this->line("Fallback  : {$unmatched}");

        if ($examples !== []) {
            $this->newLine();
            $this->table(['ID', 'SKU', 'Produk', 'Dari', 'Ke'], $examples);
        }

        return self::SUCCESS;
    }

    protected function manualQuotaCandidates(): Collection
    {
        return $this->categories
            ->values()
            ->filter(fn (Category $category) => $this->roots->doesntContain(fn (Category $root) => (int) $root->id === (int) $category->id))
            ->filter(fn (Category $category) => $this->roots->contains(fn (Category $root) => $this->isDescendantOf($category, $root)))
            ->reject(fn (Category $category) => $this->isGeneratedTextLogoCategory($category))
            ->reject(fn (Category $category) => $this->isAutoQuotaCategory($category))
            ->values();
    }

    protected function isQuotaProduct(Product $product): bool
    {
        $text = $this->normalizeText(implode(' ', array_filter([
            $product->name,
            $product->provider_product_id,
            $product->description,
        ])));
        $categoryText = $this->normalizeText((string) ($product->category?->breadcrumb_name ?? '') . ' ' . (string) ($product->category?->slug ?? ''));

        if ($this->isSmartfrenUnlimitedInGeneral($text, $categoryText)) {
            return true;
        }

        $resolvedTop = WiboostCatalog::resolveDigiflazzTopCategorySlug(
            (string) $product->name,
            (string) $product->provider_product_id,
            '',
            trim((string) $product->provider_product_id . ' ' . (string) $product->description)
        );

        if ($resolvedTop === 'kuota-murah') {
            return true;
        }

        if ($resolvedTop === 'top-up-game') {
            return false;
        }

        $currentRoot = $this->rootCategory($product->category);

        return $currentRoot
            && $this->roots->contains(fn (Category $root) => (int) $root->id === (int) $currentRoot->id)
            && $this->looksLikeQuotaText($text);
    }

    protected function bestManualCategoryForProduct(Product $product): ?Category
    {
        $text = $this->normalizeText(implode(' ', array_filter([
            $product->name,
            $product->provider_product_id,
            $product->description,
        ])));
        $categoryText = $this->normalizeText((string) ($product->category?->breadcrumb_name ?? '') . ' ' . (string) ($product->category?->slug ?? ''));

        $operator = $this->firstMatchedGroup($text, $this->operatorAliases())
            ?: $this->firstMatchedGroup($categoryText, $this->operatorAliases());
        $variant = $this->firstMatchedGroup($text, $this->variantAliases());

        if (! $operator && ! $variant) {
            return null;
        }

        if ($operator === 'smartfren' && $variant === 'unlimited') {
            $forcedTarget = $this->smartfrenUnlimitedCategory();

            if ($forcedTarget) {
                return $forcedTarget;
            }
        }

        $best = null;
        $bestScore = 0;

        foreach ($this->candidates as $candidate) {
            $candidateText = $this->normalizeText($candidate->breadcrumb_name . ' ' . $candidate->slug);
            $candidateOwn = $this->normalizeText($candidate->name . ' ' . $candidate->slug);
            $candidateOperator = $this->firstMatchedGroup($candidateText, $this->operatorAliases());
            $candidateVariant = $this->firstMatchedGroup($candidateText, $this->variantAliases());
            $score = max(1, (int) $candidate->depth) * 4;

            if ($operator && $candidateOperator && ! $this->compatibleOperator($operator, $candidateOperator)) {
                continue;
            }

            if ($variant && $candidateVariant && $candidateVariant !== $variant && $candidateVariant !== 'umum') {
                continue;
            }

            if ($operator && $this->containsAnyKeyword($candidateText, $this->operatorMatchKeywords($operator))) {
                $score += 1400;
            }

            if ($operator && $this->containsAnyKeyword($candidateOwn, $this->operatorMatchKeywords($operator))) {
                $score += 300;
            }

            if ($variant && $this->containsAnyKeyword($candidateText, $this->variantAliases()[$variant])) {
                $score += 520;
            }

            if ($variant && $this->containsAnyKeyword($candidateOwn, $this->variantAliases()[$variant])) {
                $score += 180;
            }

            if ($operator === 'smartfren' && $variant === 'unlimited' && $candidateVariant === 'unlimited') {
                $score += 450;
            }

            if (($candidate->children_count ?? 0) > 0) {
                $score -= 90;
            }

            if ($operator && $this->containsAnyKeyword($candidateText, $this->operatorAliases()['lainnya'])) {
                $score -= 180;
            }

            if (! $operator && $this->containsAnyKeyword($candidateText, $this->operatorAliases()['lainnya'])) {
                $score += 350;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        return $bestScore >= 500 ? $best : null;
    }

    protected function fallbackQuotaCategory(): ?Category
    {
        return $this->candidates->first(fn (Category $category) => $this->containsAnyKeyword(
            $this->normalizeText($category->breadcrumb_name . ' ' . $category->slug),
            $this->operatorAliases()['lainnya']
        ));
    }

    protected function smartfrenUnlimitedCategory(): ?Category
    {
        return $this->categories
            ->values()
            ->filter(fn (Category $category) => $this->roots->doesntContain(fn (Category $root) => (int) $root->id === (int) $category->id))
            ->filter(fn (Category $category) => $this->roots->contains(fn (Category $root) => $this->isDescendantOf($category, $root)))
            ->filter(function (Category $category) {
                $text = $this->normalizeText($category->breadcrumb_name . ' ' . $category->slug);
                $ownText = $this->normalizeText($category->name . ' ' . $category->slug);

                return $this->containsAnyKeyword($text, $this->operatorAliases()['smartfren'])
                    && $this->containsAnyKeyword($ownText, $this->variantAliases()['unlimited']);
            })
            ->sortByDesc(function (Category $category) {
                $score = (int) $category->depth * 10;

                if (! $this->isGeneratedTextLogoCategory($category)) {
                    $score += 200;
                }

                if (filled($category->image) || filled($category->emote)) {
                    $score += 150;
                }

                if (($category->children_count ?? 0) === 0) {
                    $score += 80;
                }

                return $score;
            })
            ->first();
    }

    protected function isSmartfrenUnlimitedInGeneral(string $productText, string $categoryText): bool
    {
        return $this->containsAnyKeyword($productText, $this->variantAliases()['unlimited'])
            && $this->containsAnyKeyword($categoryText, $this->operatorAliases()['smartfren'])
            && $this->containsAnyKeyword($categoryText, $this->variantAliases()['umum']);
    }

    protected function cleanupAutoQuotaCategories(): void
    {
        $autoCategories = $this->categories
            ->values()
            ->filter(fn (Category $category) => $this->roots->doesntContain(fn (Category $root) => (int) $root->id === (int) $category->id))
            ->filter(fn (Category $category) => $this->roots->contains(fn (Category $root) => $this->isDescendantOf($category, $root)))
            ->filter(fn (Category $category) => $this->isAutoQuotaCategory($category))
            ->sortByDesc(fn (Category $category) => $category->depth);

        foreach ($autoCategories as $category) {
            Product::query()
                ->where('category_id', $category->id)
                ->update([
                    'category_id' => $this->fallbackQuotaCategory()?->id ?: $this->roots->first()->id,
                    'updated_at' => now(),
                ]);

            Category::query()
                ->where('parent_id', $category->id)
                ->get()
                ->reject(fn (Category $child) => $this->isAutoQuotaCategory($child))
                ->each(fn (Category $child) => $child->forceFill([
                    'parent_id' => $this->roots->first()->id,
                    'updated_at' => now(),
                ])->saveQuietly());

            if ($category->image && str_starts_with((string) $category->image, 'categories/generated/')) {
                Storage::disk('public')->delete($category->image);
            }

            Category::query()->whereKey($category->id)->delete();
        }
    }

    protected function isDescendantOf(Category $category, Category $ancestor): bool
    {
        $cursor = $category;

        while ($cursor) {
            if ((int) $cursor->id === (int) $ancestor->id) {
                return true;
            }

            $cursor = $cursor->parent_id ? $this->categories->get((int) $cursor->parent_id) : null;
        }

        return false;
    }

    protected function rootCategory(?Category $category): ?Category
    {
        $cursor = $category;

        while ($cursor?->parent_id && $this->categories->has((int) $cursor->parent_id)) {
            $cursor = $this->categories->get((int) $cursor->parent_id);
        }

        return $cursor;
    }

    protected function isAutoQuotaCategory(Category $category): bool
    {
        $slug = (string) $category->slug;

        return (in_array($slug, WiboostCatalog::digiflazzQuotaAutoCategorySlugs(), true) || str_starts_with($slug, 'kuota-'))
            && ($this->isGeneratedTextLogoCategory($category)
                || (blank($category->image) && blank($category->emote)));
    }

    protected function isGeneratedTextLogoCategory(Category $category): bool
    {
        return str_starts_with((string) $category->image, 'categories/generated/');
    }

    protected function looksLikeQuotaText(string $text): bool
    {
        if ($this->containsAnyKeyword($text, [' pulsa ', ' masa aktif ', ' sms ', ' telpon ', ' telepon ', ' voice ', ' nelpon '])) {
            return false;
        }

        return $this->containsAnyKeyword($text, [
            ' data ', ' kuota ', ' internet ', ' paket ', ' gb ', ' mb ', ' unlimited ', ' combo ',
            ' freedom ', ' flash ', ' happy ', ' hotrod ', ' xtra ', ' mini ', ' owsem ', ' yellow ',
            ' always on ', ' aon ', ' omg ', ' jajan ', ' kaget ', ' volume ',
        ]);
    }

    protected function compatibleOperator(string $operator, string $candidateOperator): bool
    {
        if ($operator === $candidateOperator || $candidateOperator === 'lainnya') {
            return true;
        }

        return false;
    }

    protected function operatorMatchKeywords(string $operator): array
    {
        return $this->operatorAliases()[$operator] ?? [];
    }

    protected function operatorAliases(): array
    {
        return [
            'axis' => [' axis ', ' aigo ', ' bronet ', ' owsem '],
            'by-u' => [' by u ', ' byu ', ' by.u ', ' by u internet ', ' by u paket ', ' byu internet ', ' byu paket '],
            'telkomsel' => [' telkomsel ', ' tsel ', ' simpati ', ' kartu as ', ' loop '],
            'tri' => [' tri ', ' three ', ' aon ', ' always on ', ' alwayson ', ' h3ro '],
            'indosat' => [' indosat ', ' im3 ', ' mentari ', ' yellow ', ' freedom '],
            'smartfren' => [' smartfren ', ' smart ', ' sf '],
            'xl' => [' xl ', ' xtra ', ' hotrod ', ' xl axiata '],
            'lainnya' => [' lainnya ', ' lain ', ' other ', ' others ', ' operator lain ', ' paket lain '],
        ];
    }

    protected function variantAliases(): array
    {
        return [
            'mini' => [' mini '],
            'owsem' => [' owsem '],
            'super-kaget' => [' super kaget '],
            'kaget' => [' kaget '],
            'jajan' => [' jajan '],
            'freedom-internet' => [' freedom internet '],
            'freedom-harian' => [' freedom harian ', ' harian ', ' daily '],
            'freedom-combo' => [' freedom combo ', ' combo '],
            'yellow' => [' yellow '],
            'unlimited' => [' unlimited ', ' unlimited nonstop ', ' nonstop ', ' unlimited max ', ' unlimited harian ', ' unlimited booster '],
            'volume' => [' volume '],
            'omg' => [' omg '],
            'flash' => [' flash '],
            'combo-sakti' => [' combo sakti ', ' sakti '],
            'happy' => [' happy '],
            'always-on' => [' always on ', ' alwayson ', ' aon '],
            'xtra-combo' => [' xtra combo ', ' extra combo ', ' xtra '],
            'umum' => [' umum ', ' regular ', ' reguler ', ' normal '],
        ];
    }

    protected function firstMatchedGroup(string $text, array $groups): ?string
    {
        foreach ($groups as $group => $keywords) {
            if ($this->containsAnyKeyword($text, $keywords)) {
                return $group;
            }
        }

        return null;
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
