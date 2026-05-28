<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Services\DigiflazzService;
use App\Support\DigiflazzProductLogo;
use App\Support\WiboostCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncDigiflazzProducts extends Command
{
    protected $signature = 'sync:digiflazz
        {--match-local-categories : Import hanya produk Digiflazz yang cocok dengan kategori lokal Top Up Game dan Kuota Murah}';

    protected $description = 'Sync produk dari Digiflazz ke database Wiboost dengan mapping kategori otomatis';

    protected ?array $strictDigiflazzCategoryCandidates = null;

    public function handle(DigiflazzService $digiService): int
    {
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
                $topCategorySlug = $matchLocalCategories
                    ? $this->resolveStrictTopCategorySlug($item)
                    : WiboostCatalog::resolveDigiflazzTopCategorySlug(
                        (string) ($item['product_name'] ?? ''),
                        (string) ($item['brand'] ?? ''),
                        (string) ($item['category'] ?? ''),
                        (string) ($item['desc'] ?? ''),
                    );

                if ($topCategorySlug === null) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $hargaModal = $this->parseProviderNumber($item['price'] ?? 0);

                $categoryId = $matchLocalCategories
                    ? $this->resolveStrictLocalCategoryId($item, $topCategorySlug)
                    : $this->resolveDefaultCategoryId($item, $topCategorySlug, $defaultCategoryId);

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
        $subcategorySlug = WiboostCatalog::resolveDigiflazzSubcategorySlug(
            $topCategorySlug,
            (string) ($item['product_name'] ?? ''),
            (string) ($item['brand'] ?? ''),
            (string) ($item['desc'] ?? ''),
        );

        return $subcategorySlug
            ? (Category::query()->where('slug', $subcategorySlug)->value('id')
                ?? $this->categoryIdBySlug($topCategorySlug, $defaultCategoryId))
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

        foreach ($this->strictDigiflazzCategoryCandidates($topCategorySlug) as $candidate) {
            if (! $this->containsAnyKeyword($candidate['breadcrumb'], $groupAliases)) {
                continue;
            }

            if ($variantKey !== null && ! $this->containsAnyKeyword($candidate['breadcrumb'], $variantAliases)) {
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

        return $bestCandidate ? (int) $bestCandidate['id'] : null;
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
                    'own' => $this->normalizeDigiflazzText($category->name . ' ' . $category->slug),
                    'parent' => $parent ? $this->normalizeDigiflazzText($parent->name . ' ' . $parent->slug) : '',
                    'breadcrumb' => $this->normalizeDigiflazzText($category->breadcrumb_name . ' ' . $category->slug),
                    'root' => $this->normalizeDigiflazzText($root->name . ' ' . $root->slug),
                    'depth' => $category->depth,
                ];
            })
            ->filter(fn (array $candidate) => (int) $candidate['depth'] > 0)
            ->filter(fn (array $candidate) => $this->containsAnyKeyword($candidate['root'], $rootAliases))
            ->values()
            ->all();

        return $this->strictDigiflazzCategoryCandidates[$topCategorySlug];
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
                'axis' => [' axis ', ' aigo '],
                'by-u' => [' by u ', ' byu '],
                'telkomsel' => [' telkomsel ', ' tsel ', ' kartu as ', ' simpati ', ' loop '],
                'tri' => [' tri ', ' three '],
                'indosat' => [' indosat ', ' im3 ', ' mentari '],
                'smartfren' => [' smartfren '],
                'xl' => [' xl ', ' xtra ', ' xl axiata '],
                'lainnya' => [' lainnya ', ' lain ', ' other ', ' others '],
            ];
        }

        return [
            'free-fire-max' => [' free fire max ', ' freefire max ', ' ff max '],
            'mobile-legends' => [' mobile legends ', ' mobile legend ', ' mobilelegend ', ' mlbb ', ' ml '],
            'free-fire' => [' free fire ', ' freefire ', ' ff '],
            'pubg-mobile' => [' pubg ', ' pubg mobile ', ' uc pubg ', ' uc mobile '],
            'valorant' => [' valorant ', ' valorant points ', ' vp ', ' valorant point '],
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
                ' wild rift ',
                ' wildrift ',
                ' wild core ',
                ' wild cores ',
                ' lol wild rift ',
                ' league of legends wild rift ',
                ' league of legend wild rift ',
                ' lol wr ',
                ' wr ',
            ],
            'lol-pc' => [
                ' lol pc ',
                ' lolpc ',
                ' league of legends pc ',
                ' league of legend pc ',
                ' league of legends ',
                ' league of legend ',
                ' riot points ',
                ' riot point ',
                ' riot cash ',
                ' lol ',
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
                'unlimited' => [' unlimited '],
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
            'genshin-impact', 'mobile-legends' => match (true) {
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
            'lol-pc', 'lol-wild-rift', 'valorant' => $this->containsAnyKeyword($primaryText, $this->strictDigiflazzVariantAliases('top-up-game')['malaysia'])
                ? 'malaysia'
                : 'umum',
            'pubg-mobile' => $this->containsAnyKeyword($primaryText, $this->strictDigiflazzVariantAliases('top-up-game')['global'])
                ? 'global'
                : 'umum',
            default => 'umum',
        };
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
