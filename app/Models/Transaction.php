<?php

namespace App\Models;

use App\Support\TripayPaymentAmounts;
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
        'credential_data', // Untuk menyimpan data akun premium yang dikirim otomatis.
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
        $providerFields = $fields
            ->filter(fn ($field) => (bool) ($field['provider_customer_no'] ?? false))
            ->pluck('value')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values();

        if ($providerFields->isNotEmpty()) {
            return $providerFields->implode('');
        }

        return (string) $this->target_data;
    }

    public function getTripayPaymentAmountsAttribute(): array
    {
        return TripayPaymentAmounts::fromPayload(
            data_get($this->response_data, 'tripay_create_response'),
            $this->amount
        );
    }

    public function getTripayAdminFeeAttribute(): int
    {
        return (int) ($this->tripay_payment_amounts['admin_fee'] ?? 0);
    }

    public function getTripayTotalPayableAttribute(): int
    {
        return (int) ($this->tripay_payment_amounts['total'] ?? round((float) $this->amount));
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
