<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpRequest extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'invoice_number',
        'product_name',
        'otp_admin_name',
        'otp_admin_whatsapp',
        'status',
        'requested_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'handled' ? 'Sudah Ditangani' : 'Belum Ditangani';
    }
}
