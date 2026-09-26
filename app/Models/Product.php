<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Support\WiboostCatalog;

class Product extends Model
{
    protected const FRIDAY_BLESSING_DISCOUNT_RATE = 0.03;

    protected const API_SELLING_PRICE_MARKUP_RATES = [
        'ordersosmed' => 0.50,
        'digiflazz' => 0.05,
    ];

    protected const API_CATEGORY_MARKUP_RATES = [
        'suntik-sosmed' => 0.50,
        'top-up-game' => 0.05,
        'kuota-murah' => 0.10,
        'paket-data' => 0.10,
    ];

    protected const MEMBER_API_CATEGORY_MARKUP_RATES = [
        'suntik-sosmed' => 0.35,
        'top-up-game' => 0.04,
        'kuota-murah' => 0.07,
        'paket-data' => 0.07,
    ];

    protected const API_PROVIDER_SOURCES = [
        'ordersosmed',
        'digiflazz',
    ];

    protected bool $resolvedPrimaryCategorySlugLoaded = false;

    protected ?string $resolvedPrimaryCategorySlugCache = null;

    protected bool $resolvedPrimaryCategoryNameLoaded = false;

    protected ?string $resolvedPrimaryCategoryNameCache = null;

    protected bool $apiSellingMarkupRateLoaded = false;

    protected float $apiSellingMarkupRateCache = 0.0;

    protected bool $checkoutFieldsLoaded = false;

    protected array $checkoutFieldsCache = [];

    protected bool $topUpGameKeyLoaded = false;

    protected ?string $topUpGameKeyCache = null;

    protected ?string $topUpGameSearchTextCache = null;

    protected $fillable = [
        'category_id',
        'provider_id',
        'process_type',
        'name',
        'slug',
        'description',
        'price',
        'membership_price',
        'discount_percent',
        'provider_product_id', // Penting untuk menyimpan SKU Digiflazz
        'provider_source',
        'provider_quantity',
        'target_label',
        'target_placeholder',
        'target_hint',
        'requires_buyer_email',
        'stock_reminder',
        'image',
        'emote',
        'is_active',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_buyer_email' => 'boolean',
        'provider_quantity' => 'integer',
        'price' => 'decimal:2',
        'membership_price' => 'decimal:2',
        'discount_percent' => 'integer',
    ];

    // Relasi ke Kategori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke stok lama jika masih ada data historis.
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    // Relasi ke kredensial (untuk akun premium/netflix dll)
    public function credentials()
    {
        return $this->hasMany(ProductCredential::class);
    }

    // Relasi ke transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Accessor untuk menghitung sisa stok otomatis khusus akun premium.
     */
    public function getAvailableStockAttribute()
    {
        if ($this->process_type !== 'account') return null;

        if (array_key_exists('available_stock_cache', $this->attributes)) {
            return max(0, (int) $this->attributes['available_stock_cache']);
        }
        
        return $this->credentials()->where('is_active', true)->get()->sum(function($cred) {
            return $cred->max_usage - $cred->current_usage;
        });
    }

    public function getRequiresTargetInputAttribute(): bool
    {
        return count($this->checkout_fields) > 0;
    }

    public function getCheckoutFieldsAttribute(): array
    {
        if ($this->checkoutFieldsLoaded) {
            return $this->checkoutFieldsCache;
        }

        $topCategorySlug = $this->resolvePrimaryCategorySlug();

        if ($topCategorySlug === 'aplikasi-premium' && ! $this->requires_buyer_email) {
            return $this->rememberCheckoutFields([]);
        }

        if ($topCategorySlug === 'top-up-game') {
            return $this->rememberCheckoutFields($this->checkoutFieldsForTopUpGameProduct());
        }

        $checkoutFields = WiboostCatalog::checkoutFieldsForTopCategory($topCategorySlug);

        if ($topCategorySlug === 'suntik-sosmed' && $this->is_ordersosmed_service) {
            $checkoutFields[] = [
                'name' => 'order_quantity',
                'label' => 'Jumlah Pesanan',
                'type' => 'number',
                'placeholder' => '',
                'hint' => '',
                'rules' => [
                    'required',
                    'integer',
                    'min:' . $this->ordersosmed_min_quantity,
                    'max:' . $this->ordersosmed_max_quantity,
                ],
            ];
        }

        if ($checkoutFields !== []) {
            return $this->rememberCheckoutFields($checkoutFields);
        }

        if (! empty($this->target_label) || ! empty($this->target_placeholder) || ! empty($this->target_hint)) {
            return $this->rememberCheckoutFields([[
                'name' => 'target_data',
                'label' => $this->target_label ?: 'Target pesanan',
                'type' => 'text',
                'placeholder' => $this->target_placeholder ?: 'Masukkan data tujuan pesanan',
                'hint' => $this->target_hint,
                'rules' => ['required', 'string', 'min:3', 'max:255'],
                'target_summary' => true,
            ]]);
        }

        return $this->rememberCheckoutFields(match ($this->process_type) {
            'api' => [[
                'name' => 'target_data',
                'label' => $this->provider_source === 'digiflazz'
                    ? 'Data tujuan (user ID, zone ID, atau nomor tujuan)'
                    : 'Target pesanan (username atau link profile)',
                'type' => 'text',
                'placeholder' => $this->provider_source === 'digiflazz'
                    ? 'Contoh: 12345678 (1234) atau 081234567890'
                    : 'Contoh: https://instagram.com/username',
                'hint' => $this->provider_source === 'digiflazz'
                    ? 'Pastikan ID, zone, atau nomor tujuan sudah benar agar pesanan tidak gagal.'
                    : 'Gunakan username atau link yang aktif dan bisa diakses publik.',
                'rules' => ['required', 'string', 'min:3', 'max:255'],
                'target_summary' => true,
            ]],
            default => [],
        });
    }

    protected function rememberCheckoutFields(array $fields): array
    {
        $this->checkoutFieldsLoaded = true;
        $this->checkoutFieldsCache = $fields;

        return $this->checkoutFieldsCache;
    }

    protected function resolvePrimaryCategorySlug(): ?string
    {
        if ($this->resolvedPrimaryCategorySlugLoaded) {
            return $this->resolvedPrimaryCategorySlugCache;
        }

        if (! $this->relationLoaded('category')) {
            $this->loadMissing('category.parent.parent');
        } else {
            $this->category?->loadMissing('parent.parent');
        }

        $category = $this->category;

        if (! $category) {
            $this->resolvedPrimaryCategorySlugLoaded = true;
            $this->resolvedPrimaryCategorySlugCache = null;

            return null;
        }

        while ($category->parent) {
            $category = $category->parent;
        }

        $this->resolvedPrimaryCategorySlugLoaded = true;
        $this->resolvedPrimaryCategorySlugCache = $category->slug;
        $this->resolvedPrimaryCategoryNameLoaded = true;
        $this->resolvedPrimaryCategoryNameCache = $category->name;

        return $this->resolvedPrimaryCategorySlugCache;
    }

    protected function resolvePrimaryCategoryName(): ?string
    {
        if ($this->resolvedPrimaryCategoryNameLoaded) {
            return $this->resolvedPrimaryCategoryNameCache;
        }

        $this->resolvePrimaryCategorySlug();

        return $this->resolvedPrimaryCategoryNameCache;
    }

    protected function resolveCategoryTargetMeta(): ?array
    {
        return WiboostCatalog::targetMetaForTopCategory($this->resolvePrimaryCategorySlug());
    }

    protected function checkoutFieldsForTopUpGameProduct(): array
    {
        return match ($this->resolveTopUpGameKey()) {
            'mobile-legends' => [
                $this->checkoutField(
                    'game_user_id',
                    'User ID Mobile Legends',
                    'text',
                    'Contoh: 123456789',
                    'Masukkan User ID Mobile Legends dengan benar.',
                    ['required', 'string', 'min:2', 'max:100'],
                    targetSummary: true,
                    providerCustomerNo: true
                ),
                $this->checkoutField(
                    'game_zone_id',
                    'Zone ID Mobile Legends',
                    'text',
                    'Contoh: 1234',
                    'Masukkan Zone ID yang ada di akun Mobile Legends.',
                    ['required', 'string', 'min:1', 'max:50'],
                    targetSummary: true,
                    providerCustomerNo: true
                ),
            ],
            'genshin-impact' => [
                $this->checkoutField(
                    'game_user_id',
                    'UID Genshin Impact',
                    'text',
                    'Contoh: 812345678',
                    'Masukkan UID Genshin Impact dengan benar.',
                    ['required', 'string', 'min:3', 'max:100'],
                    targetSummary: true,
                    providerCustomerNo: true
                ),
                $this->checkoutField(
                    'game_server',
                    'Server Genshin Impact',
                    'text',
                    'Contoh: Asia',
                    'Isi server sesuai akun, misalnya Asia, America, Europe, atau TW/HK/MO.',
                    ['required', 'string', 'min:2', 'max:50'],
                    targetSummary: true
                ),
            ],
            'free-fire', 'free-fire-max' => [
                $this->singleGameIdField('Player ID Free Fire', 'Contoh: 123456789', 'Masukkan Player ID Free Fire dengan benar.'),
            ],
            'pubg-mobile' => [
                $this->singleGameIdField('Character ID PUBG Mobile', 'Contoh: 5123456789', 'Masukkan Character ID PUBG Mobile dengan benar.'),
            ],
            'valorant' => [
                $this->singleGameIdField('Riot ID / Username Valorant', 'Contoh: username#TAG', 'Masukkan Riot ID atau username Valorant sesuai instruksi produk.'),
            ],
            'lol-pc' => [
                $this->singleGameIdField('Riot ID League of Legends', 'Contoh: username#TAG', 'Masukkan Riot ID League of Legends sesuai akun.'),
            ],
            'lol-wild-rift' => [
                $this->singleGameIdField('Riot ID Wild Rift', 'Contoh: username#TAG', 'Masukkan Riot ID Wild Rift sesuai akun.'),
            ],
            'cod-mobile' => [
                $this->singleGameIdField('Open ID / User ID COD Mobile', 'Contoh: 674123456789', 'Masukkan Open ID atau User ID COD Mobile dengan benar.'),
            ],
            'fc-mobile' => [
                $this->singleGameIdField('User ID FC Mobile', 'Contoh: 123456789', 'Masukkan User ID FC Mobile dengan benar.'),
            ],
            'honor-of-kings' => [
                $this->singleGameIdField('User ID Honor of Kings', 'Contoh: 123456789', 'Masukkan User ID Honor of Kings dengan benar.'),
            ],
            'arena-of-valor' => [
                $this->singleGameIdField('User ID Arena of Valor', 'Contoh: 123456789', 'Masukkan User ID Arena of Valor dengan benar.'),
            ],
            default => [
                $this->singleGameIdField('User ID / Nomor tujuan game', 'Contoh: 123456789', 'Masukkan data tujuan sesuai instruksi produk.'),
            ],
        };
    }

    protected function singleGameIdField(string $label, string $placeholder, string $hint): array
    {
        return $this->checkoutField(
            'game_user_id',
            $label,
            'text',
            $placeholder,
            $hint,
            ['required', 'string', 'min:2', 'max:100'],
            targetSummary: true,
            providerCustomerNo: true
        );
    }

    protected function checkoutField(
        string $name,
        string $label,
        string $type,
        string $placeholder,
        string $hint,
        array $rules,
        bool $targetSummary = false,
        bool $providerCustomerNo = false
    ): array {
        return [
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'placeholder' => $placeholder,
            'hint' => $hint,
            'rules' => $rules,
            'target_summary' => $targetSummary,
            'provider_customer_no' => $providerCustomerNo,
        ];
    }

    protected function resolveTopUpGameKey(): ?string
    {
        if ($this->topUpGameKeyLoaded) {
            return $this->topUpGameKeyCache;
        }

        $text = $this->normalizedTopUpGameSearchText();

        $this->topUpGameKeyLoaded = true;
        $this->topUpGameKeyCache = match (true) {
            $this->containsAny($text, ['mobile legends bang bang', 'mobile legends bangbang', 'mobile legends', 'mobile legend', 'mobilelegend', 'mlbb', ' ml ', 'game mobile legends']) => 'mobile-legends',
            $this->containsAny($text, ['free fire max', 'freefire max', 'ff max', 'game free fire max']) => 'free-fire-max',
            $this->containsAny($text, ['free fire', 'freefire', 'ff', 'game free fire']) => 'free-fire',
            $this->containsAny($text, ['pubg', 'pubg mobile', 'uc pubg', 'uc mobile']) => 'pubg-mobile',
            $this->containsAny($text, ['valorant', 'valorant points', 'valorant point', 'valorant vp', 'vp valorant', 'valo', ' vp ']) => 'valorant',
            $this->containsAny($text, ['arena of valor', ' aov ', 'game arena of valor']) => 'arena-of-valor',
            $this->containsAny($text, ['call of duty mobile', 'call of duty', 'cod mobile', 'codm', 'cp cod']) => 'cod-mobile',
            $this->containsAny($text, ['fc mobile', 'fifa mobile', 'fifa points', 'eafc mobile', 'ea fc mobile', 'fc points', 'fc point']) => 'fc-mobile',
            $this->containsAny($text, ['genshin impact', 'genshin', 'genesis crystal', 'genesis crystals', 'welkin']) => 'genshin-impact',
            $this->containsAny($text, ['honor of kings', 'hok']) => 'honor-of-kings',
            $this->containsAny($text, ['league of legends wild rift', 'league of legend wild rift', 'lol wild rift', 'wild rift', 'wildrift', 'wild core', 'wild cores', 'wild pass', 'wildrift pass', 'lol wr', 'lolwr', 'wr wild core', 'wr wild cores']) => 'lol-wild-rift',
            $this->containsAny($text, ['league of legends pc', 'league of legend pc', 'lol pc', 'league pc', 'riot points', 'riot point', 'riot cash']) => 'lol-pc',
            default => null,
        };

        return $this->topUpGameKeyCache;
    }

    protected function normalizedTopUpGameSearchText(): string
    {
        if ($this->topUpGameSearchTextCache !== null) {
            return $this->topUpGameSearchTextCache;
        }

        $segments = [
            $this->name,
            $this->slug,
            $this->description,
            $this->provider_product_id,
        ];

        $category = $this->category;

        while ($category) {
            $segments[] = $category->name;
            $segments[] = $category->slug;
            $category->loadMissing('parent');
            $category = $category->parent;
        }

        $text = Str::lower(implode(' ', array_filter($segments)));
        $text = preg_replace('/[^a-z0-9#]+/i', ' ', $text) ?: '';

        $this->topUpGameSearchTextCache = ' ' . trim($text) . ' ';

        return $this->topUpGameSearchTextCache;
    }

    protected function containsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, ' ' . trim(Str::lower($keyword)) . ' ')) {
                return true;
            }
        }

        return false;
    }

    public function getResolvedTargetLabelAttribute(): string
    {
        if (!empty($this->target_label)) {
            return $this->target_label;
        }

        $primaryField = $this->checkout_fields[0] ?? null;

        if ($primaryField !== null) {
            return $primaryField['label'];
        }

        return match ($this->process_type) {
            'api' => $this->provider_source === 'digiflazz'
                ? 'Data tujuan (user ID, zone ID, atau nomor tujuan)'
                : 'Target pesanan (username atau link profile)',
            default => 'Target pesanan',
        };
    }

    public function getResolvedTargetPlaceholderAttribute(): string
    {
        if (!empty($this->target_placeholder)) {
            return $this->target_placeholder;
        }

        $primaryField = $this->checkout_fields[0] ?? null;

        if ($primaryField !== null) {
            return $primaryField['placeholder'];
        }

        return match ($this->process_type) {
            'api' => $this->provider_source === 'digiflazz'
                ? 'Contoh: 12345678 (1234) atau 081234567890'
                : 'Contoh: https://instagram.com/username',
            default => 'Masukkan data tujuan pesanan',
        };
    }

    public function getResolvedTargetHintAttribute(): ?string
    {
        if (!empty($this->target_hint)) {
            return $this->target_hint;
        }

        $primaryField = $this->checkout_fields[0] ?? null;

        if ($primaryField !== null) {
            return $primaryField['hint'];
        }

        return match ($this->process_type) {
            'api' => $this->provider_source === 'digiflazz'
                ? 'Pastikan ID, zone, atau nomor tujuan sudah benar agar pesanan tidak gagal.'
                : 'Gunakan username atau link yang aktif dan bisa diakses publik.',
            default => null,
        };
    }

    public function getIsOrdersosmedServiceAttribute(): bool
    {
        return ($this->provider_source ?: $this->provider_id) === 'ordersosmed';
    }

    public function getUsesApiSellingMarkupAttribute(): bool
    {
        return $this->api_selling_markup_rate > 0;
    }

    public function getApiSellingMarkupRateAttribute(): float
    {
        if ($this->apiSellingMarkupRateLoaded) {
            return $this->apiSellingMarkupRateCache;
        }

        $provider = Str::lower((string) ($this->provider_source ?: $this->provider_id));
        $isKnownApiProvider = in_array($provider, static::API_PROVIDER_SOURCES, true);

        if ($this->process_type !== 'api' && ! $isKnownApiProvider) {
            $this->apiSellingMarkupRateLoaded = true;
            $this->apiSellingMarkupRateCache = 0.0;

            return $this->apiSellingMarkupRateCache;
        }

        $this->apiSellingMarkupRateLoaded = true;
        $this->apiSellingMarkupRateCache = static::apiSellingMarkupRateFor(
            $this->resolvePrimaryCategorySlug(),
            $provider,
            $this->resolveApiMarkupCategoryText()
        );

        return $this->apiSellingMarkupRateCache;
    }

    public static function apiSellingMarkupRateFor(?string $topCategorySlug, ?string $provider = null, ?string $categoryText = null): float
    {
        $slug = Str::slug((string) $topCategorySlug);

        if ($slug !== '' && isset(static::API_CATEGORY_MARKUP_RATES[$slug])) {
            return static::API_CATEGORY_MARKUP_RATES[$slug];
        }

        $text = Str::lower(trim(str_replace(['-', '_'], ' ', implode(' ', array_filter([
            $slug,
            $categoryText,
        ])))));

        if ($text !== '') {
            if (str_contains($text, 'suntik sosmed')) {
                return 0.50;
            }

            if (str_contains($text, 'top up game')) {
                return 0.05;
            }

            if (str_contains($text, 'paket data') || str_contains($text, 'kuota murah')) {
                return 0.10;
            }
        }

        $provider = Str::lower((string) $provider);

        return static::API_SELLING_PRICE_MARKUP_RATES[$provider] ?? 0.0;
    }

    protected function resolveApiMarkupCategoryText(): string
    {
        $segments = [
            $this->resolvePrimaryCategoryName(),
            $this->name,
            $this->slug,
        ];

        if (! $this->relationLoaded('category')) {
            $this->loadMissing('category.parent.parent.parent');
        } else {
            $this->category?->loadMissing('parent.parent.parent');
        }

        $category = $this->category;

        while ($category) {
            $segments[] = $category->name;
            $segments[] = $category->slug;
            $category = $category->parent;
        }

        return implode(' ', array_filter($segments));
    }

    public function getSellingPriceAttribute(): float
    {
        return $this->applyActiveDiscount($this->original_selling_price);
    }

    public function getMembershipSellingPriceAttribute(): float
    {
        return $this->applyActiveDiscount($this->original_membership_selling_price);
    }

    public function getOriginalSellingPriceAttribute(): float
    {
        if ($this->shouldUseMembershipPrice()) {
            return $this->calculateMembershipSellingPrice((float) $this->price);
        }

        return $this->calculateSellingPrice((float) $this->price);
    }

    public function getOriginalMembershipSellingPriceAttribute(): float
    {
        return $this->calculateMembershipSellingPrice((float) $this->price);
    }

    public function getHasActiveDiscountAttribute(): bool
    {
        return $this->active_discount_rate > 0;
    }

    public function getActiveDiscountRateAttribute(): float
    {
        // Diskon per-produk (0-100%) atau diskon global (mis. Jumat), ambil yang terbesar.
        $productRate = max(0, min(100, (int) ($this->discount_percent ?? 0))) / 100;

        return max($productRate, static::currentDiscountRate());
    }

    public function getActiveDiscountLabelAttribute(): ?string
    {
        $rate = $this->active_discount_rate;

        return $rate > 0
            ? 'Diskon ' . rtrim(rtrim(number_format($rate * 100, 2, ',', '.'), '0'), ',') . '%'
            : null;
    }

    public static function isAgustusanPromoActive(): bool
    {
        // Promo HUT RI ke-81: 17-20 Agustus 2026 (WIB).
        return now()->between(
            \Illuminate\Support\Carbon::create(2026, 8, 17, 0, 0, 0, config('app.timezone')),
            \Illuminate\Support\Carbon::create(2026, 8, 20, 23, 59, 59, config('app.timezone'))
        );
    }

    public static function currentDiscountRate(): float
    {
        // Promo HUT RI ke-81: diskon 19% untuk SEMUA produk di semua kategori.
        if (static::isAgustusanPromoActive()) {
            return 0.19;
        }

        return now()->isFriday() ? static::FRIDAY_BLESSING_DISCOUNT_RATE : 0.0;
    }

    public static function currentDiscountLabel(): ?string
    {
        $rate = static::currentDiscountRate();

        return $rate > 0
            ? 'Diskon ' . rtrim(rtrim(number_format($rate * 100, 2, ',', '.'), '0'), ',') . '%'
            : null;
    }

    public function getDisplayLogoImageAttribute(): ?string
    {
        if ($this->usesCategoryLogo()) {
            return $this->category?->image ?: $this->image;
        }

        return $this->image ?: $this->category?->image;
    }

    public function getDisplayLogoEmoteAttribute(): ?string
    {
        if ($this->usesCategoryLogo()) {
            return $this->category?->emote ?: $this->emote;
        }

        return $this->emote ?: $this->category?->emote;
    }

    protected function usesCategoryLogo(): bool
    {
        return in_array($this->resolvePrimaryCategorySlug(), [
            'kuota-murah',
            'paket-data',
            'aplikasi-premium',
        ], true);
    }

    public function cachePrimaryCategory(?string $slug, ?string $name = null): self
    {
        $this->resolvedPrimaryCategorySlugLoaded = true;
        $this->resolvedPrimaryCategorySlugCache = $slug ? Str::slug($slug) : null;
        $this->resolvedPrimaryCategoryNameLoaded = true;
        $this->resolvedPrimaryCategoryNameCache = $name;
        $this->apiSellingMarkupRateLoaded = false;
        $this->checkoutFieldsLoaded = false;
        $this->topUpGameKeyLoaded = false;
        $this->topUpGameSearchTextCache = null;

        return $this;
    }

    public function getOrdersosmedMinQuantityAttribute(): int
    {
        $descriptionMin = $this->extractDescriptionInteger('/Minimum order:\s*([\d.,]+)/i');

        return max(1, $descriptionMin ?: (int) ($this->provider_quantity ?: 1));
    }

    public function getOrdersosmedMaxQuantityAttribute(): int
    {
        $descriptionMax = $this->extractDescriptionInteger('/Maximum order:\s*([\d.,]+)/i');

        return max($this->ordersosmed_min_quantity, $descriptionMax ?: 1000000);
    }

    public function getOrdersosmedAverageTimeAttribute(): string
    {
        $averageSeconds = $this->resolveOrdersosmedSuccessfulAverageSeconds();

        if ($averageSeconds === null) {
            return 'Belum ada order sukses';
        }

        $successCount = $this->resolveOrdersosmedSuccessfulOrderCount();
        $suffix = $successCount > 0 ? ' dari ' . number_format($successCount, 0, ',', '.') . ' order sukses' : '';

        return $this->formatOrdersosmedDuration($averageSeconds) . $suffix;
    }

    public function getOrdersosmedUnitPriceAttribute(): float
    {
        return $this->selling_price / $this->ordersosmed_pricing_quantity;
    }

    public function getOriginalOrdersosmedUnitPriceAttribute(): float
    {
        return $this->original_selling_price / $this->ordersosmed_pricing_quantity;
    }

    public function getOrdersosmedPricingQuantityAttribute(): int
    {
        return max(1, (int) ($this->provider_quantity ?: $this->ordersosmed_min_quantity));
    }

    public function getOrdersosmedPricePerThousandAttribute(): float
    {
        return $this->ordersosmed_unit_price * 1000;
    }

    public function getOriginalOrdersosmedPricePerThousandAttribute(): float
    {
        return $this->original_ordersosmed_unit_price * 1000;
    }

    public function calculateCheckoutAmount(array $validated): float
    {
        if ($this->is_ordersosmed_service && filled($validated['order_quantity'] ?? null)) {
            $quantity = max(1, (int) $validated['order_quantity']);

            return (float) max(1, ceil($quantity * $this->ordersosmed_unit_price));
        }

        return $this->selling_price;
    }

    protected function calculateSellingPrice(float $basePrice): float
    {
        if (! $this->uses_api_selling_markup) {
            return $basePrice;
        }

        return (float) ceil(round($basePrice * (1 + $this->api_selling_markup_rate), 6));
    }

    protected function shouldUseMembershipPrice(): bool
    {
        return auth()->check() && auth()->user()?->isMember();
    }

    protected function calculateMembershipSellingPrice(float $basePrice): float
    {
        if ($this->resolvePrimaryCategorySlug() === 'aplikasi-premium' && filled($this->membership_price)) {
            return (float) $this->membership_price;
        }

        if (! $this->uses_api_selling_markup) {
            return $this->calculateSellingPrice($basePrice);
        }

        $rate = static::memberMarkupRateFor(
            $this->resolvePrimaryCategorySlug(),
            $this->resolveApiMarkupCategoryText()
        );

        if ($rate <= 0) {
            return $this->calculateSellingPrice($basePrice);
        }

        return (float) ceil(round($basePrice * (1 + $rate), 6));
    }

    public static function memberMarkupRateFor(?string $topCategorySlug, ?string $categoryText = null): float
    {
        $slug = Str::slug((string) $topCategorySlug);

        if ($slug !== '' && isset(static::MEMBER_API_CATEGORY_MARKUP_RATES[$slug])) {
            return static::MEMBER_API_CATEGORY_MARKUP_RATES[$slug];
        }

        $text = Str::lower(trim(str_replace(['-', '_'], ' ', implode(' ', array_filter([
            $slug,
            $categoryText,
        ])))));

        if ($text !== '') {
            if (str_contains($text, 'suntik sosmed')) {
                return 0.35;
            }

            if (str_contains($text, 'top up game')) {
                return 0.04;
            }

            if (str_contains($text, 'paket data') || str_contains($text, 'kuota murah')) {
                return 0.07;
            }
        }

        return 0.0;
    }

    protected function applyActiveDiscount(float $price): float
    {
        if (! $this->has_active_discount) {
            return $price;
        }

        return (float) max(0, ceil(round($price * (1 - $this->active_discount_rate), 6)));
    }

    protected function extractDescriptionInteger(string $pattern): ?int
    {
        if (preg_match($pattern, (string) $this->description, $matches) !== 1) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $matches[1]);

        return filled($digits) ? (int) $digits : null;
    }

    protected function resolveOrdersosmedSuccessfulAverageSeconds(): ?int
    {
        if (array_key_exists('ordersosmed_success_average_seconds_cache', $this->attributes)) {
            $seconds = $this->attributes['ordersosmed_success_average_seconds_cache'];

            return is_numeric($seconds) ? max(0, (int) round((float) $seconds)) : null;
        }

        $durations = $this->successfulOrderDurationsInSeconds();

        if ($durations === []) {
            return null;
        }

        return (int) round(array_sum($durations) / count($durations));
    }

    protected function resolveOrdersosmedSuccessfulOrderCount(): int
    {
        if (array_key_exists('ordersosmed_success_order_count_cache', $this->attributes)) {
            return max(0, (int) $this->attributes['ordersosmed_success_order_count_cache']);
        }

        return count($this->successfulOrderDurationsInSeconds());
    }

    protected function successfulOrderDurationsInSeconds(): array
    {
        return $this->transactions()
            ->where('order_status', 'success')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->get(['created_at', 'updated_at'])
            ->map(function (Transaction $transaction) {
                if (! $transaction->created_at || ! $transaction->updated_at || $transaction->updated_at->lessThan($transaction->created_at)) {
                    return null;
                }

                return $transaction->created_at->diffInSeconds($transaction->updated_at);
            })
            ->filter(fn ($seconds) => $seconds !== null)
            ->values()
            ->all();
    }

    protected function formatOrdersosmedDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return 'kurang dari 1 menit';
        }

        $days = intdiv($seconds, 86400);
        $seconds %= 86400;
        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;
        $minutes = intdiv($seconds, 60);

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . ' hari';
        }

        if ($hours > 0) {
            $parts[] = $hours . ' jam';
        }

        if ($minutes > 0) {
            $parts[] = $minutes . ' menit';
        }

        return implode(' ', array_slice($parts, 0, 2));
    }

    /**
     * Boot function untuk generate slug otomatis jika belum ada
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(5);
            }
        });
    }

    public function buildOrderInputData(array $validated): ?array
    {
        $fields = collect($this->checkout_fields)
            ->map(function (array $field) use ($validated) {
                $value = trim((string) ($validated[$field['name']] ?? ''));

                if ($value === '') {
                    return null;
                }

                return [
                    'name' => $field['name'],
                    'label' => $field['label'],
                    'value' => $value,
                    'target_summary' => (bool) ($field['target_summary'] ?? false),
                    'provider_customer_no' => (bool) ($field['provider_customer_no'] ?? false),
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($fields === []) {
            return null;
        }

        return ['fields' => $fields];
    }

    public function summarizeOrderInput(array $validated): string
    {
        if ($this->resolvePrimaryCategorySlug() === 'top-up-game') {
            $fields = data_get($this->buildOrderInputData($validated), 'fields', []);
            $summaryFields = collect($fields)
                ->filter(fn ($field) => (bool) ($field['target_summary'] ?? false))
                ->values();

            if ($summaryFields->count() === 2) {
                return $summaryFields[0]['value'] . ' (' . $summaryFields[1]['value'] . ')';
            }

            if ($summaryFields->count() === 1) {
                return (string) $summaryFields[0]['value'];
            }

            if ($fields !== []) {
                return collect($fields)
                    ->map(fn ($field) => ($field['label'] ?? 'Input') . ': ' . ($field['value'] ?? '-'))
                    ->implode(' / ');
            }
        }

        $fields = data_get($this->buildOrderInputData($validated), 'fields', []);

        if ($fields === []) {
            return '- (Tidak membutuhkan target tambahan)';
        }

        $primaryField = collect($fields)->firstWhere('target_summary', true) ?? $fields[0];

        return trim((string) ($primaryField['value'] ?? '-'));
    }
}
