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

class MapPremiumLocalCategories extends Command
{
    protected $signature = 'wiboost:map-premium-local-categories
        {--execute : Simpan pemindahan produk aplikasi premium ke sub kategori manual}
        {--cleanup-auto : Hapus kategori aplikasi premium otomatis setelah produk dipindahkan}';

    protected $description = 'Pindahkan produk Aplikasi Premium ke sub kategori manual, tanpa membuat kategori premium-* otomatis.';

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
        $root = $this->categories->firstWhere('slug', 'aplikasi-premium');

        if (! $root) {
            $this->error('Kategori utama Aplikasi Premium tidak ditemukan.');

            return self::FAILURE;
        }

        $this->candidates = $this->manualPremiumCandidates($root);

        if ($this->candidates->isEmpty()) {
            $this->error('Tidak ada sub kategori manual di bawah Aplikasi Premium.');
            $this->warn('Buat dulu sub kategori manual dari halaman kategori, lalu jalankan command ini lagi.');

            return self::FAILURE;
        }

        $products = Product::query()
            ->with('category.parent.parent.parent')
            ->where('process_type', 'account')
            ->orderBy('id')
            ->get()
            ->filter(fn (Product $product) => $this->isPremiumProduct($product, $root))
            ->values();

        if ($products->isEmpty()) {
            $this->info('Tidak ada produk Aplikasi Premium yang perlu dipindahkan.');

            return self::SUCCESS;
        }

        $moved = 0;
        $unchanged = 0;
        $unmatched = 0;
        $examples = [];
        $targetMeta = WiboostCatalog::targetMetaForTopCategory('aplikasi-premium') ?? [
            'label' => null,
            'placeholder' => null,
            'hint' => null,
        ];

        $runner = function () use ($products, $root, $execute, $cleanupAuto, $targetMeta, &$moved, &$unchanged, &$unmatched, &$examples) {
            foreach ($products as $product) {
                $target = $this->bestManualCategoryForProduct($product);

                if (! $target) {
                    $target = $this->fallbackPremiumCategory() ?: $root;
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
                $this->cleanupAutoPremiumCategories($root);
            }
        };

        if ($execute) {
            DB::transaction($runner);
        } else {
            $runner();
        }

        $this->info($execute ? 'Mode execute: produk Aplikasi Premium sudah dipindahkan.' : 'Mode preview: belum ada data diubah.');
        $this->line('Dicek     : ' . $products->count());
        $this->line("Dipindah  : {$moved}");
        $this->line("Tetap     : {$unchanged}");
        $this->line("Fallback  : {$unmatched}");

        if ($examples !== []) {
            $this->newLine();
            $this->table(['ID', 'Produk', 'Dari', 'Ke'], $examples);
        }

        return self::SUCCESS;
    }

    protected function manualPremiumCandidates(Category $root): Collection
    {
        return $this->categories
            ->values()
            ->filter(fn (Category $category) => (int) $category->id !== (int) $root->id)
            ->filter(fn (Category $category) => $this->isDescendantOf($category, $root))
            ->reject(fn (Category $category) => $this->isGeneratedTextLogoCategory($category))
            ->reject(fn (Category $category) => $this->isAutoPremiumCategory($category))
            ->values();
    }

    protected function isPremiumProduct(Product $product, Category $root): bool
    {
        $currentRoot = $this->rootCategory($product->category);

        return $currentRoot && (int) $currentRoot->id === (int) $root->id;
    }

    protected function bestManualCategoryForProduct(Product $product): ?Category
    {
        $text = $this->normalizeText(implode(' ', array_filter([
            $product->name,
            $product->description,
            $product->provider_product_id,
        ])));

        $app = $this->firstMatchedGroup($text, $this->appAliases());
        $variant = $this->firstMatchedGroup($text, $this->variantAliases());

        if (! $app && ! $variant) {
            return null;
        }

        $best = null;
        $bestScore = 0;

        foreach ($this->candidates as $candidate) {
            $candidateText = $this->normalizeText($candidate->breadcrumb_name . ' ' . $candidate->slug);
            $candidateOwn = $this->normalizeText($candidate->name . ' ' . $candidate->slug);
            $candidateApp = $this->firstMatchedGroup($candidateText, $this->appAliases());
            $candidateVariant = $this->firstMatchedGroup($candidateText, $this->variantAliases());
            $score = max(1, (int) $candidate->depth) * 4;

            if ($app && $candidateApp && $candidateApp !== $app && $candidateApp !== 'lainnya') {
                continue;
            }

            if ($app && ! $candidateApp && ! $this->containsAnyKeyword($candidateText, $this->appAliases()['lainnya'])) {
                continue;
            }

            if ($variant && $candidateVariant && $candidateVariant !== $variant && $candidateVariant !== 'umum') {
                continue;
            }

            if ($app && $this->containsAnyKeyword($candidateText, $this->appAliases()[$app])) {
                $score += 1400;
            }

            if ($app && $this->containsAnyKeyword($candidateOwn, $this->appAliases()[$app])) {
                $score += 300;
            }

            if ($variant && $this->containsAnyKeyword($candidateText, $this->variantAliases()[$variant])) {
                $score += 520;
            }

            if ($variant && $this->containsAnyKeyword($candidateOwn, $this->variantAliases()[$variant])) {
                $score += 180;
            }

            if (filled($candidate->image) || filled($candidate->emote)) {
                $score += 40;
            }

            if ($candidate->children_count ?? 0) {
                $score -= 90;
            }

            if ($app && $this->containsAnyKeyword($candidateText, $this->appAliases()['lainnya'])) {
                $score -= 180;
            }

            if (! $app && $this->containsAnyKeyword($candidateText, $this->appAliases()['lainnya'])) {
                $score += 350;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        return $bestScore >= 500 ? $best : null;
    }

    protected function fallbackPremiumCategory(): ?Category
    {
        return $this->candidates->first(fn (Category $category) => $this->containsAnyKeyword(
            $this->normalizeText($category->breadcrumb_name . ' ' . $category->slug),
            $this->appAliases()['lainnya']
        ));
    }

    protected function cleanupAutoPremiumCategories(Category $root): void
    {
        $autoCategories = $this->categories
            ->values()
            ->filter(fn (Category $category) => (int) $category->id !== (int) $root->id)
            ->filter(fn (Category $category) => $this->isDescendantOf($category, $root))
            ->filter(fn (Category $category) => $this->isAutoPremiumCategory($category))
            ->sortByDesc(fn (Category $category) => $category->depth);

        foreach ($autoCategories as $category) {
            Product::query()
                ->where('category_id', $category->id)
                ->update([
                    'category_id' => $this->fallbackPremiumCategory()?->id ?: $root->id,
                    'updated_at' => now(),
                ]);

            Category::query()
                ->where('parent_id', $category->id)
                ->get()
                ->reject(fn (Category $child) => $this->isAutoPremiumCategory($child))
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

    protected function isAutoPremiumCategory(Category $category): bool
    {
        $slug = (string) $category->slug;

        return in_array($slug, WiboostCatalog::premiumAutoCategorySlugs(), true)
            || str_starts_with($slug, 'premium-')
            || $this->isGeneratedTextLogoCategory($category);
    }

    protected function isGeneratedTextLogoCategory(Category $category): bool
    {
        return str_starts_with((string) $category->image, 'categories/generated/');
    }

    protected function appAliases(): array
    {
        return [
            'netflix' => [' netflix '],
            'spotify' => [' spotify '],
            'canva' => [' canva '],
            'youtube' => [' youtube ', ' yt premium ', ' youtube premium ', ' youtube music '],
            'capcut' => [' capcut '],
            'vidio' => [' vidio '],
            'viu' => [' viu '],
            'disney' => [' disney ', ' disney plus ', ' disney hotstar ', ' hotstar '],
            'prime-video' => [' prime video ', ' amazon prime '],
            'hbo' => [' hbo ', ' hbo max ', ' hbo go '],
            'maxstream' => [' maxstream ', ' max stream '],
            'iqiyi' => [' iqiyi '],
            'wetv' => [' wetv ', ' we tv '],
            'loklok' => [' loklok ', ' lok lok '],
            'bstation' => [' bstation ', ' b station ', ' bilibili '],
            'crunchyroll' => [' crunchyroll ', ' crunchy roll '],
            'viki' => [' viki ', ' rakuten viki '],
            'vision' => [' vision ', ' vision plus ', ' visionplus '],
            'mola' => [' mola ', ' mola tv '],
            'catchplay' => [' catchplay ', ' catch play '],
            'iflix' => [' iflix '],
            'deezer' => [' deezer '],
            'tidal' => [' tidal '],
            'apple-music' => [' apple music '],
            'apple-tv' => [' apple tv ', ' appletv '],
            'icloud' => [' icloud ', ' i cloud '],
            'chatgpt' => [' chatgpt ', ' chat gpt ', ' openai '],
            'gemini' => [' gemini '],
            'claude' => [' claude ', ' anthropic '],
            'perplexity' => [' perplexity '],
            'copilot' => [' copilot ', ' microsoft copilot '],
            'blackbox' => [' blackbox ', ' black box '],
            'midjourney' => [' midjourney ', ' mid journey '],
            'runway' => [' runway '],
            'zoom' => [' zoom '],
            'microsoft' => [' microsoft ', ' office ', ' office 365 ', ' microsoft 365 '],
            'google' => [' google one ', ' google drive '],
            'grammarly' => [' grammarly '],
            'quillbot' => [' quillbot ', ' quill bot '],
            'turnitin' => [' turnitin '],
            'scribd' => [' scribd '],
            'coursehero' => [' course hero ', ' coursehero '],
            'chegg' => [' chegg '],
            'studocu' => [' studocu ', ' stu docu '],
            'duolingo' => [' duolingo '],
            'adobe' => [' adobe ', ' photoshop ', ' lightroom ', ' illustrator ', ' premiere pro '],
            'picsart' => [' picsart ', ' pics art '],
            'wink' => [' wink '],
            'alight-motion' => [' alight motion ', ' alightmotion '],
            'remini' => [' remini '],
            'vsco' => [' vsco '],
            'inshot' => [' inshot ', ' in shot '],
            'kinemaster' => [' kinemaster ', ' kine master '],
            'filmora' => [' filmora '],
            'meitu' => [' meitu '],
            'prequel' => [' prequel '],
            'vpn' => [' vpn ', ' surfshark ', ' nordvpn ', ' expressvpn '],
            'lainnya' => [' lainnya ', ' lain ', ' other ', ' others ', ' aplikasi lain ', ' app lain ', ' premium lain '],
        ];
    }

    protected function variantAliases(): array
    {
        return [
            'private' => [' private ', ' privat ', ' personal ', ' individu ', ' individual '],
            'sharing' => [' sharing ', ' share ', ' shared ', ' semi private ', ' semi-private '],
            'family' => [' family ', ' keluarga '],
            'team' => [' team ', ' grup ', ' group '],
            'trial' => [' trial ', ' uji coba '],
            'membership' => [' membership ', ' member ', ' langganan ', ' subscription '],
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
