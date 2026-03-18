<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'badge_text',
        'title',
        'description',
        'emoji',
        'theme',
        'image',
        'link', // <-- Menambahkan field link
        'is_active'
    ];
}