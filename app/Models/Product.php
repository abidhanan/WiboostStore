<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'process_type',
        'name',
        'slug',
        'description',
        'price',
        'provider_product_id', // Penting untuk menyimpan SKU Digiflazz
        'stock_reminder',
        'image',
        'emote',
        'is_active',
        'status',
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