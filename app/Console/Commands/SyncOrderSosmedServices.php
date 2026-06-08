<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Services\OrderSosmedService;
use App\Support\WiboostCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncOrderSosmedServices extends Command
{
    protected $signature = 'sync:ordersosmed
        {--provider-category-ids= : Batasi import berdasarkan ID kategori provider OrderSosmed, contoh: 6735,6276,6302}
        {--target-category= : Paksa semua produk hasil filter masuk ke kategori lokal tertentu, bisa ID, slug, atau nama kategori}
        {--match-local-categories : Import hanya layanan yang cocok dengan kategori lokal sosmed yang sudah dibuat}';

    protected $description = 'Sync layanan dari OrderSosmed ke database Wiboost';

    protected ?array $strictSosmedCategoryCandidates = null;

    public function handle(
        OrderSosmedService $orderSosmedService
    ): int
    {
        $defaultCategoryId = Category::query()
            ->where('slug', 'suntik-sosmed')
            ->value('id') ?? Category::query()->orderBy('id')->value('id');

        if (! $defaultCategoryId) {
            $this->error('Gagal sync: belum ada kategori untuk menampung layanan OrderSosmed.');

            return self::FAILURE;
        }

        $allowedProviderCategoryIds = $this->parseProviderCategoryIds((string) $this->option('provider-category-ids'));
        $targetCategoryId = $this->resolveTargetCategoryId((string) $this->option('target-category'));
        $matchLocalCategories = (bool) $this->option('match-local-categories');

        if (filled((string) $this->option('target-category')) && ! $targetCategoryId) {
            $this->error('Kategori lokal target tidak ditemukan: ' . $this->option('target-category'));
            $this->warn('Gunakan ID kategori lokal, slug, atau nama persis seperti "SOSMED 2".');

            return self::FAILURE;
        }

        if ($allowedProviderCategoryIds !== []) {
            $this->info('Filter kategori provider OrderSosmed: ' . implode(', ', $allowedProviderCategoryIds));

            if ($targetCategoryId) {
                $targetCategory = Category::query()->find($targetCategoryId);
                $this->info('Produk hasil filter akan diarahkan ke kategori lokal: ' . ($targetCategory?->name ?? $targetCategoryId));
            } else {
                $this->info('Produk akan diarahkan ke kategori lokal bernama/slug SOSMED <ID> jika tersedia.');
            }
        }

        if ($matchLocalCategories) {
            $this->info('Mode cocok kategori lokal aktif: hanya produk yang sesuai kategori sosmed lokal yang akan disimpan.');
        }

        $this->info('Mengambil daftar layanan dari OrderSosmed...');
        $response = $orderSosmedService->getServices();
        $syncMode = 'api';

        if (! ($response['success'] ?? false) || empty($response['data'])) {
            $this->error('API private OrderSosmed belum bisa dipakai, sync otomatis dihentikan.');
            $this->error('Pesan provider: ' . ($response['message'] ?? 'Provider tidak mengembalikan daftar layanan.'));

            if ($this->isUnauthorizedIpResponse($response)) {
                $this->warn('Masukkan IP yang muncul di pesan Unauthorized IP ke whitelist OrderSosmed, lalu jalankan sync ulang.');
            } else {
                $this->warn('Upload juga app/Services/OrderSosmedService.php terbaru, lalu jalankan optimize:clear sebelum sync ulang.');
            }

            return self::FAILURE;
        }

        $services = collect($response['data'] ?? [])
            ->filter(fn (array $service) => $this->shouldImportService($service, $allowedProviderCategoryIds))
            ->values();

        if (($response['success'] ?? false) && ! empty($response['data']) && $services->isEmpty()) {
            $this->error('Daftar layanan OrderSosmed berhasil diambil, tetapi filter tidak menemukan produk yang cocok.');

            if ($allowedProviderCategoryIds !== []) {
                $this->warn('Angka di --provider-category-ids harus memakai category_id dari API OrderSosmed, bukan nomor menu di dashboard.');
                $this->warn('Jalankan: php artisan ordersosmed:list-categories --search=Instagram');
            }

            return self::FAILURE;
        }

        if (! ($response['success'] ?? false) || $services->isEmpty()) {
            $this->error('Gagal ambil layanan OrderSosmed: ' . ($response['message'] ?? 'Unknown error'));

            return self::FAILURE;
        }

        $total = $services->count();
        $success = 0;
        $failed = 0;
        $skippedMissingCategory = 0;
        $skippedUnmatchedLocalCategory = 0;
        $matchedProviderProductIds = [];

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($services as $service) {
            try {
                $providerProductId = $this->resolveProviderProductId($service);
                $name = $this->normalizeProductName(
                    (string) ($service['name'] ?? $service['service_name'] ?? $service['service'] ?? '')
                );

                if ($providerProductId === null || $name === '') {
                    $failed++;
                    $bar->advance();
                    continue;
                }

                $providerQuantity = $this->resolveProviderQuantity($service);
                $basePrice = $this->resolveBasePrice($service, $providerQuantity);

                $categoryId = $this->resolveCategoryId($service, $defaultCategoryId, $allowedProviderCategoryIds, $targetCategoryId, $matchLocalCategories);

                if (! $categoryId) {
                    if ($matchLocalCategories) {
                        $skippedUnmatchedLocalCategory++;
                    } else {
                        $skippedMissingCategory++;
                    }

                    $bar->advance();
                    continue;
                }

                $targetMeta = $this->resolveTargetMeta($service, $syncMode);
                $processType = 'api';

                $product = $this->findExistingProduct($providerProductId, $name, $syncMode);

                if (! $product->exists && blank($product->slug)) {
                    $product->slug = Str::slug($name) . '-' . Str::random(5);
                }

                $product->fill([
                    'category_id' => $categoryId,
                    'provider_id' => 'ordersosmed',
                    'provider_source' => 'ordersosmed',
                    'provider_quantity' => $providerQuantity,
                    'process_type' => $processType,
                    'name' => $name,
                    'description' => $this->buildDescription($service, $syncMode),
                    'price' => $basePrice,
                    'provider_product_id' => $providerProductId,
                    'target_label' => $targetMeta['label'],
                    'target_placeholder' => $targetMeta['placeholder'],
                    'target_hint' => $targetMeta['hint'],
                    'is_active' => $basePrice > 0,
                    'status' => $basePrice > 0 ? 'active' : 'inactive',
                    'stock_reminder' => 0,
                ]);

                $product->save();
                $matchedProviderProductIds[] = $providerProductId;
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Gagal sync service OrderSosmed: ' . $e->getMessage(), [
                    'service' => $service,
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nLayanan OrderSosmed selesai disinkronkan.");
        $this->info("Berhasil: {$success}");
        $this->info('Mode sync: API private');

        if ($failed > 0) {
            $this->warn("Gagal: {$failed}");
        }

        if ($skippedMissingCategory > 0) {
            $this->warn("Dilewati karena kategori lokal tidak ditemukan: {$skippedMissingCategory}");
            $this->warn('Pastikan kategori lokal bernama SOSMED 2, SOSMED 3, dan SOSMED 5 sudah dibuat.');
        }

        if ($skippedUnmatchedLocalCategory > 0) {
            $this->warn("Dilewati karena tidak cocok dengan kategori lokal sosmed: {$skippedUnmatchedLocalCategory}");
        }

        if ($success === 0) {
            $this->error('Tidak ada produk OrderSosmed yang berhasil disimpan.');

            if ($matchLocalCategories) {
                $this->warn('Pastikan kategori lokal memakai nama platform/jenis layanan, contoh: Instagram, Like, Like Indonesia, TikTok Views.');
            }

            return self::FAILURE;
        }

        if ($matchLocalCategories) {
            $deactivatedUnmatched = $this->deactivateUnmatchedOrderSosmedProducts($matchedProviderProductIds);

            if ($deactivatedUnmatched > 0) {
                $this->warn("Produk OrderSosmed lama/non-cocok dinonaktifkan: {$deactivatedUnmatched}");
            }
        }

        return ($failed > 0 || $skippedMissingCategory > 0) ? self::FAILURE : self::SUCCESS;
    }

    protected function resolveProviderProductId(array $service): ?string
    {
        $id = $service['id'] ?? $service['service'] ?? $service['service_id'] ?? null;

        if (! filled($id)) {
            return null;
        }

        $catalogType = $service['_ordersosmed_catalog_type'] ?? null;

        if (filled($catalogType)) {
            return $catalogType . ':' . $id;
        }

        return (string) $id;
    }

    protected function resolveProviderQuantity(array $service): int
    {
        if ($this->usesOrderSosmedPerThousandPricing($service)) {
            return 1000;
        }

        $min = (int) round($this->parseProviderNumber($service['min'] ?? $service['minimum'] ?? 0));

        return max(1, $min);
    }

    protected function resolveBasePrice(array $service, int $providerQuantity): float
    {
        if (($service['_ordersosmed_pricing_mode'] ?? null) === 'flat') {
            $price = $this->parseProviderNumber($service['price'] ?? $service['rate'] ?? 0);

            return max(0, $price);
        }

        if (isset($service['price']) && $this->parseProviderNumber($service['price']) > 0) {
            return max(0, $this->parseProviderNumber($service['price']));
        }

        if (isset($service['rate']) && $this->parseProviderNumber($service['rate']) > 0) {
            $rate = $this->parseProviderNumber($service['rate'] ?? 0);

            return max(0, $rate);
        }

        return 0;
    }

    protected function usesOrderSosmedPerThousandPricing(array $service): bool
    {
        return ($service['_ordersosmed_pricing_mode'] ?? null) !== 'flat';
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

    protected function resolveCategoryId(
        array $service,
        int $defaultCategoryId,
        array $allowedProviderCategoryIds = [],
        ?int $targetCategoryId = null,
        bool $matchLocalCategories = false
    ): ?int
    {
        if ($targetCategoryId) {
            return $targetCategoryId;
        }

        if ($matchLocalCategories) {
            return $this->resolveCategoryIdFromLocalMatch($service);
        }

        if ($allowedProviderCategoryIds !== []) {
            return $this->resolveCategoryIdFromProviderCategory($service);
        }

        $subcategorySlug = WiboostCatalog::resolveOrdersosmedSubcategorySlug(
            (string) ($service['name'] ?? $service['service_name'] ?? ''),
            $this->resolveProviderCategoryName($service),
            (string) ($service['description'] ?? $service['note'] ?? ''),
        );

        return Category::query()->where('slug', $subcategorySlug)->value('id')
            ?? Category::query()->where('slug', 'suntik-sosmed')->value('id')
            ?? $defaultCategoryId;
    }

    protected function buildDescription(array $service, string $syncMode): string
    {
        $averageTime = $this->resolveAverageTime($service);

        $chunks = array_filter([
            $service['description'] ?? null,
            $service['note'] ?? null,
            filled($this->resolveProviderCategoryId($service)) ? 'ID kategori provider: ' . $this->resolveProviderCategoryId($service) : null,
            filled($this->resolveProviderCategoryName($service)) ? 'Kategori provider: ' . $this->resolveProviderCategoryName($service) : null,
            isset($service['min']) ? 'Minimum order: ' . $service['min'] : null,
            isset($service['max']) ? 'Maximum order: ' . $service['max'] : null,
            $averageTime ? 'Waktu rata-rata: ' . $averageTime : null,
        ]);

        return $chunks !== [] ? implode("\n", $chunks) : 'Layanan otomatis dari provider OrderSosmed.';
    }

    protected function resolveAverageTime(array $service): ?string
    {
        $keys = [
            'average_time',
            'avg_time',
            'average',
            'time',
            'speed',
            'estimated_time',
            'estimate',
            'estimasi',
            'waktu_rata_rata',
            'start_time',
            'completion_time',
        ];

        foreach ($keys as $key) {
            $value = trim((string) ($service[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        $text = trim((string) ($service['description'] ?? '') . "\n" . (string) ($service['note'] ?? ''));
        $pattern = '/(?:Waktu\s*rata[-\s]*rata|Average\s*time|Avg\.?\s*time|Estimasi(?:\s*waktu)?|Kecepatan|Speed|Start\s*time|Completion\s*time|Waktu\s*proses)\s*[:：-]\s*(.+)$/mi';

        if (preg_match($pattern, $text, $matches) === 1) {
            return trim($matches[1]);
        }

        return null;
    }

    protected function resolveTargetMeta(array $service, string $syncMode): array
    {
        $meta = WiboostCatalog::targetMetaForTopCategory('suntik-sosmed') ?? [
            'label' => 'Username akun / link postingan',
            'placeholder' => 'Contoh: @username atau https://instagram.com/p/abc123',
            'hint' => 'Masukkan username akun atau link postingan yang aktif sesuai layanan suntik sosmed yang dipilih.',
        ];

        return $meta;
    }

    protected function shouldImportService(array $service, array $allowedProviderCategoryIds = []): bool
    {
        $catalogType = strtolower((string) ($service['_ordersosmed_catalog_type'] ?? 'sosmed'));
        $category = strtolower($this->resolveProviderCategoryName($service));
        $name = strtolower((string) ($service['name'] ?? $service['service_name'] ?? ''));

        if ($catalogType !== 'sosmed') {
            return false;
        }

        if ($this->isCommentServiceText($category . ' ' . $name)) {
            return false;
        }

        if (str_contains($category, 'buzzer') || str_contains($name, 'buzzer')) {
            return false;
        }

        if ($allowedProviderCategoryIds !== []) {
            $providerCategoryId = $this->resolveProviderCategoryId($service);

            return $providerCategoryId !== null
                && in_array($providerCategoryId, $allowedProviderCategoryIds, true);
        }

        return true;
    }

    protected function parseProviderCategoryIds(string $categoryIds): array
    {
        return collect(explode(',', $categoryIds))
            ->map(fn (string $id) => trim($id))
            ->filter(fn (string $id) => $id !== '')
            ->unique()
            ->values()
            ->all();
    }

    protected function isUnauthorizedIpResponse(array $response): bool
    {
        $message = strtolower((string) ($response['message'] ?? ''));
        $raw = strtolower(json_encode($response['raw'] ?? $response, JSON_UNESCAPED_UNICODE) ?: '');

        return str_contains($message, 'unauthorized ip')
            || str_contains($message, 'ip anda tidak kami kenali')
            || str_contains($raw, 'unauthorized ip')
            || str_contains($raw, 'ip anda tidak kami kenali');
    }

    protected function resolveTargetCategoryId(string $category): ?int
    {
        $category = trim($category);

        if ($category === '') {
            return null;
        }

        return Category::query()
            ->when(is_numeric($category), fn ($query) => $query->orWhereKey((int) $category))
            ->orWhere('slug', $category)
            ->orWhere('slug', Str::slug($category))
            ->orWhere('name', $category)
            ->value('id');
    }

    protected function resolveProviderCategoryId(array $service): ?string
    {
        foreach (['category_id', 'categoryId', 'cat_id', 'catId'] as $key) {
            $value = trim((string) ($service[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function resolveProviderCategoryName(array $service): string
    {
        foreach (['category_name', 'category', 'type'] as $key) {
            $value = trim((string) ($service[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    protected function resolveCategoryIdFromProviderCategory(array $service): ?int
    {
        $providerCategoryId = $this->resolveProviderCategoryId($service);
        $providerCategoryName = $this->resolveProviderCategoryName($service);

        $slugCandidates = collect([
            filled($providerCategoryId) ? 'sosmed-' . $providerCategoryId : null,
            filled($providerCategoryId) ? Str::slug('SOSMED ' . $providerCategoryId) : null,
            filled($providerCategoryName) ? Str::slug($providerCategoryName) : null,
        ])->filter()->unique()->values()->all();

        $nameCandidates = collect([
            filled($providerCategoryId) ? 'SOSMED ' . $providerCategoryId : null,
            filled($providerCategoryId) ? 'Sosmed ' . $providerCategoryId : null,
            filled($providerCategoryName) ? $providerCategoryName : null,
        ])->filter()->unique()->values()->all();

        return Category::query()
            ->whereIn('slug', $slugCandidates)
            ->orWhereIn('name', $nameCandidates)
            ->value('id');
    }

    protected function resolveCategoryIdFromLocalMatch(array $service): ?int
    {
        $serviceText = $this->normalizeMatchText(implode(' ', array_filter([
            $service['service_name'] ?? null,
            $service['name'] ?? null,
        ])));

        $providerCategoryText = $this->normalizeMatchText($this->resolveProviderCategoryName($service));
        $descriptionText = $this->normalizeMatchText((string) ($service['description'] ?? $service['note'] ?? ''));

        if ($this->isCommentServiceText($serviceText . ' ' . $providerCategoryText)) {
            return null;
        }

        $classification = $this->classifyStrictSosmedService($serviceText, $providerCategoryText);

        if ($classification === null && trim($descriptionText) !== '') {
            $classification = $this->classifyStrictSosmedService(
                $this->normalizeMatchText($serviceText . ' ' . $descriptionText),
                $providerCategoryText
            );
        }

        if ($classification === null) {
            return null;
        }

        return $this->findStrictSosmedCategoryId(
            $classification['platform'],
            $classification['metric'],
            $classification['region'] ?? null,
        );
    }

    protected function classifyStrictSosmedService(string $serviceText, string $providerCategoryText): ?array
    {
        $servicePlatforms = $this->detectTargetSosmedPlatforms($serviceText);

        if (
            $this->containsNonTargetSosmedPlatform($serviceText)
            || ($servicePlatforms === [] && $this->containsGenericTrafficText($serviceText))
        ) {
            return null;
        }

        $categoryPlatforms = $this->containsNonTargetSosmedPlatform($providerCategoryText)
            ? []
            : $this->detectTargetSosmedPlatforms($providerCategoryText);

        if (count($servicePlatforms) > 1) {
            return null;
        }

        $platform = count($servicePlatforms) === 1
            ? $servicePlatforms[0]
            : (count($categoryPlatforms) === 1 ? $categoryPlatforms[0] : null);

        if ($platform === null) {
            return null;
        }

        $classificationText = $this->normalizeMatchText($serviceText . ' ' . $providerCategoryText);

        if ($platform !== 'youtube' && $this->isLiveSosmedServiceText($classificationText)) {
            return [
                'platform' => $platform,
                'metric' => 'lainnya',
                'region' => null,
            ];
        }

        if ($platform === 'youtube') {
            return [
                'platform' => 'youtube',
                'metric' => $this->classifyStrictYoutubeMetric($serviceText, $providerCategoryText),
                'region' => null,
            ];
        }

        $metric = $this->classifyStrictInstagramTiktokMetric($serviceText, $providerCategoryText);

        return [
            'platform' => $platform,
            'metric' => $metric,
            'region' => in_array($metric, ['followers', 'like'], true)
                ? $this->classifyStrictRegion($classificationText)
                : null,
        ];
    }

    protected function detectTargetSosmedPlatforms(string $text): array
    {
        $matches = [
            'instagram' => $this->containsAnyKeyword($text, [' instagram ', ' instagram com ', ' ig ']),
            'tiktok' => $this->containsAnyKeyword($text, [' tiktok ', ' tik tok ']),
            'youtube' => $this->containsAnyKeyword($text, [' youtube ', ' youtub ', ' yt ']),
        ];

        return collect($matches)
            ->filter()
            ->keys()
            ->values()
            ->all();
    }

    protected function containsNonTargetSosmedPlatform(string $text): bool
    {
        return $this->containsAnyKeyword($text, [
            ' telegram ',
            ' tele ',
            ' facebook ',
            ' fb ',
            ' twitter ',
            ' tweet ',
            ' retweet ',
            ' x twitter ',
            ' spotify ',
            ' whatsapp ',
            ' discord ',
            ' twitch ',
            ' soundcloud ',
            ' shopee ',
            ' tokopedia ',
            ' google maps ',
            ' maps ',
            ' threads ',
            ' linkedin ',
            ' pinterest ',
            ' snapchat ',
            ' capcut ',
        ]);
    }

    protected function containsGenericTrafficText(string $text): bool
    {
        return $this->containsAnyKeyword($text, [
            ' website ',
            ' traffic ',
            ' visit ',
            ' seo ',
        ]);
    }

    protected function classifyStrictInstagramTiktokMetric(string $serviceText, string $providerCategoryText): string
    {
        $primaryMetrics = $this->matchingStrictInstagramTiktokMetrics($serviceText);

        if (count($primaryMetrics) === 1) {
            return $primaryMetrics[0];
        }

        if (count($primaryMetrics) > 1) {
            return 'lainnya';
        }

        $categoryMetrics = $this->matchingStrictInstagramTiktokMetrics($providerCategoryText);

        return count($categoryMetrics) === 1 ? $categoryMetrics[0] : 'lainnya';
    }

    protected function matchingStrictInstagramTiktokMetrics(string $text): array
    {
        if ($this->containsAnyKeyword($text, [' reaction ', ' reactions ', ' react ', ' reacts '])) {
            return ['lainnya'];
        }

        $matches = [
            'followers' => $this->containsAnyKeyword($text, [' follower ', ' followers ']),
            'like' => $this->containsAnyKeyword($text, [' like ', ' likes ', ' heart ', ' hearts ']),
            'views' => $this->containsAnyKeyword($text, [' view ', ' views ', ' viewer ', ' viewers ', ' reach ', ' impression ', ' impressions ', ' play ', ' plays ']),
            'repost' => $this->containsAnyKeyword($text, [' repost ', ' reposts ']),
            'save' => $this->containsAnyKeyword($text, [' save ', ' saves ', ' saved ', ' bookmark ', ' favorites ', ' favourite ', ' favorite ']),
            'share' => $this->containsAnyKeyword($text, [' share ', ' shares ', ' shared ']),
        ];

        $positiveMetrics = collect($matches)
            ->filter()
            ->keys()
            ->all();

        return $positiveMetrics;
    }

    protected function classifyStrictYoutubeMetric(string $serviceText, string $providerCategoryText): string
    {
        $primaryMetrics = $this->matchingStrictYoutubeMetrics($serviceText);

        if (count($primaryMetrics) === 1) {
            return $primaryMetrics[0];
        }

        if (count($primaryMetrics) > 1) {
            return 'lainnya';
        }

        $categoryMetrics = $this->matchingStrictYoutubeMetrics($providerCategoryText);

        return count($categoryMetrics) === 1 ? $categoryMetrics[0] : 'lainnya';
    }

    protected function matchingStrictYoutubeMetrics(string $text): array
    {
        $matches = [
            'subscribers' => $this->containsAnyKeyword($text, [' subscriber ', ' subscribers ', ' subs ']),
            'like' => $this->containsAnyKeyword($text, [' like ', ' likes ']),
            'view' => $this->containsAnyKeyword($text, [' view ', ' views ', ' viewer ', ' viewers ', ' watchtime ', ' watch time ', ' play ', ' plays ']),
        ];

        $positiveMetrics = collect($matches)
            ->filter()
            ->keys()
            ->all();

        return $positiveMetrics;
    }

    protected function classifyStrictRegion(string $text): string
    {
        if ($this->containsAnyKeyword($text, [' indonesia ', ' indonesian ', ' indo ', ' lokal ', ' local indonesia ', ' wni '])) {
            return 'indonesia';
        }

        return 'global';
    }

    protected function findStrictSosmedCategoryId(string $platform, string $metric, ?string $region = null): ?int
    {
        $bestCandidate = null;
        $bestScore = 0;

        foreach ($this->strictSosmedCategoryCandidates() as $candidate) {
            if (! $this->containsAnyKeyword($candidate['breadcrumb'], $this->strictCategoryAliases($platform))) {
                continue;
            }

            if (! $this->containsAnyKeyword($candidate['breadcrumb'], $this->strictCategoryAliases($metric))) {
                continue;
            }

            if ($region !== null && ! $this->containsAnyKeyword($candidate['breadcrumb'], $this->strictCategoryAliases($region))) {
                continue;
            }

            $score = (int) $candidate['depth'];

            if ($this->containsAnyKeyword($candidate['own'], $this->strictCategoryAliases($metric))) {
                $score += 100;
            }

            if ($region !== null && $this->containsAnyKeyword($candidate['own'], $this->strictCategoryAliases($region))) {
                $score += 140;
            }

            if ($this->containsAnyKeyword($candidate['parent'], $this->strictCategoryAliases($metric))) {
                $score += 80;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestCandidate = $candidate;
            }
        }

        if ($bestCandidate !== null) {
            return (int) $bestCandidate['id'];
        }

        return null;
    }

    protected function strictSosmedCategoryCandidates(): array
    {
        if ($this->strictSosmedCategoryCandidates !== null) {
            return $this->strictSosmedCategoryCandidates;
        }

        $this->strictSosmedCategoryCandidates = Category::query()
            ->with('parent.parent.parent.parent')
            ->get()
            ->map(function (Category $category) {
                $parent = $category->parent;

                return [
                    'id' => $category->id,
                    'own' => $this->normalizeMatchText($category->name . ' ' . $category->slug),
                    'parent' => $parent ? $this->normalizeMatchText($parent->name . ' ' . $parent->slug) : '',
                    'breadcrumb' => $this->normalizeMatchText($category->breadcrumb_name . ' ' . $category->slug),
                    'depth' => $category->depth,
                ];
            })
            ->filter(function (array $candidate) {
                $text = $candidate['breadcrumb'];

                if ($this->containsAnyKeyword($text, [' top up ', ' game ', ' kuota ', ' pulsa ', ' aplikasi premium ', ' nomor luar ', ' buzzer ', ' digiflazz '])) {
                    return false;
                }

                return $this->containsAnyKeyword($text, [' suntik sosmed ', ' social media ', ' sosmed ', ' instagram ', ' tiktok ', ' youtube ']);
            })
            ->values()
            ->all();

        return $this->strictSosmedCategoryCandidates;
    }

    protected function strictCategoryAliases(string $key): array
    {
        return match ($key) {
            'instagram' => [' instagram '],
            'tiktok' => [' tiktok ', ' tik tok '],
            'youtube' => [' youtube '],
            'followers' => [' followers ', ' follower '],
            'like' => [' like ', ' likes '],
            'views', 'view' => [' views ', ' view ', ' viewer ', ' viewers '],
            'repost' => [' repost ', ' reposts '],
            'save' => [' save ', ' saves ', ' saved '],
            'share' => [' share ', ' shares ', ' shared '],
            'subscribers' => [' subscribers ', ' subscriber ', ' subs '],
            'lainnya' => [' lainnya ', ' lain ', ' other ', ' others '],
            'indonesia' => [' indonesia ', ' indo ', ' lokal ', ' local '],
            'global' => [' global ', ' worldwide ', ' luar negeri ', ' international '],
            default => [' ' . $key . ' '],
        };
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

    protected function isCommentServiceText(string $text): bool
    {
        $text = $this->normalizeMatchText($text);

        return $this->containsAnyKeyword($text, [
            ' comment ',
            ' comments ',
            ' komentar ',
            ' komen ',
            ' reply ',
            ' replies ',
            ' balasan ',
        ]);
    }

    protected function isLiveSosmedServiceText(string $text): bool
    {
        return $this->containsAnyKeyword($text, [
            ' live ',
            ' livestream ',
            ' live stream ',
            ' siaran langsung ',
        ]);
    }

    protected function matchKeywordGroup(string $text, array $groups): ?string
    {
        foreach ($groups as $name => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return $name;
                }
            }
        }

        return null;
    }

    protected function normalizeMatchText(string $text): string
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

    protected function normalizeProductName(string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            return '';
        }

        return trim(Str::substr($name, 0, 255));
    }

    protected function findExistingProduct(string $providerProductId, string $name, string $syncMode): Product
    {
        $exactMatch = Product::query()
            ->where('provider_source', 'ordersosmed')
            ->where('provider_product_id', $providerProductId)
            ->first();

        if ($exactMatch) {
            return $exactMatch;
        }

        if ($syncMode === 'api') {
            $suffixMatches = Product::query()
                ->where('provider_source', 'ordersosmed')
                ->where('provider_product_id', 'like', '%:' . $providerProductId)
                ->get();

            if ($suffixMatches->count() === 1) {
                return $suffixMatches->first();
            }
        }

        return new Product([
            'provider_source' => 'ordersosmed',
            'provider_product_id' => $providerProductId,
        ]);
    }

    protected function deactivateUnmatchedOrderSosmedProducts(array $matchedProviderProductIds): int
    {
        $matchedProviderProductIds = collect($matchedProviderProductIds)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($matchedProviderProductIds === []) {
            return 0;
        }

        return Product::query()
            ->where('provider_source', 'ordersosmed')
            ->where('process_type', 'api')
            ->where(function ($query) use ($matchedProviderProductIds) {
                $query->whereNull('provider_product_id')
                    ->orWhereNotIn('provider_product_id', $matchedProviderProductIds);
            })
            ->update([
                'is_active' => false,
                'status' => 'inactive',
                'updated_at' => now(),
            ]);
    }
}
