<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    // Menambahkan 'image'
    protected $fillable = [
        'badge_text',
        'title',
        'description',
        'emoji',
        'theme',
        'image',
        'is_active'
    ];
}