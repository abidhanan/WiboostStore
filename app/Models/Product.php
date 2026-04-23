<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Support\WiboostCatalog;

class Product extends Model
{
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

    // Relasi ke stok (untuk produk manual/fisik)
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
     * Accessor untuk menghitung sisa stok otomatis (khusus tipe account/number)
     */
    public function getAvailableStockAttribute()
    {
        if (!in_array($this->process_type, ['account', 'number'])) return null;
        
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
            'manual' => [[
                'name' => 'target_data',
                'label' => 'Informasi pesanan / kontak yang bisa dihubungi',
                'type' => 'text',
                'placeholder' => 'Contoh: username, link akun, nomor kontak, atau catatan pesanan',
                'hint' => 'Masukkan data yang paling memudahkan admin memproses pesanan kamu.',
                'rules' => ['required', 'string', 'min:3', 'max:255'],
                'target_summary' => true,
            ]],
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
            'manual' => 'Informasi pesanan / kontak yang bisa dihubungi',
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
            'manual' => 'Contoh: username, link akun, nomor kontak, atau catatan pesanan',
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
            'manual' => 'Masukkan data yang paling memudahkan admin memproses pesanan kamu.',
            'api' => $this->provider_source === 'digiflazz'
                ? 'Pastikan ID, zone, atau nomor tujuan sudah benar agar pesanan tidak gagal.'
                : 'Gunakan username atau link yang aktif dan bisa diakses publik.',
            default => null,
        };
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
        $fields = data_get($this->buildOrderInputData($validated), 'fields', []);

        if ($fields === []) {
            return '- (Tidak membutuhkan target tambahan)';
        }

        $primaryField = collect($fields)->firstWhere('target_summary', true) ?? $fields[0];

        return trim((string) ($primaryField['value'] ?? '-'));
    }
}
