<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletHistory extends Model
{
    protected $fillable = [
        'user_id', 
        'type', 
        'amount', 
        'description', 
        'invoice_number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        $displayType = str_contains((string) ($this->invoice_number ?? ''), 'POIN')
            ? 'poin'
            : (string) ($this->type ?? '');

        return match ($displayType) {
            'refund' => 'Refund',
            'topup' => 'Top Up',
            'purchase' => 'Belanja',
            'poin' => 'Poin',
            default => $displayType !== ''
                ? strtoupper(str_replace('_', ' ', $displayType))
                : '-',
        };
    }
}
