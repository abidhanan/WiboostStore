<?php

namespace App\Models;

use App\Mail\OrderSuccessMail;
use App\Services\ResellerCommissionService;
use App\Support\TripayPaymentAmounts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_number', 
        'user_id', 
        'product_id', 
        'amount', 
        'paid_amount',
        'target_data', 
        'order_input_data',
        'target_notes', 
        'response_data', 
        'payment_status', 
        'order_status',
        'payment_method',
        'snap_token',
        'credential_data', // Untuk menyimpan data akun premium yang dikirim otomatis.
        'login_proof_path',
        'login_proof_uploaded_at',
        'points_awarded_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'order_input_data' => 'array',
        'response_data' => 'array',
        'credential_data' => 'array',
        'login_proof_uploaded_at' => 'datetime',
        'points_awarded_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function resellerCommissions()
    {
        return $this->hasMany(ResellerCommission::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class);
    }

    public function hasManualRefund(): bool
    {
        if ($this->relationLoaded('refund')) {
            return $this->refund !== null;
        }

        return $this->refund()->exists();
    }

    protected static function booted(): void
    {
        static::saved(function (Transaction $transaction): void {
            if ($transaction->payment_status === 'paid' && $transaction->order_status === 'success') {
                app(ResellerCommissionService::class)->awardForTransaction($transaction);
            }

            if (($transaction->wasChanged('order_status') || $transaction->wasChanged('payment_status'))
                && ($transaction->order_status === 'failed' || $transaction->payment_status === 'failed')) {
                $transaction->sendFailedStatusEmailIfNeeded();
            }
        });
    }

    public function sendFailedStatusEmailIfNeeded(): void
    {
        $transaction = $this->fresh(['product', 'user']) ?? $this->loadMissing(['product', 'user']);

        if (! $transaction->user?->email || ! filter_var($transaction->user->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $responseData = $transaction->response_data ?? [];

        if (filled(data_get($responseData, 'notifications.failed_email_sent_at'))) {
            return;
        }

        try {
            Mail::to($transaction->user->email)->send(new OrderSuccessMail($transaction));

            data_set($responseData, 'notifications.failed_email_sent_at', now()->toDateTimeString());

            $transaction->forceFill([
                'response_data' => $responseData,
            ])->saveQuietly();
        } catch (\Throwable $exception) {
            Log::warning('Gagal mengirim email pesanan gagal.', [
                'transaction_id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'email' => $transaction->user->email,
                'error' => $exception->getMessage(),
            ]);
        }
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

        $legacyProviderFields = $fields
            ->filter(fn ($field) => in_array((string) ($field['name'] ?? ''), [
                'game_user_id',
                'game_zone_id',
                'phone_number',
                'customer_no',
                'subscriber_id',
                'meter_number',
            ], true))
            ->pluck('value')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values();

        if ($legacyProviderFields->isNotEmpty()) {
            return $legacyProviderFields->implode('');
        }

        return (string) $this->target_data;
    }

    public function getTripayPaymentAmountsAttribute(): array
    {
        return TripayPaymentAmounts::fromPayload(
            data_get($this->response_data, 'xoftware_create_response')
                ?: data_get($this->response_data, 'tripay_create_response'),
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

    public function usesQrisPayment(): bool
    {
        $normalizedMethod = preg_replace('/[^a-z0-9]/', '', strtolower((string) $this->payment_method));

        return in_array($normalizedMethod, [
            'tripay',
            'qris',
            'qrisrealtime',
        ], true);
    }

    public function getDisplayPaymentMethodAttribute(): string
    {
        if ($this->payment_method === 'wallet') {
            return 'SALDO';
        }

        if ($this->usesQrisPayment()) {
            return 'QRIS';
        }

        if (($this->payment_method === 'manual' || blank($this->payment_method))
            && $this->payment_status !== 'paid') {
            return 'BELUM PILIH';
        }

        return strtoupper(str_replace('_', ' ', (string) $this->payment_method));
    }

    public function isMembershipPayment(): bool
    {
        return (string) data_get($this->response_data, 'purpose') === 'membership'
            || str_starts_with((string) $this->invoice_number, 'MEM-');
    }

    public function membershipActivated(): bool
    {
        return filled(data_get($this->response_data, 'membership_activated_at'));
    }

    public function markMembershipActivated(): void
    {
        $this->forceFill([
            'response_data' => array_merge($this->response_data ?? [], [
                'purpose' => 'membership',
                'membership_activated_at' => now()->toDateTimeString(),
            ]),
        ])->save();
    }

    public function getDisplayTotalPayableAttribute(): int
    {
        if (! $this->usesQrisPayment()) {
            return (int) round((float) $this->amount);
        }

        $paidAmount = (int) round((float) $this->paid_amount);

        if ($this->payment_status === 'paid' && $paidAmount > 0) {
            return $paidAmount;
        }

        return $this->tripay_total_payable;
    }

    public function getQrisExpiresAtAttribute(): ?Carbon
    {
        if (! $this->usesQrisPayment()) {
            return null;
        }

        $raw = (array) (
            data_get($this->response_data, 'xoftware_create_response')
                ?: data_get($this->response_data, 'tripay_create_response')
                ?: []
        );

        foreach ([
            'data.expires_at',
            'data.expired_at',
            'data.expiry_at',
            'data.expiry',
            'expires_at',
            'expired_at',
            'expiry_at',
            'expiry',
        ] as $key) {
            $value = data_get($raw, $key);

            if (! is_scalar($value) || blank((string) $value)) {
                continue;
            }

            try {
                return Carbon::parse((string) $value);
            } catch (\Throwable) {
                break;
            }
        }

        return $this->created_at?->copy()
            ->addMinutes(max(1, (int) config('xoftware.expired_minutes', 60)));
    }

    public function getCancelledDueToQrisExpiryAttribute(): bool
    {
        return $this->payment_status === 'failed'
            && data_get($this->response_data, 'payment_cancel_reason') === 'expired';
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->hasManualRefund()) {
            return 'Refund';
        }

        if ($this->cancelled_due_to_qris_expiry) {
            return 'Batal';
        }

        if (in_array($this->payment_status, ['unpaid', 'pending'], true)) {
            return 'Menunggu';
        }

        return match ($this->order_status) {
            'success' => 'Sukses',
            'processing' => 'Proses',
            'failed' => 'Gagal',
            default => 'Menunggu',
        };
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
