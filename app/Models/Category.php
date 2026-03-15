<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Tambahkan 'parent_id' dan 'description' ke dalam array ini
    protected $fillable = ['parent_id', 'name', 'slug', 'description', 'image'];

    // Relasi ke Kategori Induk (Parent) - Contoh: Kategori "Instagram" parent-nya "Suntik Sosmed"
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Relasi ke Sub-Kategori (Anak-anaknya) - Contoh: Kategori "Suntik Sosmed" punya anak "Instagram", "Tiktok"
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Relasi ke Produk
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}