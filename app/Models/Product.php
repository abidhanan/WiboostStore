<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'stock_reminder',
        'image',
        'emote',
        'is_active',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
        return in_array($this->process_type, ['api', 'manual'], true);
    }

    public function getResolvedTargetLabelAttribute(): string
    {
        if (!empty($this->target_label)) {
            return $this->target_label;
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

        return match ($this->process_type) {
            'manual' => 'Contoh: username, link akun, nomor WhatsApp, atau catatan pesanan',
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
}
