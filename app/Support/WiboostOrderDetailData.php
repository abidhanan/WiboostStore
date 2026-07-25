<?php

namespace App\Support;

use App\Models\Transaction;
use Illuminate\Support\Str;

class WiboostOrderDetailData
{
    public static function forTransaction(Transaction $transaction): array
    {
        $product = $transaction->product;
        $rootCategory = self::resolveRootCategory($product?->category);
        $inputLines = self::inputBuyerLines($transaction);

        return [
            'invoice' => $transaction->invoice_number ?: '-',
            'category' => $transaction->isMembershipPayment() ? 'Membership' : ($rootCategory?->name ?: '-'),
            'category_key' => $transaction->isMembershipPayment() ? 'membership' : Str::slug((string) ($rootCategory?->slug ?: $rootCategory?->name)),
            'product' => $transaction->display_product_name,
            'total' => 'Rp ' . number_format((int) $transaction->display_total_payable, 0, ',', '.'),
            'payment' => strtoupper((string) $transaction->display_payment_method),
            'status' => strtoupper(self::statusLabel($transaction)),
            'provider_id' => $transaction->provider_order_id ?: '-',
            'time' => self::formatReportDate($transaction->created_at),
            'input_lines' => $inputLines,
            'input_text' => implode("\n", $inputLines),
        ];
    }

    public static function statusLabel(Transaction $transaction): string
    {
        if ($transaction->hasManualRefund()) {
            return 'Refund';
        }

        if ($transaction->cancelled_due_to_qris_expiry) {
            return 'Batal';
        }

        if (in_array($transaction->payment_status, ['unpaid', 'pending'], true)) {
            return 'Menunggu Bayar';
        }

        if ($transaction->payment_status === 'failed' || $transaction->order_status === 'failed') {
            return 'Gagal';
        }

        return match ($transaction->order_status) {
            'success' => 'Sukses',
            'processing' => 'Proses',
            default => 'Pending',
        };
    }

    public static function inputBuyerLines(Transaction $transaction): array
    {
        return collect($transaction->order_input_fields)
            ->map(function ($field) {
                $label = trim((string) ($field['label'] ?? 'Input'));
                $value = trim((string) ($field['value'] ?? '-'));

                return $label . ': ' . ($value !== '' ? $value : '-');
            })
            ->filter(fn ($line) => trim((string) $line) !== '')
            ->values()
            ->all();
    }

    public static function formatReportDate($date): string
    {
        if (! $date) {
            return '-';
        }

        $months = [
            1 => 'JAN',
            2 => 'FEB',
            3 => 'MAR',
            4 => 'APR',
            5 => 'MEI',
            6 => 'JUN',
            7 => 'JUL',
            8 => 'AGU',
            9 => 'SEP',
            10 => 'OKT',
            11 => 'NOV',
            12 => 'DES',
        ];

        return $date->format('d') . ' '
            . ($months[(int) $date->format('n')] ?? Str::upper($date->format('M'))) . ' '
            . $date->format('Y, H:i');
    }

    protected static function resolveRootCategory($category)
    {
        while ($category?->parent) {
            $category = $category->parent;
        }

        return $category;
    }
}
