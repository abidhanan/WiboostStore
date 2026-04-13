<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\DigiflazzService;
use App\Services\DiscordWebhookService;
use App\Services\OrderSosmedService;
use Illuminate\Console\Command;

class WiboostMaintenanceReport extends Command
{
    protected $signature = 'wiboost:maintenance-report {--force : Kirim laporan walaupun tidak ada temuan}';

    protected $description = 'Kirim ringkasan maintenance operasional Wiboost ke Discord webhook';

    public function handle(
        DiscordWebhookService $discordWebhookService,
        DigiflazzService $digiflazzService,
        OrderSosmedService $orderSosmedService
    ): int {
        $pendingMinutes = (int) config('wiboost.pending_alert_minutes', 60);
        $processingMinutes = (int) config('wiboost.processing_alert_minutes', 120);
        $maxItems = max(1, (int) config('wiboost.maintenance_max_items', 5));

        $lowStockProducts = Product::whereIn('process_type', ['account', 'number'])
            ->get()
            ->filter(function ($product) {
                return $product->stock_reminder > 0
                    && $product->available_stock !== null
                    && $product->available_stock <= $product->stock_reminder;
            })
            ->take($maxItems)
            ->values();

        $stalePendingTransactions = Transaction::with(['product', 'user'])
            ->where('payment_status', 'paid')
            ->where('order_status', 'pending')
            ->where('updated_at', '<=', now()->subMinutes($pendingMinutes))
            ->latest('updated_at')
            ->take($maxItems)
            ->get();

        $staleProcessingTransactions = Transaction::with(['product', 'user'])
            ->where('payment_status', 'paid')
            ->where('order_status', 'processing')
            ->where('updated_at', '<=', now()->subMinutes($processingMinutes))
            ->latest('updated_at')
            ->take($maxItems)
            ->get();

        $manualQueue = Transaction::with(['product', 'user'])
            ->where('payment_status', 'paid')
            ->where('order_status', 'processing')
            ->whereHas('product', function ($query) {
                $query->where('process_type', 'manual');
            })
            ->latest('updated_at')
            ->take($maxItems)
            ->get();

        $misconfiguredApiProducts = Product::where('process_type', 'api')
            ->where(function ($query) {
                $query->whereNull('provider_source')
                    ->orWhere('provider_source', '')
                    ->orWhereNull('provider_product_id')
                    ->orWhere('provider_product_id', '');
            })
            ->take($maxItems)
            ->get();

        $providerStatus = [
            'Digiflazz' => $digiflazzService->isConfigured(),
            'OrderSosmed' => $orderSosmedService->isConfigured(),
            'Midtrans' => filled(config('midtrans.server_key')) && filled(config('midtrans.client_key')),
            'Discord Webhook' => $discordWebhookService->isEnabled(),
        ];

        $issueCount = $lowStockProducts->count()
            + $stalePendingTransactions->count()
            + $staleProcessingTransactions->count()
            + $manualQueue->count()
            + $misconfiguredApiProducts->count()
            + collect($providerStatus)->filter(fn ($isReady) => ! $isReady)->count();

        if ($issueCount === 0 && ! $this->option('force')) {
            $this->info('Maintenance report dilewati karena tidak ada temuan baru.');

            return self::SUCCESS;
        }

        $fields = [
            ['name' => 'Stok menipis', 'value' => (string) $lowStockProducts->count(), 'inline' => true],
            ['name' => 'Pending berbayar', 'value' => (string) $stalePendingTransactions->count(), 'inline' => true],
            ['name' => 'Processing lama', 'value' => (string) $staleProcessingTransactions->count(), 'inline' => true],
            ['name' => 'Manual queue', 'value' => (string) $manualQueue->count(), 'inline' => true],
            ['name' => 'API belum lengkap', 'value' => (string) $misconfiguredApiProducts->count(), 'inline' => true],
            [
                'name' => 'Provider siap',
                'value' => collect($providerStatus)->map(fn ($ready, $name) => ($ready ? 'OK' : 'ISSUE') . ' ' . $name)->implode("\n"),
                'inline' => false,
            ],
        ];

        if ($lowStockProducts->isNotEmpty()) {
            $fields[] = [
                'name' => 'Produk stok tipis',
                'value' => $lowStockProducts->map(fn ($product) => "{$product->name} (sisa {$product->available_stock})")->implode("\n"),
                'inline' => false,
            ];
        }

        if ($stalePendingTransactions->isNotEmpty()) {
            $fields[] = [
                'name' => 'Pending berbayar yang tertahan',
                'value' => $stalePendingTransactions->map(fn ($transaction) => "{$transaction->invoice_number} - " . ($transaction->product->name ?? 'Produk'))->implode("\n"),
                'inline' => false,
            ];
        }

        if ($staleProcessingTransactions->isNotEmpty()) {
            $fields[] = [
                'name' => 'Processing lama',
                'value' => $staleProcessingTransactions->map(fn ($transaction) => "{$transaction->invoice_number} - " . ($transaction->product->name ?? 'Produk'))->implode("\n"),
                'inline' => false,
            ];
        }

        if ($manualQueue->isNotEmpty()) {
            $fields[] = [
                'name' => 'Manual order aktif',
                'value' => $manualQueue->map(fn ($transaction) => "{$transaction->invoice_number} - " . ($transaction->product->name ?? 'Produk'))->implode("\n"),
                'inline' => false,
            ];
        }

        if ($misconfiguredApiProducts->isNotEmpty()) {
            $fields[] = [
                'name' => 'Produk API belum lengkap',
                'value' => $misconfiguredApiProducts->map(fn ($product) => $product->name)->implode("\n"),
                'inline' => false,
            ];
        }

        if ($discordWebhookService->isEnabled()) {
            $discordWebhookService->sendSystemAlert(
                'Laporan maintenance Wiboost',
                'Ringkasan operasional otomatis untuk membantu monitoring stok, antrean order, dan kesiapan integrasi.',
                $fields,
                $issueCount > 0 ? 16433050 : 4968121
            );

            $this->info("Maintenance report dikirim dengan {$issueCount} temuan.");
        } else {
            $this->warn("Discord webhook belum diatur. Laporan dihitung lokal dengan {$issueCount} temuan, tetapi tidak dikirim.");
        }

        return self::SUCCESS;
    }
}
