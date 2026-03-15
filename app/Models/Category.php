<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Tambahkan 'image' ke dalam array ini
    protected $fillable = ['name', 'slug', 'image'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}