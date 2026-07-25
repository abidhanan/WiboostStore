<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DiscordWebhookService
{
    public function isEnabled(?string $channel = null): bool
    {
        if ($channel !== null) {
            return filled($this->webhookUrl($channel));
        }

        if (filled(config('services.discord.webhook_url'))) {
            return true;
        }

        return collect(config('services.discord.webhooks', []))->contains(fn ($url) => filled($url));
    }

    public function sendOrderAlert(Transaction $transaction, string $title, ?string $description = null, int $color = 5922504, string $channel = 'transactions'): void
    {
        $transaction->loadMissing('user', 'product');

        $totalPayable = $this->resolveTransactionTotalPayable($transaction);

        $fields = [
            ['name' => 'Invoice', 'value' => (string) $transaction->invoice_number, 'inline' => true],
            ['name' => 'Produk', 'value' => (string) ($transaction->display_product_name ?? '-'), 'inline' => true],
            ['name' => 'User', 'value' => (string) ($transaction->user->name ?? 'Guest'), 'inline' => true],
            ['name' => 'Nominal', 'value' => $this->formatRupiah($totalPayable), 'inline' => true],
            ['name' => 'Status', 'value' => strtoupper((string) $transaction->order_status), 'inline' => true],
            ['name' => 'Pembayaran', 'value' => strtoupper((string) ($transaction->display_payment_method ?? $transaction->payment_method ?? '-')), 'inline' => true],
        ];

        if ($transaction->has_order_input && filled($transaction->order_input_text)) {
            $fields[] = [
                'name' => 'Input Buyer',
                'value' => mb_strimwidth((string) $transaction->order_input_text, 0, 1000, '...'),
                'inline' => false,
            ];
        }

        if (filled($transaction->target_notes)) {
            $fields[] = [
                'name' => 'Catatan',
                'value' => mb_strimwidth((string) $transaction->target_notes, 0, 1000, '...'),
                'inline' => false,
            ];
        }

        $this->sendEmbed($title, $description, $fields, $color, $channel);
    }

    public function sendDepositAlert(Deposit $deposit, string $title, ?string $description = null, int $color = 4968121, string $channel = 'deposits'): void
    {
        $deposit->loadMissing('user');

        $totalPayable = $this->resolveDepositTotalPayable($deposit);

        $this->sendEmbed($title, $description, [
            ['name' => 'Invoice', 'value' => (string) $deposit->invoice_number, 'inline' => true],
            ['name' => 'User', 'value' => (string) ($deposit->user->name ?? 'Guest'), 'inline' => true],
            ['name' => 'Nominal', 'value' => $this->formatRupiah($totalPayable), 'inline' => true],
            ['name' => 'Metode', 'value' => strtoupper((string) ($deposit->display_payment_method ?? $deposit->payment_method ?? '-')), 'inline' => true],
        ], $color, $channel);
    }

    public function sendLowStockAlert(Product $product, int $availableStock): void
    {
        $this->sendEmbed('Stok produk menipis', 'Segera lakukan restock agar pesanan tidak tertahan.', [
            ['name' => 'Produk', 'value' => (string) $product->name, 'inline' => true],
            ['name' => 'Sisa Stok', 'value' => (string) $availableStock, 'inline' => true],
            ['name' => 'Batas Reminder', 'value' => (string) ($product->stock_reminder ?? 0), 'inline' => true],
        ], 16433050, 'stock');
    }

    public function sendSystemAlert(string $title, ?string $description = null, array $fields = [], int $color = 16433050, string $channel = 'maintenance'): void
    {
        $this->sendEmbed($title, $description, $fields, $color, $channel);
    }

    protected function sendEmbed(string $title, ?string $description, array $fields, int $color, string $channel): void
    {
        $webhookUrl = $this->webhookUrl($channel);

        if (! filled($webhookUrl)) {
            return;
        }

        try {
            Http::timeout(10)->post($webhookUrl, [
                'username' => config('services.discord.username', 'Wiboost Bot'),
                'avatar_url' => config('services.discord.avatar_url'),
                'embeds' => [[
                    'title' => $title,
                    'description' => $description,
                    'color' => $color,
                    'fields' => $fields,
                ]],
            ])->throw();
        } catch (Throwable $e) {
            Log::warning("Discord webhook send failed [{$channel}]: " . $e->getMessage());
        }
    }

    protected function webhookUrl(string $channel): ?string
    {
        $webhooks = config('services.discord.webhooks', []);
        $channelUrl = $webhooks[$channel] ?? null;

        if (filled($channelUrl)) {
            return $channelUrl;
        }

        $fallbackUrl = config('services.discord.webhook_url');

        return filled($fallbackUrl) ? $fallbackUrl : null;
    }

    protected function resolveTransactionTotalPayable(Transaction $transaction): int
    {
        $totalPayable = (int) ($transaction->display_total_payable ?? 0);

        if ($totalPayable > 0) {
            return $totalPayable;
        }

        $paidAmount = (int) round((float) $transaction->paid_amount);

        if ($paidAmount > 0) {
            return $paidAmount;
        }

        return (int) round((float) $transaction->amount);
    }

    protected function resolveDepositTotalPayable(Deposit $deposit): int
    {
        $paidAmount = (int) round((float) $deposit->paid_amount);

        if ($paidAmount > 0) {
            return $paidAmount;
        }

        $totalPayable = (int) ($deposit->tripay_total_payable ?? 0);

        if ($totalPayable > 0) {
            return $totalPayable;
        }

        return (int) round((float) $deposit->amount);
    }

    protected function formatRupiah($amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}
