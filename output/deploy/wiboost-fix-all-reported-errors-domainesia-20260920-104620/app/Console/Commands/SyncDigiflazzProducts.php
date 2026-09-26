<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Services\DigiflazzService;
use App\Support\DigiflazzProductLogo;
use App\Support\ProductCategoryMapping;
use App\Support\WiboostCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncDigiflazzProducts extends Command
{
    protected $signature = 'sync:digiflazz
        {--match-local-categories : Import hanya produk Digiflazz yang cocok dengan kategori lokal Top Up Game dan Kuota Murah}
        {--update-existing-only : Perbarui hanya SKU yang sudah ada tanpa mengubah mapping kategori}
        {--execute : Simpan perubahan pada mode update-existing-only (tanpa opsi ini hanya pratinjau)}';

    protected $description = 'Sync produk dari Digiflazz ke database Wiboost dengan mapping kategori otomatis';

    protected ?array $strictDigiflazzCategoryCandidates = null;

    public function handle(DigiflazzService $digiService): int
    {
        if ($this->option('execute') && ! $this->option('update-existing-only')) {
            $this->error('--execute hanya dapat digunakan bersama --update-existing-only.');

            return self::FAILURE;
        }

        if ($this->option('update-existing-only') && $this->option('match-local-categories')) {
            $this->error('Gunakan --update-existing-only tanpa --match-local-categories.');

            return self::FAILURE;
        }

        $this->info('--- MEMULAI PROSES SINKRONISASI ---');
        $this->info('Mengambil data dari Digiflazz...');
        $matchLocalCategories = (bool) $this->option('match-local-categories');

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

        if ($this->option('update-existing-only')) {
            return $this->updateExistingProducts($products->all(), (bool) $this->option('execute'));
        }

        if ($matchLocalCategories) {
            $this->info('Mode cocok kategori lokal aktif: hanya produk Top Up Game dan Kuota Murah yang sesuai kategori lokal akan disimpan.');
            Product::query()
                ->where('provider_source', 'digiflazz')
                ->update([
                    'is_active' => false,
                    'status' => 'inactive',
                    'updated_at' => now(),
                ]);
            $this->info('Produk Digiflazz lama dinonaktifkan sementara; produk yang cocok akan diaktifkan ulang.');
        }

        $this->info("Ditemukan {$total} produk. Memulai mapping...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($products as $item) {
            try {
                $mappedCategory = ProductCategoryMapping::categoryFor(
                    'digiflazz',
                    (string) ($item['buyer_sku_code'] ?? ''),
                    (string) ($item['product_name'] ?? '')
                );
                if ($mappedCategory && ! $this->storedMappingMatchesDigiflazzItem($item, $mappedCategory)) {
                    $mappedCategory = null;
                }

                $mappedTopCategorySlug = $mappedCategory ? ProductCategoryMapping::rootSlug($mappedCategory) : null;
                $topCategorySlug = $mappedTopCategorySlug ?: ($matchLocalCategories
                    ? $this->resolveStrictTopCategorySlug($item)
                    : WiboostCatalog::resolveDigiflazzTopCategorySlug(
                        (string) ($item['product_name'] ?? ''),
                        (string) ($item['brand'] ?? ''),
                        (string) ($item['category'] ?? ''),
                        (string) ($item['desc'] ?? ''),
                    ));

                if ($topCategorySlug === null) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $hargaModal = $this->parseProviderNumber($item['price'] ?? 0);

                $categoryId = $mappedCategory?->id ?: ($matchLocalCategories
                    ? $this->resolveStrictLocalCategoryId($item, $topCategorySlug)
                    : $this->resolveDefaultCategoryId($item, $topCategorySlug, $defaultCategoryId));

                if (! $categoryId) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $targetMeta = WiboostCatalog::targetMetaForTopCategory($topCategorySlug) ?? [
                    'label' => null,
                    'placeholder' => null,
                    'hint' => null,
                ];

                $product = Product::firstOrNew([
                    'provider_source' => 'digiflazz',
                    'provider_product_id' => (string) $item['buyer_sku_code'],
                ]);

                if (! $product->exists && blank($product->slug)) {
                    $product->slug = Str::slug((string) ($item['product_name'] ?? 'produk-digiflazz')) . '-' . Str::random(5);
                }

                $isActive = (bool) ($item['buyer_product_status'] ?? false) && (bool) ($item['seller_product_status'] ?? false);
                $productEmote = DigiflazzProductLogo::emoteForDigiflazzItem($item, $topCategorySlug);

                $product->fill([
                    'category_id' => $categoryId,
                    'provider_id' => 'digiflazz',
                    'provider_source' => 'digiflazz',
                    'process_type' => 'api',
                    'name' => (string) ($item['product_name'] ?? 'Produk Digiflazz'),
                    'description' => (string) ($item['desc'] ?? '-'),
                    'price' => $hargaModal,
                    'provider_product_id' => (string) $item['buyer_sku_code'],
                    'provider_quantity' => 1,
                    'target_label' => $targetMeta['label'],
                    'target_placeholder' => $targetMeta['placeholder'],
                    'target_hint' => $targetMeta['hint'],
                    'is_active' => $isActive,
                    'status' => $isActive ? 'active' : 'inactive',
                    'stock_reminder' => $product->stock_reminder ?? 0,
                    'image' => $productEmote !== null ? null : $product->image,
                    'emote' => $productEmote ?? $product->emote,
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
        $this->info("Dilewati   : {$skipped}");

        if ($failed > 0) {
            $this->warn("Gagal      : {$failed} (Cek storage/logs/laravel.log untuk detail)");
        }

        if ($matchLocalCategories && $success === 0) {
            $this->error('Tidak ada produk Digiflazz yang cocok dengan kategori lokal Top Up Game / Kuota Murah.');
            $this->warn('Pastikan kategori lokal berada di bawah Top Up Game atau Kuota Murah, misalnya Mobile Legends, Free Fire, Axis, Telkomsel, atau Lainnya.');

            return self::FAILURE;
        }

        $this->info('--- SELESAI ---');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function updateExistingProducts(array $providerItems, bool $execute): int
    {
        $bySku = [];

        foreach ($providerItems as $item) {
            $bySku[trim((string) $item['buyer_sku_code'])] = $item;
        }

        $checked = 0;
        $changed = 0;
        $unchanged = 0;
        $missing = 0;
        $invalidPrice = 0;

        Product::query()
            ->where('provider_source', 'digiflazz')
            ->orderBy('id')
            ->chunkById(500, function ($products) use ($bySku, $execute, &$checked, &$changed, &$unchanged, &$missing, &$invalidPrice) {
                foreach ($products as $product) {
                    $checked++;
                    $item = $bySku[trim((string) $product->provider_product_id)] ?? null;

                    if ($item === null) {
                        $missing++;
                        continue;
                    }

                    $price = $this->parseProviderNumber($item['price'] ?? 0);

                    if ($price <= 0) {
                        $invalidPrice++;
                        continue;
                    }

                    $name = trim((string) ($item['product_name'] ?? ''));
                    $description = trim((string) ($item['desc'] ?? ''));
                    $isActive = array_key_exists('buyer_product_status', $item)
                        && array_key_exists('seller_product_status', $item)
                        ? (bool) $item['buyer_product_status'] && (bool) $item['seller_product_status']
                        : (bool) $product->is_active;

                    $product->fill([
                        'name' => $name !== '' ? $name : $product->name,
                        'description' => $description !== '' ? $description : $product->description,
                        'price' => $price,
                        'is_active' => $isActive,
                        'status' => $isActive ? 'active' : 'inactive',
                    ]);

                    if (! $product->isDirty()) {
                        $unchanged++;
                        continue;
                    }

                    $changed++;

                    if ($execute) {
                        $product->saveQuietly();
                    }
                }
            });

        $this->newLine();
        $this->info($execute ? 'Pembaruan produk Digiflazz selesai.' : 'Pratinjau saja; database belum diubah.');
        $this->line("SKU lokal dicek: {$checked}; berubah: {$changed}; tetap: {$unchanged}; tidak ada di provider: {$missing}; harga tidak valid: {$invalidPrice}.");
        $this->line('Kategori, subkategori, logo, slug, dan SKU lokal tidak diubah. Produk baru dari provider tidak diimpor.');

        if (! $execute) {
            $this->line('Jalankan lagi dengan --execute untuk menyimpan perubahan.');
        }

        return self::SUCCESS;
    }

    protected function parseProviderNumber(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return 0.0;
        }

        $text = preg_replace('/[^\d,.\-]+/', '', str_replace(["\xc2\xa0", ' '], '', $text)) ?: '';

        if ($text === '' || $text === '-') {
            return 0.0;
        }

        $negative = str_starts_with($text, '-');
        $text = ltrim($text, '-');

        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $text) === 1) {
            $text = str_replace('.', '', $text);
            $text = str_replace(',', '.', $text);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $text) === 1) {
            $text = str_replace(',', '', $text);
        } elseif (str_contains($text, ',') && ! str_contains($text, '.')) {
            $text = str_replace(',', '.', $text);
        } elseif (substr_count($text, '.') > 1) {
            $text = str_replace('.', '', $text);
        } elseif (substr_count($text, ',') > 1) {
            $text = str_replace(',', '', $text);
        }

        return (float) (($negative ? '-' : '') . $text);
    }

    protected function resolveDefaultCategoryId(array $item, string $topCategorySlug, int $defaultCategoryId): int
    {
        if (in_array($topCategorySlug, ['top-up-game', 'kuota-murah', 'paket-data'], true)) {
            $strictCategoryId = $this->resolveStrictLocalCategoryId($item, $topCategorySlug === 'paket-data' ? 'kuota-murah' : $topCategorySlug);

            if ($strictCategoryId) {
                return $strictCategoryId;
            }
        }

        $subcategorySlug = WiboostCatalog::resolveDigiflazzSubcategorySlug(
            $topCategorySlug,
            (string) ($item['product_name'] ?? ''),
            (string) ($item['brand'] ?? ''),
            (string) ($item['desc'] ?? ''),
        );
        $subcategoryId = $subcategorySlug
            ? $this->manualDigiflazzCategoryIdBySlug($subcategorySlug, $topCategorySlug)
            : null;

        return $subcategoryId
            ? $subcategoryId
            : $this->categoryIdBySlug($topCategorySlug, $defaultCategoryId);
    }

    protected function resolveStrictLocalCategoryId(array $item, string $topCategorySlug): ?int
    {
        $route = $this->classifyStrictDigiflazzRoute($item, $topCategorySlug);

        if ($route === null) {
            return null;
        }

        return $this->findStrictDigiflazzCategoryId(
            $route['top'],
            $route['group'],
            $route['variant'] ?? null,
        );
    }

    protected function resolveStrictTopCategorySlug(array $item): ?string
    {
        $text = $this->normalizeDigiflazzText($this->itemSearchText($item));
        $primaryText = $this->normalizeDigiflazzText($this->itemPrimarySearchText($item));

        if ($this->isStrictQuotaProductText($text) && $this->detectStrictQuotaOperator($text) !== null) {
            return 'kuota-murah';
        }

        if (($this->detectStrictGameKey($primaryText) ?? $this->detectStrictGameKey($text)) !== null) {
            return 'top-up-game';
        }

        return null;
    }

    protected function classifyStrictDigiflazzRoute(array $item, string $topCategorySlug): ?array
    {
        $text = $this->normalizeDigiflazzText($this->itemSearchText($item));
        $primaryText = $this->normalizeDigiflazzText($this->itemPrimarySearchText($item));

        if ($topCategorySlug === 'kuota-murah') {
            if (! $this->isStrictQuotaProductText($text)) {
                return null;
            }

            $operator = $this->detectStrictQuotaOperator($text);

            if ($operator === null) {
                return null;
            }

            return [
                'top' => 'kuota-murah',
                'group' => $operator,
                'variant' => $this->detectStrictQuotaVariant($operator, $text),
            ];
        }

        if ($topCategorySlug === 'top-up-game') {
            $game = $this->detectStrictGameKey($primaryText) ?? $this->detectStrictGameKey($text);

            if ($game === null) {
                return null;
            }

            return [
                'top' => 'top-up-game',
                'group' => $game,
                'variant' => $this->detectStrictGameVariant($game, $primaryText, $text),
            ];
        }

        return null;
    }

    protected function findStrictDigiflazzCategoryId(string $topCategorySlug, string $groupKey, ?string $variantKey = null): ?int
    {
        $groupAliases = $this->strictDigiflazzGroupAliases($topCategorySlug)[$groupKey] ?? [];
        $variantAliases = $variantKey !== null
            ? ($this->strictDigiflazzVariantAliases($topCategorySlug)[$variantKey] ?? [])
            : [];
        $bestCandidate = null;
        $bestScore = 0;
        $fallbackCandidate = null;
        $fallbackScore = 0;

        foreach ($this->strictDigiflazzCategoryCandidates($topCategorySlug) as $candidate) {
            if (! $this->containsAnyKeyword($candidate['breadcrumb'], $groupAliases)) {
                continue;
            }

            if ($variantKey !== null && ! $this->containsAnyKeyword($candidate['breadcrumb'], $variantAliases)) {
                $score = (int) $candidate['depth'];

                if ($this->containsAnyKeyword($candidate['own'], $groupAliases)) {
                    $score += 160;
                }

                if ($this->containsAnyKeyword($candidate['parent'], $groupAliases)) {
                    $score += 80;
                }

                if ($score > $fallbackScore) {
                    $fallbackScore = $score;
                    $fallbackCandidate = $candidate;
                }

                continue;
            }

            if ($variantKey === null && ! $this->containsAnyKeyword($candidate['own'], $groupAliases)) {
                continue;
            }

            $score = (int) $candidate['depth'];

            if ($this->containsAnyKeyword($candidate['own'], $groupAliases)) {
                $score += 200;
            }

            if ($this->containsAnyKeyword($candidate['parent'], $groupAliases)) {
                $score += 120;
            }

            if ($variantKey !== null && $this->containsAnyKeyword($candidate['own'], $variantAliases)) {
                $score += 300;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestCandidate = $candidate;
            }
        }

        return $bestCandidate
            ? (int) $bestCandidate['id']
            : ($fallbackCandidate ? (int) $fallbackCandidate['id'] : null);
    }

    protected function storedMappingMatchesDigiflazzItem(array $item, Category $mappedCategory): bool
    {
        $topCategorySlug = ProductCategoryMapping::rootSlug($mappedCategory);

        if (! in_array($topCategorySlug, ['top-up-game', 'kuota-murah', 'paket-data'], true)) {
            return true;
        }

        $route = $this->classifyStrictDigiflazzRoute(
            $item,
            $topCategorySlug === 'paket-data' ? 'kuota-murah' : $topCategorySlug
        );

        if ($route === null) {
            return true;
        }

        $categoryText = $this->normalizeDigiflazzText($mappedCategory->breadcrumb_name . ' ' . $mappedCategory->slug);
        $groupAliases = $this->strictDigiflazzGroupAliases($route['top'])[$route['group']] ?? [];

        if ($groupAliases !== [] && ! $this->containsAnyKeyword($categoryText, $groupAliases)) {
            return false;
        }

        $variant = $route['variant'] ?? null;

        if ($route['top'] === 'top-up-game' && ($route['group'] ?? null) === 'mobile-legends' && $variant === 'umum') {
            foreach (['indonesia', 'malaysia', 'membership', 'global', 'kombinasi'] as $nonUmumVariant) {
                $nonUmumAliases = $this->strictDigiflazzVariantAliases('top-up-game')[$nonUmumVariant] ?? [];

                if ($nonUmumAliases !== [] && $this->containsAnyKeyword($categoryText, $nonUmumAliases)) {
                    return false;
                }
            }
        }

        if ($variant !== null && $variant !== 'umum') {
            $variantAliases = $this->strictDigiflazzVariantAliases($route['top'])[$variant] ?? [];

            if ($variantAliases !== [] && ! $this->containsAnyKeyword($categoryText, $variantAliases)) {
                return false;
            }
        }

        return true;
    }

    protected function strictDigiflazzCategoryCandidates(string $topCategorySlug): array
    {
        if ($this->strictDigiflazzCategoryCandidates === null) {
            $this->strictDigiflazzCategoryCandidates = [];
        }

        if (array_key_exists($topCategorySlug, $this->strictDigiflazzCategoryCandidates)) {
            return $this->strictDigiflazzCategoryCandidates[$topCategorySlug];
        }

        $rootAliases = $this->strictDigiflazzRootAliases($topCategorySlug);

        $this->strictDigiflazzCategoryCandidates[$topCategorySlug] = Category::query()
            ->with('parent.parent.parent.parent')
            ->get()
            ->map(function (Category $category) {
                $parent = $category->parent;
                $root = $category;

                while ($root->parent) {
                    $root = $root->parent;
                }

                return [
                    'id' => $category->id,
                    'slug' => (string) $category->slug,
                    'image' => (string) $category->image,
                    'emote' => (string) $category->emote,
                    'own' => $this->normalizeDigiflazzText($category->name . ' ' . $category->slug),
                    'parent' => $parent ? $this->normalizeDigiflazzText($parent->name . ' ' . $parent->slug) : '',
                    'breadcrumb' => $this->normalizeDigiflazzText($category->breadcrumb_name . ' ' . $category->slug),
                    'root' => $this->normalizeDigiflazzText($root->name . ' ' . $root->slug),
                    'depth' => $category->depth,
                ];
            })
            ->filter(fn (array $candidate) => (int) $candidate['depth'] > 0)
            ->filter(fn (array $candidate) => $this->containsAnyKeyword($candidate['root'], $rootAliases))
            ->reject(fn (array $candidate) => $topCategorySlug === 'top-up-game' && $this->isGeneratedGameCategoryCandidate($candidate))
            ->reject(fn (array $candidate) => in_array($topCategorySlug, ['kuota-murah', 'paket-data'], true) && $this->isGeneratedQuotaCategoryCandidate($candidate))
            ->values()
            ->all();

        return $this->strictDigiflazzCategoryCandidates[$topCategorySlug];
    }

    protected function manualDigiflazzCategoryIdBySlug(string $slug, string $topCategorySlug): ?int
    {
        return Category::query()
            ->where('slug', $slug)
            ->get()
            ->first(fn (Category $category) => ! $this->isGeneratedCategoryForTop($category, $topCategorySlug))
            ?->id;
    }

    protected function isGeneratedCategoryForTop(Category $category, string $topCategorySlug): bool
    {
        $candidate = [
            'slug' => (string) $category->slug,
            'image' => (string) $category->image,
            'emote' => (string) $category->emote,
        ];

        if ($topCategorySlug === 'top-up-game') {
            return $this->isGeneratedGameCategoryCandidate($candidate);
        }

        if (in_array($topCategorySlug, ['kuota-murah', 'paket-data'], true)) {
            return $this->isGeneratedQuotaCategoryCandidate($candidate);
        }

        return false;
    }

    protected function isGeneratedGameCategoryCandidate(array $candidate): bool
    {
        $slug = $candidate['slug'] ?? '';

        return (in_array($slug, WiboostCatalog::digiflazzGameAutoCategorySlugs(), true) || str_starts_with($slug, 'game-'))
            && (str_starts_with($candidate['image'] ?? '', 'categories/generated/')
                || (blank($candidate['image'] ?? null) && blank($candidate['emote'] ?? null)));
    }

    protected function isGeneratedQuotaCategoryCandidate(array $candidate): bool
    {
        $slug = $candidate['slug'] ?? '';

        return (in_array($slug, WiboostCatalog::digiflazzQuotaAutoCategorySlugs(), true) || str_starts_with($slug, 'kuota-'))
            && (str_starts_with($candidate['image'] ?? '', 'categories/generated/')
                || (blank($candidate['image'] ?? null) && blank($candidate['emote'] ?? null)));
    }

    protected function strictDigiflazzRootAliases(string $topCategorySlug): array
    {
        return match ($topCategorySlug) {
            'top-up-game' => [' top up game ', ' topup game ', ' game ', ' games '],
            'kuota-murah' => [' kuota murah ', ' kuota ', ' paket data ', ' data internet '],
            default => [' ' . $topCategorySlug . ' '],
        };
    }

    protected function strictDigiflazzGroupAliases(string $topCategorySlug): array
    {
        if ($topCategorySlug === 'kuota-murah') {
            return [
                'axis' => [' axis ', ' aigo ', ' bronet ', ' owsem '],
                'by-u' => [' by u ', ' byu ', ' by.u ', ' by u internet ', ' by u paket ', ' byu internet ', ' byu paket '],
                'telkomsel' => [' telkomsel ', ' tsel ', ' kartu as ', ' simpati ', ' loop '],
                'tri' => [' tri ', ' three ', ' aon ', ' always on ', ' alwayson ', ' h3ro '],
                'indosat' => [' indosat ', ' im3 ', ' mentari ', ' yellow ', ' freedom '],
                'smartfren' => [' smartfren ', ' smart ', ' sf '],
                'xl' => [' xl ', ' xtra ', ' xl axiata ', ' hotrod '],
                'lainnya' => [' lainnya ', ' lain ', ' other ', ' others ', ' operator lain ', ' paket lain '],
            ];
        }

        return [
            'free-fire-max' => [' free fire max ', ' freefire max ', ' ff max '],
            'mobile-legends' => [' mobile legends bang bang ', ' mobile legends bangbang ', ' mobile legends ', ' mobile legend ', ' mobilelegend ', ' mlbb ', ' ml '],
            'free-fire' => [' free fire ', ' freefire ', ' ff '],
            'pubg-mobile' => [' pubg ', ' pubg mobile ', ' uc pubg ', ' uc mobile '],
            'valorant' => [' valorant ', ' valorant points ', ' valorant point ', ' vp valorant ', ' valorant vp ', ' valo ', ' vp '],
            'arena-of-valor' => [' arena of valor ', ' aov '],
            'genshin-impact' => [
                ' genshin ',
                ' genshin impact ',
                ' genesis crystal ',
                ' genesis crystals ',
                ' welkin ',
                ' blessing of the welkin ',
                ' blessing welkin ',
            ],
            'call-of-duty-mobile' => [' call of duty ', ' call of duty mobile ', ' codm ', ' cod mobile ', ' cod ', ' cp cod '],
            'fc-mobile' => [' fc mobile ', ' fifa mobile ', ' fifa points ', ' eafc mobile ', ' ea fc mobile ', ' fc points ', ' fc point '],
            'honor-of-kings' => [' honor of kings ', ' hok '],
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
            'lol-pc' => [
                ' lol pc ',
                ' lolpc ',
                ' league of legends pc ',
                ' league of legend pc ',
                ' riot points ',
                ' riot point ',
                ' riot cash ',
                ' rp ',
            ],
            'lainnya' => [' lainnya ', ' lain ', ' other ', ' others '],
        ];
    }

    protected function strictDigiflazzVariantAliases(string $topCategorySlug): array
    {
        if ($topCategorySlug === 'kuota-murah') {
            return [
                'mini' => [' mini '],
                'owsem' => [' owsem '],
                'umum' => [' umum ', ' regular ', ' reguler ', ' normal '],
                'super-kaget' => [' super kaget '],
                'kaget' => [' kaget '],
                'jajan' => [' jajan '],
                'freedom-internet' => [' freedom internet '],
                'freedom-harian' => [' freedom harian ', ' harian '],
                'freedom-combo' => [' freedom combo ', ' combo '],
                'yellow' => [' yellow '],
                'unlimited' => [' unlimited ', ' unlimited nonstop ', ' nonstop ', ' unlimited max ', ' unlimited harian ', ' unlimited booster '],
                'volume' => [' volume '],
                'omg' => [' omg '],
                'flash' => [' flash '],
                'combo-sakti' => [' combo sakti '],
                'happy' => [' happy '],
                'always-on' => [' always on ', ' aon '],
                'xtra-combo' => [' xtra combo ', ' extra combo '],
                'harian' => [' harian ', ' daily '],
            ];
        }

        return [
            'umum' => [' umum ', ' regular ', ' reguler ', ' normal '],
            'indonesia' => [' indonesia ', ' indonesian ', ' indo ', ' id '],
            'malaysia' => [' malaysia ', ' malay ', ' my '],
            'membership' => [' membership ', ' member ', ' mingguan ', ' bulanan ', ' weekly ', ' monthly ', ' pass ', ' diamond pass ', ' weekly diamond pass ', ' welkin ', ' blessing ', ' starlight ', ' twilight ', ' booyah pass '],
            'global' => [' global ', ' worldwide ', ' international '],
            'kombinasi' => [' kombinasi ', ' combo ', ' combination '],
        ];
    }

    protected function isStrictQuotaProductText(string $text): bool
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

    protected function detectStrictQuotaOperator(string $text): ?string
    {
        foreach (['by-u', 'axis', 'telkomsel', 'tri', 'indosat', 'smartfren', 'xl'] as $operator) {
            if ($this->containsAnyKeyword($text, $this->strictDigiflazzGroupAliases('kuota-murah')[$operator])) {
                return $operator;
            }
        }

        return null;
    }

    protected function detectStrictQuotaVariant(string $operator, string $text): string
    {
        return match ($operator) {
            'axis' => match (true) {
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['owsem']) => 'owsem',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['mini']) => 'mini',
                default => 'umum',
            },
            'by-u' => match (true) {
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['super-kaget']) => 'super-kaget',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['kaget']) => 'kaget',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['jajan']) => 'jajan',
                default => 'umum',
            },
            'indosat' => match (true) {
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['freedom-combo']) => 'freedom-combo',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['freedom-harian']) => 'freedom-harian',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['yellow']) => 'yellow',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['freedom-internet']) => 'freedom-internet',
                default => 'umum',
            },
            'smartfren' => match (true) {
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['unlimited']) => 'unlimited',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['volume']) => 'volume',
                default => 'umum',
            },
            'telkomsel' => match (true) {
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['combo-sakti']) => 'combo-sakti',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['omg']) => 'omg',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['flash']) => 'flash',
                default => 'umum',
            },
            'tri' => match (true) {
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['always-on']) => 'always-on',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['happy']) => 'happy',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['mini']) => 'mini',
                default => 'umum',
            },
            'xl' => match (true) {
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['xtra-combo']) => 'xtra-combo',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['harian']) => 'harian',
                $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('kuota-murah')['mini']) => 'mini',
                default => 'umum',
            },
            default => 'umum',
        };
    }

    protected function detectStrictGameKey(string $text): ?string
    {
        foreach (array_keys($this->strictDigiflazzGroupAliases('top-up-game')) as $gameKey) {
            if ($gameKey === 'lainnya') {
                continue;
            }

            if ($this->containsAnyKeyword($text, $this->strictDigiflazzGroupAliases('top-up-game')[$gameKey])) {
                return $gameKey;
            }
        }

        return null;
    }

    protected function detectStrictGameVariant(string $gameKey, string $primaryText, string $allText): ?string
    {
        return match ($gameKey) {
            'arena-of-valor', 'call-of-duty-mobile', 'fc-mobile' => null,
            'free-fire', 'free-fire-max' => $this->containsAnyKeyword($allText, $this->strictDigiflazzVariantAliases('top-up-game')['membership'])
                ? 'membership'
                : 'umum',
            'mobile-legends' => match (true) {
                $this->isStrictMobileLegendsMalaysiaDiamond($primaryText, $allText) => 'malaysia',
                $this->isStrictMobileLegendsIndonesiaDiamond($primaryText, $allText) => 'indonesia',
                $this->isStrictMobileLegendsMembership($primaryText, $allText) || ! $this->isStrictMobileLegendsPlainDiamond($primaryText, $allText) => 'membership',
                default => 'umum',
            },
            'genshin-impact' => match (true) {
                $this->containsAnyKeyword($allText, $this->strictDigiflazzVariantAliases('top-up-game')['membership']) => 'membership',
                $this->containsAnyKeyword($primaryText, $this->strictDigiflazzVariantAliases('top-up-game')['malaysia']) => 'malaysia',
                $this->containsAnyKeyword($primaryText, $this->strictDigiflazzVariantAliases('top-up-game')['indonesia']) => 'indonesia',
                default => 'umum',
            },
            'honor-of-kings' => match (true) {
                $this->containsAnyKeyword($allText, $this->strictDigiflazzVariantAliases('top-up-game')['membership']) => 'membership',
                $this->containsAnyKeyword($primaryText, $this->strictDigiflazzVariantAliases('top-up-game')['kombinasi']) => 'kombinasi',
                default => 'umum',
            },
            'lol-pc', 'lol-wild-rift', 'valorant' => match (true) {
                $this->containsAnyKeyword($primaryText, $this->strictDigiflazzVariantAliases('top-up-game')['malaysia']) => 'malaysia',
                $this->containsAnyKeyword($allText, $this->strictDigiflazzVariantAliases('top-up-game')['membership']) => 'membership',
                $this->containsAnyKeyword($primaryText, $this->strictDigiflazzVariantAliases('top-up-game')['indonesia']) => 'indonesia',
                $this->containsAnyKeyword($primaryText, $this->strictDigiflazzVariantAliases('top-up-game')['global']) => 'global',
                default => 'umum',
            },
            'pubg-mobile' => $this->containsAnyKeyword($primaryText, $this->strictDigiflazzVariantAliases('top-up-game')['global'])
                ? 'global'
                : 'umum',
            default => 'umum',
        };
    }

    protected function isStrictMobileLegendsIndonesiaDiamond(string $primaryText, string $allText): bool
    {
        $text = trim($primaryText . ' ' . $allText);

        if ($this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('top-up-game')['malaysia'])) {
            return false;
        }

        if (! $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('top-up-game')['indonesia'])) {
            return false;
        }

        return $this->isStrictMobileLegendsPlainDiamond($primaryText, $allText);
    }

    protected function isStrictMobileLegendsMalaysiaDiamond(string $primaryText, string $allText): bool
    {
        $text = trim($primaryText . ' ' . $allText);

        if (! $this->containsAnyKeyword($text, $this->strictDigiflazzVariantAliases('top-up-game')['malaysia'])) {
            return false;
        }

        return $this->isStrictMobileLegendsPlainDiamond($primaryText, $allText);
    }

    protected function isStrictMobileLegendsPlainDiamond(string $primaryText, string $allText): bool
    {
        $text = trim($primaryText . ' ' . $allText);

        if (! $this->containsAnyKeyword($text, [' diamond ', ' diamonds ', ' dm '])) {
            return false;
        }

        return ! $this->isStrictMobileLegendsMembership($primaryText, $allText);
    }

    protected function isStrictMobileLegendsMembership(string $primaryText, string $allText): bool
    {
        $text = trim($primaryText . ' ' . $allText);

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

    protected function itemSearchText(array $item): string
    {
        return implode(' ', array_filter([
            $item['brand'] ?? null,
            $item['category'] ?? null,
            $item['product_name'] ?? null,
            $item['buyer_sku_code'] ?? null,
            $item['desc'] ?? null,
        ]));
    }

    protected function itemPrimarySearchText(array $item): string
    {
        return implode(' ', array_filter([
            $item['brand'] ?? null,
            $item['category'] ?? null,
            $item['product_name'] ?? null,
            $item['buyer_sku_code'] ?? null,
        ]));
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

    protected function normalizeDigiflazzText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = Str::lower($text);
        $text = preg_replace('/[^\pL\pN]+/u', ' ', $text) ?? $text;

        return ' ' . trim(preg_replace('/\s+/u', ' ', $text) ?? $text) . ' ';
    }

    protected function categoryIdBySlug(string $slug, int $fallbackId): int
    {
        return Category::query()->where('slug', $slug)->value('id') ?? $fallbackId;
    }
}
