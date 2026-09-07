<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = [
        'transaction_id',
        'admin_id',
        'proof_path',
        'notes',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function metadata(): array
    {
        $metadata = json_decode((string) $this->notes, true);

        return is_array($metadata) ? $metadata : [];
    }

    public function getManualRefundPercentAttribute(): int
    {
        $percent = (int) ($this->metadata()['manual_refund_percent'] ?? 100);

        return in_array($percent, [25, 50, 75, 100], true) ? $percent : 100;
    }

    public function getManualRefundAmountAttribute(): int
    {
        $metadataAmount = $this->metadata()['manual_refund_amount'] ?? null;

        if (is_numeric($metadataAmount)) {
            return max(0, (int) round((float) $metadataAmount));
        }

        $transactionTotal = (float) ($this->transaction?->display_total_payable ?? 0);

        return max(0, (int) round($transactionTotal * $this->manual_refund_percent / 100));
    }
}
