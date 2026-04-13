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
    public function isEnabled(): bool
    {
        return filled(config('services.discord.webhook_url'));
    }

    public function sendOrderAlert(Transaction $transaction, string $title, ?string $description = null, int $color = 5922504): void
    {
        $transaction->loadMissing('user', 'product');

        $fields = [
            ['name' => 'Invoice', 'value' => (string) $transaction->invoice_number, 'inline' => true],
            ['name' => 'Produk', 'value' => (string) ($transaction->product->name ?? '-'), 'inline' => true],
            ['name' => 'User', 'value' => (string) ($transaction->user->name ?? 'Guest'), 'inline' => true],
            ['name' => 'Nominal', 'value' => 'Rp ' . number_format((float) $transaction->amount, 0, ',', '.'), 'inline' => true],
            ['name' => 'Status', 'value' => strtoupper((string) $transaction->order_status), 'inline' => true],
            ['name' => 'Pembayaran', 'value' => strtoupper((string) ($transaction->payment_method ?? '-')), 'inline' => true],
        ];

        if (filled($transaction->target_data) && ($transaction->product?->requires_target_input ?? false)) {
            $fields[] = [
                'name' => 'Target',
                'value' => mb_strimwidth((string) $transaction->target_data, 0, 1000, '...'),
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

        $this->sendEmbed($title, $description, $fields, $color);
    }

    public function sendDepositAlert(Deposit $deposit, string $title, ?string $description = null, int $color = 4968121): void
    {
        $deposit->loadMissing('user');

        $this->sendEmbed($title, $description, [
            ['name' => 'Invoice', 'value' => (string) $deposit->invoice_number, 'inline' => true],
            ['name' => 'User', 'value' => (string) ($deposit->user->name ?? 'Guest'), 'inline' => true],
            ['name' => 'Nominal', 'value' => 'Rp ' . number_format((float) $deposit->amount, 0, ',', '.'), 'inline' => true],
            ['name' => 'Metode', 'value' => strtoupper((string) ($deposit->payment_method ?? '-')), 'inline' => true],
        ], $color);
    }

    public function sendLowStockAlert(Product $product, int $availableStock): void
    {
        $this->sendEmbed('Stok produk menipis', 'Segera lakukan restock agar pesanan tidak tertahan.', [
            ['name' => 'Produk', 'value' => (string) $product->name, 'inline' => true],
            ['name' => 'Sisa Stok', 'value' => (string) $availableStock, 'inline' => true],
            ['name' => 'Batas Reminder', 'value' => (string) ($product->stock_reminder ?? 0), 'inline' => true],
        ], 16433050);
    }

    protected function sendEmbed(string $title, ?string $description, array $fields, int $color): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            Http::timeout(10)->post((string) config('services.discord.webhook_url'), [
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
            Log::warning('Discord webhook send failed: ' . $e->getMessage());
        }
    }
}
