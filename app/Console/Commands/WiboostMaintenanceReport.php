<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\DigiflazzService;
use App\Services\DiscordWebhookService;
use App\Services\OrderSosmedService;
use App\Support\WiboostCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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

        $lowStockProducts = Product::where('process_type', 'account')
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
            ->whereHas('product', fn ($query) => $query->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()))
            ->where('updated_at', '<=', now()->subMinutes($pendingMinutes))
            ->latest('updated_at')
            ->take($maxItems)
            ->get();

        $staleProcessingTransactions = Transaction::with(['product', 'user'])
            ->where('payment_status', 'paid')
            ->where('order_status', 'processing')
            ->whereHas('product', fn ($query) => $query->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()))
            ->where('updated_at', '<=', now()->subMinutes($processingMinutes))
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

        $recentWebsiteErrors = $this->recentWebsiteErrors($maxItems);

        $providerDiagnostics = $this->buildProviderDiagnostics(
            $discordWebhookService,
            $digiflazzService,
            $orderSosmedService
        );
        $providerStatus = collect($providerDiagnostics)->mapWithKeys(
            fn (array $diagnostic, string $name) => [$name => $diagnostic['status'] === 'ok']
        )->all();

        $issueCount = $lowStockProducts->count()
            + $stalePendingTransactions->count()
            + $staleProcessingTransactions->count()
            + $misconfiguredApiProducts->count()
            + count($recentWebsiteErrors)
            + collect($providerDiagnostics)->filter(fn (array $diagnostic) => $diagnostic['status'] === 'issue')->count();

        if ($issueCount === 0 && ! $this->option('force')) {
            $this->info('Maintenance report dilewati karena tidak ada temuan baru.');

            return self::SUCCESS;
        }

        $fields = [
            ['name' => 'Stok menipis', 'value' => (string) $lowStockProducts->count(), 'inline' => true],
            ['name' => 'Pending berbayar', 'value' => (string) $stalePendingTransactions->count(), 'inline' => true],
            ['name' => 'Processing lama', 'value' => (string) $staleProcessingTransactions->count(), 'inline' => true],
            ['name' => 'API belum lengkap', 'value' => (string) $misconfiguredApiProducts->count(), 'inline' => true],
            ['name' => 'Error website terbaru', 'value' => (string) count($recentWebsiteErrors), 'inline' => true],
            [
                'name' => 'Provider siap',
                'value' => collect($providerStatus)->map(fn ($ready, $name) => ($ready ? 'OK' : 'ISSUE') . ' ' . $name)->implode("\n"),
                'inline' => false,
            ],
            [
                'name' => 'Diagnostik provider',
                'value' => collect($providerDiagnostics)
                    ->map(fn (array $diagnostic, string $name) => strtoupper($diagnostic['status']) . " {$name}: {$diagnostic['message']}")
                    ->implode("\n"),
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

        if ($misconfiguredApiProducts->isNotEmpty()) {
            $fields[] = [
                'name' => 'Produk API belum lengkap',
                'value' => $misconfiguredApiProducts->map(fn ($product) => $product->name)->implode("\n"),
                'inline' => false,
            ];
        }

        if ($recentWebsiteErrors !== []) {
            $fields[] = [
                'name' => 'Error website dari log',
                'value' => mb_strimwidth(implode("\n", $recentWebsiteErrors), 0, 1000, '...'),
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

    protected function buildProviderDiagnostics(
        DiscordWebhookService $discordWebhookService,
        DigiflazzService $digiflazzService,
        OrderSosmedService $orderSosmedService
    ): array {
        $diagnostics = [];

        if (! $digiflazzService->isConfigured()) {
            $diagnostics['Digiflazz'] = [
                'status' => 'issue',
                'message' => 'Kredensial belum diatur.',
            ];
        } else {
            $balance = $digiflazzService->getBalance();
            $deposit = (float) data_get($balance, 'raw.data.deposit', 0);
            $message = trim((string) ($balance['message'] ?? ''));

            if (! ($balance['success'] ?? false)) {
                $diagnostics['Digiflazz'] = [
                    'status' => 'issue',
                    'message' => $message !== '' ? $message : 'Gagal terhubung ke provider.',
                ];
            } elseif ($deposit <= 0) {
                $diagnostics['Digiflazz'] = [
                    'status' => 'issue',
                    'message' => 'Saldo provider kosong atau belum terbaca.',
                ];
            } else {
                $diagnostics['Digiflazz'] = [
                    'status' => 'ok',
                    'message' => 'Saldo provider Rp ' . number_format($deposit, 0, ',', '.'),
                ];
            }
        }

        if (! $orderSosmedService->isConfigured()) {
            $diagnostics['OrderSosmed'] = [
                'status' => 'issue',
                'message' => 'Kredensial belum lengkap.',
            ];
        } else {
            $profile = $orderSosmedService->getProfile();

            if (! ($profile['success'] ?? false)) {
                $diagnostics['OrderSosmed'] = [
                    'status' => 'issue',
                    'message' => (string) ($profile['message'] ?? 'Gagal terhubung ke provider.'),
                ];
            } else {
                $balance = $this->resolveProviderBalance($profile['data'] ?? []);
                $diagnostics['OrderSosmed'] = [
                    'status' => 'ok',
                    'message' => $balance !== null
                        ? 'Profil aktif. Saldo provider Rp ' . number_format($balance, 0, ',', '.')
                        : 'Profil API aktif dan bisa diakses.',
                ];
            }
        }

        $publicUrl = rtrim((string) config('wiboost.public_url', config('app.url')), '/');
        $callbackUrl = $publicUrl . '/tripay/callback';
        $tripayConfigured = filled(config('tripay.api_key')) && filled(config('tripay.private_key')) && filled(config('tripay.merchant_code'));

        if (! $tripayConfigured) {
            $diagnostics['Tripay'] = [
                'status' => 'issue',
                'message' => 'API key, private key, atau merchant code belum diatur.',
            ];
        } elseif ($this->looksLikeLocalUrl($publicUrl)) {
            $diagnostics['Tripay'] = [
                'status' => 'issue',
                'message' => "Public URL masih lokal ({$publicUrl}). Callback eksternal belum siap.",
            ];
        } else {
            $mode = config('tripay.is_production') ? 'production' : 'sandbox';
            $diagnostics['Tripay'] = [
                'status' => 'ok',
                'message' => "Mode {$mode}. Callback {$callbackUrl}",
            ];
        }

        $diagnostics['Discord Webhook'] = [
            'status' => $discordWebhookService->isEnabled() ? 'ok' : 'issue',
            'message' => $discordWebhookService->isEnabled()
                ? 'Webhook aktif.'
                : 'Webhook belum diatur.',
        ];

        return $diagnostics;
    }

    protected function resolveProviderBalance(array $profile): ?float
    {
        foreach (['balance', 'saldo', 'deposit'] as $key) {
            $value = data_get($profile, $key);

            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    protected function recentWebsiteErrors(int $maxItems): array
    {
        $logPath = storage_path('logs/laravel.log');

        if (! File::exists($logPath) || ! is_readable($logPath)) {
            return [];
        }

        $lines = File::lines($logPath);
        $errorLines = [];

        foreach ($lines as $line) {
            if (! preg_match('/\.(ERROR|CRITICAL|ALERT|EMERGENCY):\s+/i', $line)) {
                continue;
            }

            $errorLines[] = mb_strimwidth(trim($line), 0, 260, '...');

            if (count($errorLines) > 100) {
                array_shift($errorLines);
            }
        }

        return array_slice(array_reverse($errorLines), 0, $maxItems);
    }

    protected function looksLikeLocalUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return true;
        }

        $host = strtolower($host);

        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }

        if (str_ends_with($host, '.test') || str_ends_with($host, '.local') || str_ends_with($host, '.localhost')) {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
        }

        return false;
    }
}
