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

    public function getProviderOrderQuantityAttribute(): ?int
    {
        $field = collect($this->order_input_fields)
            ->firstWhere('name', 'order_quantity');

        if (! $field || ! filled($field['value'] ?? null)) {
            return null;
        }

        return max(1, (int) $field['value']);
    }

    public function getProviderCustomerNoAttribute(): string
    {
        $fields = collect($this->order_input_fields);
        $gameUserId = trim((string) ($fields->firstWhere('name', 'game_user_id')['value'] ?? ''));
        $gameZoneId = trim((string) ($fields->firstWhere('name', 'game_zone_id')['value'] ?? ''));

        if ($gameUserId !== '' && $gameZoneId !== '') {
            return $gameUserId . $gameZoneId;
        }

        return (string) $this->target_data;
    }

    public function getProviderOrderIdAttribute(): ?string
    {
        $orderId = data_get($this->response_data, 'provider_order_id')
            ?? data_get($this->response_data, 'data.id')
            ?? data_get($this->response_data, 'data.order_id')
            ?? data_get($this->response_data, 'data.order')
            ?? data_get($this->response_data, 'order_id')
            ?? data_get($this->response_data, 'order')
            ?? data_get($this->response_data, 'id');

        return filled($orderId) ? (string) $orderId : null;
    }
}
