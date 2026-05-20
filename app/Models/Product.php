<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Support\WiboostCatalog;

class Product extends Model
{
    protected const API_SELLING_PRICE_MARKUP_RATES = [
        'ordersosmed' => 0.50,
        'digiflazz' => 0.05,
    ];

    protected const DIGIFLAZZ_CATEGORY_MARKUP_RATES = [
        'top-up-game' => 0.05,
        'kuota-murah' => 0.10,
        'paket-data' => 0.10,
    ];

    protected $fillable = [
        'category_id',
        'provider_id',
        'process_type',
        'name',
        'slug',
        'description',
        'price',
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
        $topCategorySlug = $this->resolvePrimaryCategorySlug();

        if ($topCategorySlug === 'aplikasi-premium' && ! $this->requires_buyer_email) {
            return [];
        }

        if ($topCategorySlug === 'top-up-game') {
            return $this->checkoutFieldsForTopUpGameProduct();
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
            return $checkoutFields;
        }

        if (! empty($this->target_label) || ! empty($this->target_placeholder) || ! empty($this->target_hint)) {
            return [[
                'name' => 'target_data',
                'label' => $this->target_label ?: 'Target pesanan',
                'type' => 'text',
                'placeholder' => $this->target_placeholder ?: 'Masukkan data tujuan pesanan',
                'hint' => $this->target_hint,
                'rules' => ['required', 'string', 'min:3', 'max:255'],
                'target_summary' => true,
            ]];
        }

        return match ($this->process_type) {
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
        };
    }

    protected function resolvePrimaryCategorySlug(): ?string
    {
        if (! $this->relationLoaded('category')) {
            $this->loadMissing('category.parent.parent');
        } else {
            $this->category?->loadMissing('parent.parent');
        }

        $category = $this->category;

        if (! $category) {
            return null;
        }

        while ($category->parent) {
            $category = $category->parent;
        }

        return $category->slug;
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
        $text = $this->normalizedTopUpGameSearchText();

        return match (true) {
            $this->containsAny($text, ['mobile legends', 'mobile legend', 'mobilelegend', 'mlbb', 'game mobile legends']) => 'mobile-legends',
            $this->containsAny($text, ['free fire max', 'freefire max', 'ff max', 'game free fire max']) => 'free-fire-max',
            $this->containsAny($text, ['free fire', 'freefire', 'ff', 'game free fire']) => 'free-fire',
            $this->containsAny($text, ['pubg', 'pubg mobile', 'uc pubg', 'uc mobile']) => 'pubg-mobile',
            $this->containsAny($text, ['valorant', 'valorant points', 'valorant point', 'vp']) => 'valorant',
            $this->containsAny($text, ['arena of valor', ' aov ', 'game arena of valor']) => 'arena-of-valor',
            $this->containsAny($text, ['call of duty mobile', 'call of duty', 'cod mobile', 'codm', 'cp cod']) => 'cod-mobile',
            $this->containsAny($text, ['fc mobile', 'fifa mobile', 'fifa points', 'eafc mobile', 'ea fc mobile', 'fc points', 'fc point']) => 'fc-mobile',
            $this->containsAny($text, ['genshin impact', 'genshin', 'genesis crystal', 'genesis crystals', 'welkin']) => 'genshin-impact',
            $this->containsAny($text, ['honor of kings', 'hok']) => 'honor-of-kings',
            $this->containsAny($text, ['wild rift', 'wildrift', 'wild core', 'wild cores', 'lol wild rift', 'lol wr']) => 'lol-wild-rift',
            $this->containsAny($text, ['league of legends pc', 'league of legend pc', 'lol pc', 'league pc', 'riot points', 'riot point', 'riot cash']) => 'lol-pc',
            default => null,
        };
    }

    protected function normalizedTopUpGameSearchText(): string
    {
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

        return ' ' . trim($text) . ' ';
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
        if ($this->process_type !== 'api') {
            return 0.0;
        }

        $provider = $this->provider_source ?: $this->provider_id;

        if ($provider === 'digiflazz') {
            $topCategorySlug = $this->resolvePrimaryCategorySlug();

            return static::DIGIFLAZZ_CATEGORY_MARKUP_RATES[$topCategorySlug] ?? static::API_SELLING_PRICE_MARKUP_RATES[$provider];
        }

        return static::API_SELLING_PRICE_MARKUP_RATES[$provider] ?? 0.0;
    }

    public function getSellingPriceAttribute(): float
    {
        return $this->calculateSellingPrice((float) $this->price);
    }

    public function getDisplayLogoImageAttribute(): ?string
    {
        if ($this->usesKuotaCategoryLogo()) {
            return $this->category?->image;
        }

        return $this->image;
    }

    public function getDisplayLogoEmoteAttribute(): ?string
    {
        if ($this->usesKuotaCategoryLogo()) {
            return $this->category?->emote ?: $this->emote;
        }

        return $this->emote;
    }

    protected function usesKuotaCategoryLogo(): bool
    {
        return $this->resolvePrimaryCategorySlug() === 'kuota-murah';
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
        return $this->selling_price / max(1, $this->ordersosmed_min_quantity);
    }

    public function getOrdersosmedPricePerThousandAttribute(): float
    {
        return $this->ordersosmed_unit_price * 1000;
    }

    public function calculateCheckoutAmount(array $validated): float
    {
        if ($this->is_ordersosmed_service && filled($validated['order_quantity'] ?? null)) {
            $quantity = max(1, (int) $validated['order_quantity']);

            return (float) (ceil(($quantity * $this->ordersosmed_unit_price) / 100) * 100);
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
