<?php

namespace App\Models;

use App\Support\TripayPaymentAmounts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'amount',
        'paid_amount',
        'payment_status',
        'payment_method',
        'payment_url',
        'tripay_reference',
        'payment_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'payment_response' => 'array',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusAttribute(): string
    {
        return (string) $this->payment_status;
    }

    public function getTripayPaymentAmountsAttribute(): array
    {
        return TripayPaymentAmounts::fromPayload($this->payment_response, $this->amount);
    }

    public function getTripayAdminFeeAttribute(): int
    {
        return (int) ($this->tripay_payment_amounts['admin_fee'] ?? 0);
    }

    public function getTripayTotalPayableAttribute(): int
    {
        return (int) ($this->tripay_payment_amounts['total'] ?? round((float) $this->amount));
    }
}
