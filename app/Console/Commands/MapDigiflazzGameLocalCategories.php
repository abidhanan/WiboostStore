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

class MapDigiflazzGameLocalCategories extends Command
{
    protected $signature = 'wiboost:map-digiflazz-game-local-categories
        {--execute : Simpan pemindahan produk game Digiflazz ke sub kategori manual}
        {--cleanup-auto : Hapus kategori game otomatis setelah produk dipindahkan}';

    protected $description = 'Pindahkan produk Top Up Game Digiflazz ke sub kategori manual, tanpa membuat kategori game-* otomatis.';

    protected Collection $categories;

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
        $root = $this->categories->firstWhere('slug', 'top-up-game');

        if (! $root) {
            $this->error('Kategori utama Top Up Game tidak ditemukan.');

            return self::FAILURE;
        }

        $this->candidates = $this->manualGameCandidates($root);

        if ($this->candidates->isEmpty()) {
            $this->error('Tidak ada sub kategori manual di bawah Top Up Game.');
            $this->warn('Buat dulu sub kategori manual dari halaman kategori, lalu jalankan command ini lagi.');

            return self::FAILURE;
        }

        $products = Product::query()
            ->with('category.parent.parent.parent')
            ->where(function ($query) {
                $query->where('provider_source', 'digiflazz')
                    ->orWhere('provider_id', 'digiflazz');
            })
            ->where('process_type', 'api')
            ->orderBy('id')
            ->get()
            ->filter(fn (Product $product) => $this->isGameProduct($product, $root))
            ->values();

        if ($products->isEmpty()) {
            $this->info('Tidak ada produk Top Up Game Digiflazz yang perlu dipindahkan.');

            return self::SUCCESS;
        }

        $moved = 0;
        $unchanged = 0;
        $unmatched = 0;
        $examples = [];
        $targetMeta = WiboostCatalog::targetMetaForTopCategory('top-up-game') ?? [
            'label' => null,
            'placeholder' => null,
            'hint' => null,
        ];

        $runner = function () use ($products, $root, $execute, $cleanupAuto, $targetMeta, &$moved, &$unchanged, &$unmatched, &$examples) {
            foreach ($products as $product) {
                $target = $this->bestManualCategoryForProduct($product);

                if (! $target) {
                    $target = $this->fallbackGameCategory() ?: $root;
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
                $this->cleanupAutoGameCategories($root);
            }
        };

        if ($execute) {
            DB::transaction($runner);
        } else {
            $runner();
        }

        $this->info($execute ? 'Mode execute: produk Top Up Game Digiflazz sudah dipindahkan.' : 'Mode preview: belum ada data diubah.');
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

    protected function manualGameCandidates(Category $root): Collection
    {
        return $this->categories
            ->values()
            ->filter(fn (Category $category) => (int) $category->id !== (int) $root->id)
            ->filter(fn (Category $category) => $this->isDescendantOf($category, $root))
            ->reject(fn (Category $category) => $this->isGeneratedTextLogoCategory($category))
            ->reject(fn (Category $category) => $this->isAutoGameCategory($category))
            ->values();
    }

    protected function isGameProduct(Product $product, Category $root): bool
    {
        $currentRoot = $this->rootCategory($product->category);

        if ($currentRoot && (int) $currentRoot->id === (int) $root->id) {
            return true;
        }

        return WiboostCatalog::resolveDigiflazzTopCategorySlug(
            (string) $product->name,
            (string) $product->provider_product_id,
            '',
            trim((string) $product->provider_product_id . ' ' . (string) $product->description)
        ) === 'top-up-game';
    }

    protected function bestManualCategoryForProduct(Product $product): ?Category
    {
        $text = $this->normalizeText(implode(' ', array_filter([
            $product->name,
            $product->provider_product_id,
            $product->description,
        ])));

        $game = $this->firstMatchedGroup($text, $this->gameAliases());
        $variant = $this->detectProductVariant($game, $text);

        if (! $game && ! $variant) {
            return null;
        }

        $best = null;
        $bestScore = 0;

        foreach ($this->candidates as $candidate) {
            $candidateText = $this->normalizeText($candidate->breadcrumb_name . ' ' . $candidate->slug);
            $candidateOwn = $this->normalizeText($candidate->name . ' ' . $candidate->slug);
            $candidateGame = $this->firstMatchedGroup($candidateText, $this->gameAliases());
            $candidateVariant = $this->firstMatchedGroup($candidateText, $this->variantAliases());
            $score = max(1, (int) $candidate->depth) * 4;

            if ($game && $candidateGame && $candidateGame !== $game && $candidateGame !== 'lainnya') {
                continue;
            }

            if ($game && ! $candidateGame && ! $this->containsAnyKeyword($candidateText, $this->gameAliases()['lainnya'])) {
                continue;
            }

            if ($variant && $candidateVariant && $candidateVariant !== $variant && $candidateVariant !== 'umum') {
                continue;
            }

            if ($game && $this->containsAnyKeyword($candidateText, $this->gameAliases()[$game])) {
                $score += 1400;
            }

            if ($game && $this->containsAnyKeyword($candidateOwn, $this->gameAliases()[$game])) {
                $score += 280;
            }

            if ($variant && $this->containsAnyKeyword($candidateText, $this->variantAliases()[$variant])) {
                $score += 520;
            }

            if ($variant && $this->containsAnyKeyword($candidateOwn, $this->variantAliases()[$variant])) {
                $score += 180;
            }

            if ($game === 'lol-wild-rift' && $this->containsAnyKeyword($candidateText, $this->gameAliases()['lol-pc'])) {
                $score -= 1200;
            }

            if (($candidate->children_count ?? 0) > 0) {
                $score -= 90;
            }

            if ($game && $this->containsAnyKeyword($candidateText, $this->gameAliases()['lainnya'])) {
                $score -= 180;
            }

            if (! $game && $this->containsAnyKeyword($candidateText, $this->gameAliases()['lainnya'])) {
                $score += 350;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        return $bestScore >= 500 ? $best : null;
    }

    protected function fallbackGameCategory(): ?Category
    {
        return $this->candidates->first(fn (Category $category) => $this->containsAnyKeyword(
            $this->normalizeText($category->breadcrumb_name . ' ' . $category->slug),
            $this->gameAliases()['lainnya']
        ));
    }

    protected function cleanupAutoGameCategories(Category $root): void
    {
        $autoCategories = $this->categories
            ->values()
            ->filter(fn (Category $category) => (int) $category->id !== (int) $root->id)
            ->filter(fn (Category $category) => $this->isDescendantOf($category, $root))
            ->filter(fn (Category $category) => $this->isAutoGameCategory($category))
            ->sortByDesc(fn (Category $category) => $category->depth);

        foreach ($autoCategories as $category) {
            Product::query()
                ->where('category_id', $category->id)
                ->update([
                    'category_id' => $root->id,
                    'updated_at' => now(),
                ]);

            Category::query()
                ->where('parent_id', $category->id)
                ->get()
                ->reject(fn (Category $child) => $this->isAutoGameCategory($child))
                ->each(fn (Category $child) => $child->forceFill([
                    'parent_id' => $root->id,
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

    protected function isAutoGameCategory(Category $category): bool
    {
        $slug = (string) $category->slug;

        return (in_array($slug, WiboostCatalog::digiflazzGameAutoCategorySlugs(), true) || str_starts_with($slug, 'game-'))
            && ($this->isGeneratedTextLogoCategory($category)
                || (blank($category->image) && blank($category->emote)));
    }

    protected function isGeneratedTextLogoCategory(Category $category): bool
    {
        return str_starts_with((string) $category->image, 'categories/generated/');
    }

    protected function gameAliases(): array
    {
        return [
            'mobile-legends' => [' mobile legends bang bang ', ' mobile legends bangbang ', ' mobile legends ', ' mobile legend ', ' mobilelegend ', ' mlbb ', ' ml '],
            'free-fire-max' => [' free fire max ', ' freefire max ', ' ff max '],
            'free-fire' => [' free fire ', ' freefire ', ' ff '],
            'pubg-mobile' => [' pubg ', ' pubg mobile ', ' uc pubg ', ' uc mobile '],
            'valorant' => [' valorant ', ' valorant points ', ' valorant point ', ' vp valorant ', ' valorant vp ', ' valo ', ' vp '],
            'arena-of-valor' => [' arena of valor ', ' aov '],
            'honor-of-kings' => [' honor of kings ', ' hok '],
            'genshin-impact' => [' genshin ', ' genshin impact ', ' genesis crystal ', ' welkin '],
            'honkai-star-rail' => [' honkai ', ' star rail ', ' oneiric shard '],
            'call-of-duty-mobile' => [' call of duty ', ' call of duty mobile ', ' codm ', ' cod mobile ', ' cp cod '],
            'fc-mobile' => [' fc mobile ', ' fifa mobile ', ' fifa points ', ' eafc mobile ', ' ea fc mobile '],
            'lol-wild-rift' => [
                ' league of legends wild rift ',
                ' league of legend wild rift ',
                ' lol wild rift ',
                ' lol wr ',
                ' lolwr ',
                ' wild rift ',
                ' wildrift ',
                ' wild core ',
                ' wild cores ',
                ' wild cores wild rift ',
                ' wild rift wild cores ',
                ' wr wild core ',
                ' wr wild cores ',
                ' wild pass ',
                ' wildrift pass ',
            ],
            'lol-pc' => [' lol pc ', ' lolpc ', ' league of legends pc ', ' league of legend pc ', ' league pc ', ' riot points ', ' riot point ', ' riot cash '],
            'lainnya' => [' lainnya ', ' lain ', ' other ', ' others ', ' game lain ', ' games lain '],
        ];
    }

    protected function variantAliases(): array
    {
        return [
            'kombinasi' => [' kombinasi ', ' combo ', ' combination '],
            'membership' => [' membership ', ' member ', ' mingguan ', ' bulanan ', ' weekly ', ' monthly ', ' pass ', ' diamond pass ', ' weekly diamond pass ', ' starlight ', ' twilight ', ' booyah pass ', ' blessing ', ' welkin '],
            'indonesia' => [' indonesia ', ' indonesian ', ' indo ', ' id '],
            'malaysia' => [' malaysia ', ' malay ', ' my '],
            'global' => [' global ', ' worldwide ', ' international '],
            'umum' => [' umum ', ' regular ', ' reguler ', ' normal '],
        ];
    }

    protected function detectProductVariant(?string $game, string $text): ?string
    {
        if ($game === 'mobile-legends') {
            if ($this->isMobileLegendsMalaysiaDiamond($text)) {
                return 'malaysia';
            }

            if ($this->isMobileLegendsIndonesiaDiamond($text)) {
                return 'indonesia';
            }

            if ($this->isMobileLegendsMembership($text) || ! $this->isMobileLegendsPlainDiamond($text)) {
                return 'membership';
            }

            return 'umum';
        }

        if (in_array($game, ['lol-wild-rift', 'valorant'], true)
            && $this->containsAnyKeyword($text, $this->variantAliases()['malaysia'])) {
            return 'malaysia';
        }

        return $this->firstMatchedGroup($text, $this->variantAliases());
    }

    protected function isMobileLegendsIndonesiaDiamond(string $text): bool
    {
        if ($this->containsAnyKeyword($text, $this->variantAliases()['malaysia'])) {
            return false;
        }

        if (! $this->containsAnyKeyword($text, [' indonesia ', ' indonesian ', ' indo '])) {
            return false;
        }

        return $this->isMobileLegendsPlainDiamond($text);
    }

    protected function isMobileLegendsMalaysiaDiamond(string $text): bool
    {
        if (! $this->containsAnyKeyword($text, $this->variantAliases()['malaysia'])) {
            return false;
        }

        return $this->isMobileLegendsPlainDiamond($text);
    }

    protected function isMobileLegendsPlainDiamond(string $text): bool
    {
        if (! $this->containsAnyKeyword($text, [' diamond ', ' diamonds ', ' dm '])) {
            return false;
        }

        return ! $this->isMobileLegendsMembership($text);
    }

    protected function isMobileLegendsMembership(string $text): bool
    {
        return $this->containsAnyKeyword($text, [
            ' weekly diamond pass ',
            ' diamond pass ',
            ' pass ',
            ' membership ',
            ' member ',
            ' starlight ',
            ' twilight ',
            ' twilight pass ',
            ' weekly ',
            ' monthly ',
            ' mingguan ',
            ' bulanan ',
            ' coupon pass ',
            ' magic wheel ',
        ]);
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
