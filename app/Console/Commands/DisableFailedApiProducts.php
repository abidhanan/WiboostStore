<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DisableFailedApiProducts extends Command
{
    protected $signature = 'wiboost:disable-failed-api-products
        {--days=14 : Rentang hari transaksi gagal yang dicek}
        {--threshold=2 : Minimal gagal berulang tanpa sukses agar dianggap bermasalah}
        {--execute : Benar-benar nonaktifkan produk, tanpa opsi ini hanya preview}';

    protected $description = 'Preview/nonaktifkan produk API Digiflazz dan OrderSosmed yang terindikasi error provider.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $threshold = max(1, (int) $this->option('threshold'));
        $since = now()->subDays($days);

        $products = Product::query()
            ->with('category.parent.parent.parent')
            ->where('process_type', 'api')
            ->where(function ($query) {
                $query->whereIn('provider_source', ['digiflazz', 'ordersosmed'])
                    ->orWhereIn('provider_id', ['digiflazz', 'ordersosmed']);
            })
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('status', '!=', 'inactive')
                    ->orWhereNull('status');
            })
            ->get();

        $rows = [];
        $productIds = [];

        foreach ($products as $product) {
            $failedTransactions = Transaction::query()
                ->where('product_id', $product->id)
                ->where('order_status', 'failed')
                ->where('created_at', '>=', $since)
                ->latest()
                ->get(['invoice_number', 'target_notes', 'response_data', 'created_at']);

            if ($failedTransactions->isEmpty()) {
                continue;
            }

            $fatalFailures = $failedTransactions->filter(fn (Transaction $transaction) => $this->looksLikeProviderProductError($transaction));
            $successCount = Transaction::query()
                ->where('product_id', $product->id)
                ->where('order_status', 'success')
                ->where('created_at', '>=', $since)
                ->count();

            $shouldDisable = $fatalFailures->isNotEmpty()
                || ($failedTransactions->count() >= $threshold && $successCount === 0);

            if (! $shouldDisable) {
                continue;
            }

            $lastFailure = $fatalFailures->first() ?? $failedTransactions->first();
            $productIds[] = $product->id;
            $rows[] = [
                $product->id,
                $product->provider_source ?: $product->provider_id,
                Str::limit($this->categoryPath($product), 38),
                Str::limit($product->name, 48),
                $failedTransactions->count(),
                $successCount,
                Str::limit($this->failureMessage($lastFailure), 60),
            ];
        }

        if ($rows === []) {
            $this->info('Tidak ada produk API aktif yang terindikasi error provider pada rentang ini.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Provider', 'Kategori', 'Produk', 'Gagal', 'Sukses', 'Contoh pesan'],
            $rows
        );

        if (! $this->option('execute')) {
            $this->warn('Preview saja. Tambahkan --execute untuk menonaktifkan produk di atas dari katalog.');

            return self::SUCCESS;
        }

        Product::query()
            ->whereIn('id', array_unique($productIds))
            ->update([
                'is_active' => false,
                'status' => 'inactive',
            ]);

        $this->info(count(array_unique($productIds)) . ' produk API bermasalah berhasil dinonaktifkan dari katalog.');

        return self::SUCCESS;
    }

    protected function looksLikeProviderProductError(Transaction $transaction): bool
    {
        $payload = Str::lower($this->failureMessage($transaction) . ' ' . json_encode($transaction->response_data ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $keywords = [
            'produk gangguan',
            'produk sedang gangguan',
            'produk tidak tersedia',
            'produk tidak aktif',
            'product inactive',
            'product is inactive',
            'product unavailable',
            'sku tidak ditemukan',
            'sku not found',
            'service not found',
            'id layanan ordersosmed tidak ditemukan',
            'layanan ordersosmed tidak cocok',
            'provider_mismatch_tokens',
            'provider product name',
            'provider service name',
            'layanan tidak tersedia',
            'layanan tidak aktif',
            'invalid service',
            'provider maintenance',
            'maintenance',
            'sistem sedang gangguan',
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($payload, $keyword)) {
                return true;
            }
        }

        return false;
    }

    protected function failureMessage(Transaction $transaction): string
    {
        return trim((string) ($transaction->target_notes
            ?? data_get($transaction->response_data, 'provider_status_message')
            ?? data_get($transaction->response_data, 'message')
            ?? data_get($transaction->response_data, 'raw.message')
            ?? '-'));
    }

    protected function categoryPath(Product $product): string
    {
        $segments = [];
        $category = $product->category;

        while ($category) {
            $segments[] = $category->name;
            $category = $category->parent;
        }

        return $segments === [] ? '-' : implode(' / ', array_reverse($segments));
    }
}
