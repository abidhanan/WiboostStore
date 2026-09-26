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

class MapOrderSosmedLocalCategories extends Command
{
    protected $signature = 'wiboost:map-ordersosmed-local-categories
        {--execute : Simpan pemindahan produk ke sub kategori manual}
        {--cleanup-auto : Hapus kategori otomatis sosmed-* setelah produk dipindahkan}';

    protected $description = 'Pindahkan produk OrderSosmed ke sub kategori Suntik Sosmed manual, tanpa membuat kategori sosmed-* otomatis.';

    protected Collection $categories;

    protected Collection $childrenByParent;

    protected Collection $candidates;

    public function handle(): int
    {
        $execute = (bool) $this->option('execute');
        $cleanupAuto = (bool) $this->option('cleanup-auto');
        $this->categories = Category::query()->with('parent.parent.parent.parent')->get()->keyBy('id');
        $this->childrenByParent = $this->categories->values()->groupBy('parent_id');
        $root = $this->categories->firstWhere('slug', 'suntik-sosmed');

        if (! $root) {
            $this->error('Kategori utama Suntik Sosmed tidak ditemukan.');

            return self::FAILURE;
        }

        $this->candidates = $this->manualSosmedCandidates($root);

        if ($this->candidates->isEmpty()) {
            $this->error('Tidak ada sub kategori manual di bawah Suntik Sosmed.');
            $this->warn('Buat dulu sub kategori manual dari halaman kategori, lalu jalankan command ini lagi.');

            return self::FAILURE;
        }

        $products = Product::query()
            ->with('category.parent.parent.parent')
            ->where(function ($query) {
                $query->where('provider_source', 'ordersosmed')
                    ->orWhere('provider_id', 'ordersosmed');
            })
            ->where('process_type', 'api')
            ->orderBy('id')
            ->get();

        if ($products->isEmpty()) {
            $this->info('Tidak ada produk OrderSosmed yang perlu dipindahkan.');

            return self::SUCCESS;
        }

        $moved = 0;
        $unchanged = 0;
        $unmatched = 0;
        $examples = [];

        $runner = function () use ($products, $root, $execute, $cleanupAuto, &$moved, &$unchanged, &$unmatched, &$examples) {
            foreach ($products as $product) {
                $target = $this->bestManualCategoryForProduct($product);

                if (! $target) {
                    $target = $root;
                    $unmatched++;
                }

                if ((int) $product->category_id === (int) $target->id) {
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
                        'updated_at' => now(),
                    ])->saveQuietly();
                }
            }

            if ($execute && $cleanupAuto) {
                $this->cleanupAutoSosmedCategories($root);
            }
        };

        if ($execute) {
            DB::transaction($runner);
        } else {
            $runner();
        }

        $this->info($execute ? 'Mode execute: produk OrderSosmed sudah dipindahkan.' : 'Mode preview: belum ada data diubah.');
        $this->line('Dicek     : ' . $products->count());
        $this->line("Dipindah  : {$moved}");
        $this->line("Tetap     : {$unchanged}");
        $this->line("Ke root   : {$unmatched}");

        if ($examples !== []) {
            $this->newLine();
            $this->table(['ID', 'Provider ID', 'Produk', 'Dari', 'Ke'], $examples);
        }

        return self::SUCCESS;
    }

    protected function manualSosmedCandidates(Category $root): Collection
    {
        return $this->categories
            ->values()
            ->filter(fn (Category $category) => (int) $category->id !== (int) $root->id)
            ->filter(fn (Category $category) => $this->isDescendantOf($category, $root))
            ->reject(fn (Category $category) => $this->isAutoSosmedCategory($category))
            ->values();
    }

    protected function bestManualCategoryForProduct(Product $product): ?Category
    {
        $text = $this->normalizeText(implode(' ', array_filter([
            $product->name,
            $product->provider_product_id,
            $product->description,
        ])));

        $platform = $this->firstMatchedGroup($text, $this->platformAliases());
        $metric = $this->firstMatchedGroup($text, $this->metricAliases());
        $region = $this->firstMatchedGroup($text, $this->regionAliases());

        if (! $platform && ! $metric && ! $region) {
            return null;
        }

        $best = null;
        $bestScore = 0;

        foreach ($this->candidates as $candidate) {
            $candidateText = $this->normalizeText($candidate->breadcrumb_name . ' ' . $candidate->slug);
            $candidateOwn = $this->normalizeText($candidate->name . ' ' . $candidate->slug);
            $candidatePlatform = $this->firstMatchedGroup($candidateText, $this->platformAliases());
            $candidateMetric = $this->firstMatchedGroup($candidateText, $this->metricAliases());
            $score = max(1, (int) $candidate->depth) * 4;

            if ($platform && $candidatePlatform && $candidatePlatform !== $platform) {
                continue;
            }

            if ($metric && $candidateMetric && $candidateMetric !== $metric && $candidateMetric !== 'lainnya') {
                continue;
            }

            if ($platform && $this->containsAnyKeyword($candidateText, $this->platformAliases()[$platform])) {
                $score += 1200;
            }

            if ($metric && $this->containsAnyKeyword($candidateText, $this->metricAliases()[$metric])) {
                $score += 700;
            }

            if ($metric && $this->containsAnyKeyword($candidateOwn, $this->metricAliases()[$metric])) {
                $score += 170;
            }

            if ($region && $this->containsAnyKeyword($candidateText, $this->regionAliases()[$region])) {
                $score += 360;
            }

            if ($region && $this->containsAnyKeyword($candidateOwn, $this->regionAliases()[$region])) {
                $score += 140;
            }

            if ($metric === null && $this->containsAnyKeyword($candidateText, $this->metricAliases()['lainnya'])) {
                $score += 80;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        return $bestScore >= 500 ? $best : null;
    }

    protected function cleanupAutoSosmedCategories(Category $root): void
    {
        $autoCategories = $this->categories
            ->values()
            ->filter(fn (Category $category) => (int) $category->id !== (int) $root->id)
            ->filter(fn (Category $category) => $this->isDescendantOf($category, $root))
            ->filter(fn (Category $category) => $this->isAutoSosmedCategory($category))
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
                ->whereNotIn('slug', WiboostCatalog::ordersosmedAutoCategorySlugs())
                ->update([
                    'parent_id' => $root->id,
                    'updated_at' => now(),
                ]);

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

    protected function isAutoSosmedCategory(Category $category): bool
    {
        return in_array((string) $category->slug, WiboostCatalog::ordersosmedAutoCategorySlugs(), true)
            || str_starts_with((string) $category->image, 'categories/generated/');
    }

    protected function platformAliases(): array
    {
        return [
            'instagram' => [' instagram ', ' instagram com ', ' ig '],
            'tiktok' => [' tiktok ', ' tik tok '],
            'youtube' => [' youtube ', ' youtub ', ' yt '],
            'telegram' => [' telegram ', ' tele ', ' tg '],
            'facebook' => [' facebook ', ' fb '],
            'twitter-x' => [' twitter ', ' tweet ', ' retweet ', ' x twitter ', ' x com '],
            'website-traffic' => [' website ', ' traffic ', ' visit ', ' visitor ', ' seo '],
        ];
    }

    protected function metricAliases(): array
    {
        return [
            'followers' => [' followers ', ' follower '],
            'like' => [' like ', ' likes ', ' heart ', ' hearts '],
            'views' => [' views ', ' view ', ' viewer ', ' viewers ', ' reach ', ' impression ', ' impressions ', ' play ', ' plays ', ' watchtime ', ' watch time '],
            'comments' => [' comments ', ' comment ', ' komentar ', ' komen '],
            'subscribers' => [' subscribers ', ' subscriber ', ' subs '],
            'share' => [' share ', ' shares ', ' shared '],
            'save' => [' save ', ' saves ', ' saved ', ' bookmark ', ' favorite ', ' favourite '],
            'repost' => [' repost ', ' reposts ', ' retweet ', ' retweets '],
            'members' => [' member ', ' members ', ' join ', ' joins '],
            'rating-review' => [' rating ', ' review ', ' reviews ', ' ulasan '],
            'traffic' => [' traffic ', ' visit ', ' visitor ', ' visitors ', ' website ', ' seo '],
            'live' => [' live ', ' livestream ', ' live stream ', ' siaran langsung '],
            'lainnya' => [' lainnya ', ' lain ', ' other ', ' others '],
        ];
    }

    protected function regionAliases(): array
    {
        return [
            'indonesia' => [' indonesia ', ' indonesian ', ' indo ', ' lokal ', ' local indonesia ', ' wni '],
            'luar-negeri' => [' luar negeri ', ' non indo ', ' international ', ' foreign ', ' usa ', ' turkey ', ' india ', ' brazil ', ' japan ', ' korea ', ' russia ', ' arab ', ' thailand ', ' vietnam ', ' philippines ', ' malaysia ', ' singapore ', ' europe '],
            'global' => [' global ', ' worldwide ', ' campuran ', ' mixed ', ' all country ', ' all negara '],
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
