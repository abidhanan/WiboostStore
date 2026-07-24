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

    public function getDisplayPaymentMethodAttribute(): string
    {
        $method = trim((string) $this->payment_method);
        $normalizedMethod = preg_replace('/[^a-z0-9]/', '', strtolower($method));

        if (($normalizedMethod === '' || $normalizedMethod === 'manual') && $this->payment_status !== 'paid') {
            return 'BELUM PILIH';
        }

        if (in_array($normalizedMethod, ['tripay', 'qris', 'qrisrealtime'], true)) {
            return 'QRIS';
        }

        if ($normalizedMethod === '') {
            return '-';
        }

        return strtoupper(str_replace('_', ' ', $method));
    }

    public function getTripayPaymentAmountsAttribute(): array
    {
        return TripayPaymentAmounts::fromPayload(
            data_get($this->payment_response, 'xoftware_create_response')
                ?: data_get($this->payment_response, 'xoftware_detail_sync')
                ?: data_get($this->payment_response, 'tripay_detail_sync')
                ?: $this->payment_response,
            $this->amount
        );
    }

    public function getTripayAdminFeeAttribute(): int
    {
        $fee = (int) ($this->tripay_payment_amounts['admin_fee'] ?? 0);

        if ($fee <= 0 && $this->isMembershipPayment()) {
            return $this->estimatedMembershipAdminFee();
        }

        return $fee;
    }

    public function getTripayTotalPayableAttribute(): int
    {
        $base = (int) round((float) $this->amount);
        $total = (int) ($this->tripay_payment_amounts['total'] ?? $base);

        if ($this->isMembershipPayment()) {
            return max($total, $base + $this->tripay_admin_fee);
        }

        return $total;
    }

    protected function estimatedMembershipAdminFee(): int
    {
        $baseAmount = max(0, (int) round((float) $this->amount));
        $channelCode = strtoupper(trim((string) config('xoftware.default_channel', 'QRISREALTIME'))) ?: 'QRISREALTIME';
        $isRealtime = in_array($channelCode, ['QRISREALTIME', 'QRIS REALTIME', 'QRIS_REALTIME', 'QRIS-REALTIME', 'QRISRT', 'QRIS_RT'], true);
        $feePercent = max(0.0, (float) config($isRealtime ? 'xoftware.qris_realtime_fee_percent' : 'xoftware.qris_fee_percent', 0.007));
        $feeFixed = max(0, (int) config($isRealtime ? 'xoftware.qris_realtime_fee_fixed' : 'xoftware.qris_fee_fixed', $isRealtime ? 250 : 150));
        $buyerFeeShare = (float) config('xoftware.buyer_fee_share', 0.7);
        $buyerFeeShare = $buyerFeeShare > 0 ? min(1.0, $buyerFeeShare) : 0.7;
        $denominator = 1 - ($feePercent * $buyerFeeShare);

        if ($baseAmount <= 0) {
            return 0;
        }

        if ($denominator <= 0) {
            $denominator = 1;
        }

        return (int) ceil(($buyerFeeShare * (($baseAmount * $feePercent) + $feeFixed)) / $denominator);
    }

    public function isMembershipPayment(): bool
    {
        return (string) data_get($this->payment_response, 'purpose') === 'membership'
            || str_starts_with((string) $this->invoice_number, 'MEM-');
    }

    public function isPointExchange(): bool
    {
        return (string) data_get($this->payment_response, 'purpose') === 'point_exchange'
            || strtolower((string) $this->payment_method) === 'poin'
            || str_starts_with((string) $this->invoice_number, 'POIN-');
    }

    public function membershipActivated(): bool
    {
        return filled(data_get($this->payment_response, 'membership_activated_at'));
    }

    public function markMembershipActivated(): void
    {
        $this->forceFill([
            'payment_response' => array_merge($this->payment_response ?? [], [
                'purpose' => 'membership',
                'membership_activated_at' => now()->toDateTimeString(),
            ]),
        ])->save();
    }
}
