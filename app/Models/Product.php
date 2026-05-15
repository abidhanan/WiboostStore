<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Support\WiboostCatalog;

class Product extends Model
{
    protected const API_SELLING_PRICE_MARKUP_RATES = [
        'ordersosmed' => 0.10,
        'digiflazz' => 0.05,
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
                    ? 'Contoh: 12345678 (1234) atau 6281234567890'
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
                ? 'Contoh: 12345678 (1234) atau 6281234567890'
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
            $userId = trim((string) ($validated['game_user_id'] ?? ''));
            $zoneId = trim((string) ($validated['game_zone_id'] ?? ''));

            if ($userId !== '' && $zoneId !== '') {
                return $userId . ' (' . $zoneId . ')';
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
