<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'provider_product_id',
        'process_type',
        'stock_reminder',
        'image',
        'emote', // <-- Kolom baru untuk menampung emoji
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function credentials()
    {
        return $this->hasMany(ProductCredential::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getAvailableStockAttribute()
    {
        if (!in_array($this->process_type, ['account', 'number'])) return null;
        
        return $this->credentials()->where('is_active', true)->get()->sum(function($cred) {
            return $cred->max_usage - $cred->current_usage;
        });
    }
}