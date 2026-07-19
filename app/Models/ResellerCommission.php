<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerCommission extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'type',
        'category_slug',
        'category_name',
        'product_name',
        'rate',
        'base_amount',
        'amount',
        'status',
        'payout_method',
        'payout_account',
        'notes',
        'metadata',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'base_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getDisplayTypeAttribute(): string
    {
        return $this->type === 'withdrawal' ? 'Komisi Keluar' : 'Komisi Masuk';
    }

    public function getDisplayStatusAttribute(): string
    {
        return match ($this->status) {
            'processing' => 'Proses',
            'success', 'approved' => 'Sukses',
            'failed', 'rejected' => 'Gagal',
            'pending' => 'Menunggu',
            default => 'Sukses',
        };
    }
}
