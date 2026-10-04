<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'created_by_user_id',
        'data_1',
        'data_2',
        'data_3',
        'data_4',
        'data_5',
        'notes',
        'tutorial_link',
        'needs_otp',
        'otp_admin_whatsapp',
        'otp_admin_name',
        'requires_login_proof',
        'requires_logout_proof',
        'max_usage',
        'duration_value',
        'duration_unit',
        'current_usage',
        'is_active',
    ];

    protected $casts = [
        'needs_otp' => 'boolean',
        'requires_login_proof' => 'boolean',
        'requires_logout_proof' => 'boolean',
        'duration_value' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
