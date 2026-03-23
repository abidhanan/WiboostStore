<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_number', 
        'user_id', 
        'product_id', 
        'amount', 
        'target_data', 
        'target_notes', 
        'response_data', 
        'payment_status', 
        'order_status',
        'payment_method',
        'snap_token',
        'credential_data', // <-- TAMBAHAN VITAL: Untuk menyimpan data akun/nomor luar ✨
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}