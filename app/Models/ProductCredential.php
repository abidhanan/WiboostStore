<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'data_1', 'data_2', 'data_3', 'data_4', 'data_5', 'max_usage', 'current_usage', 'is_active'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}