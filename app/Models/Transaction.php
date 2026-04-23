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
        'order_input_data',
        'target_notes', 
        'response_data', 
        'payment_status', 
        'order_status',
        'payment_method',
        'snap_token',
        'credential_data', // <-- TAMBAHAN VITAL: Untuk menyimpan data akun/nomor luar ✨
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'order_input_data' => 'array',
        'response_data' => 'array',
        'credential_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getOrderInputFieldsAttribute(): array
    {
        $fields = data_get($this->order_input_data, 'fields');

        if (is_array($fields) && $fields !== []) {
            return collect($fields)
                ->filter(fn ($field) => filled($field['value'] ?? null))
                ->values()
                ->all();
        }

        if (filled($this->target_data) && $this->target_data !== '- (Tidak membutuhkan target tambahan)') {
            return [[
                'name' => 'target_data',
                'label' => 'Target / Tujuan',
                'value' => (string) $this->target_data,
                'target_summary' => true,
            ]];
        }

        return [];
    }

    public function getHasOrderInputAttribute(): bool
    {
        return $this->order_input_fields !== [];
    }

    public function getOrderInputSummaryAttribute(): ?string
    {
        $fields = $this->order_input_fields;

        if ($fields === []) {
            return null;
        }

        $primaryField = collect($fields)->firstWhere('target_summary', true) ?? $fields[0];

        return $primaryField['value'] ?? null;
    }

    public function getOrderInputTextAttribute(): ?string
    {
        $fields = $this->order_input_fields;

        if ($fields === []) {
            return null;
        }

        return collect($fields)
            ->map(fn ($field) => ($field['label'] ?? 'Input') . ': ' . ($field['value'] ?? '-'))
            ->implode("\n");
    }
}
