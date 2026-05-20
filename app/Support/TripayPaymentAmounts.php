<?php

namespace App\Support;

class TripayPaymentAmounts
{
    public static function fromPayload(?array $payload, int|float|string $baseAmount): array
    {
        $base = max(0, (int) round((float) $baseAmount));
        $data = self::paymentData($payload);

        $explicitFee = self::firstNumeric($data, [
            'fee_customer',
            'customer_fee',
            'admin_fee',
            'fee_admin',
            'payment_fee',
            'total_fee',
            'fee',
        ]);

        $totalCandidate = self::firstNumeric($data, [
            'total_amount',
            'amount_total',
            'pay_amount',
            'amount_pay',
            'checkout_amount',
            'grand_total',
            'amount',
        ]);

        $fee = $explicitFee;

        if ($fee === null) {
            $fee = $totalCandidate !== null && $totalCandidate > $base
                ? $totalCandidate - $base
                : 0;
        }

        $fee = max(0, (int) round($fee));
        $total = $totalCandidate !== null
            ? max((int) round($totalCandidate), $base + $fee)
            : $base + $fee;

        return [
            'base' => $base,
            'admin_fee' => $fee,
            'total' => $total,
        ];
    }

    protected static function paymentData(?array $payload): array
    {
        if (! is_array($payload) || $payload === []) {
            return [];
        }

        $data = data_get($payload, 'data');

        return is_array($data) ? $data : $payload;
    }

    protected static function firstNumeric(array $data, array $keys): ?float
    {
        foreach ($keys as $key) {
            $value = data_get($data, $key);

            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }
}
