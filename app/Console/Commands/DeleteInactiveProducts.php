<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class DeleteInactiveProducts extends Command
{
    protected $signature = 'wiboost:delete-inactive-products
        {--execute : Jalankan penghapusan. Tanpa opsi ini hanya preview.}
        {--force-with-transactions : Tetap hapus walau ada transaksi terkait. Tidak disarankan karena riwayat transaksi ikut terhapus oleh relasi database.}';

    protected $description = 'Hapus semua produk yang berstatus nonaktif dari katalog.';

    public function handle(): int
    {
        $products = $this->inactiveProducts();
        $transactionCount = $products->sum('transactions_count');
        $credentialCount = $products->sum('credentials_count');
        $stockCount = $products->sum('stocks_count');

        $this->table(
            ['Produk nonaktif', 'Transaksi terkait', 'Kredensial/stok terkait', 'Contoh produk'],
            [[
                $products->count(),
                $transactionCount,
                $credentialCount + $stockCount,
                $products->pluck('name')->take(8)->implode("\n") ?: '-',
            ]]
        );

        if (! $this->option('execute')) {
            $this->warn('Preview saja. Tambahkan --execute untuk benar-benar menghapus produk nonaktif.');

            return self::SUCCESS;
        }

        if ($products->isEmpty()) {
            $this->info('Tidak ada produk nonaktif yang perlu dihapus.');

            return self::SUCCESS;
        }

        if ($transactionCount > 0 && ! $this->option('force-with-transactions')) {
            $this->error('Dibatalkan: ada produk nonaktif yang punya transaksi terkait.');
            $this->warn('Menghapus produk tersebut akan ikut menghapus riwayat transaksi karena relasi database cascade. Jika tetap mau, jalankan ulang dengan --force-with-transactions.');

            return self::FAILURE;
        }

        Product::query()
            ->whereIn('id', $products->pluck('id'))
            ->delete();

        $this->info('Produk nonaktif berhasil dihapus: ' . $products->count() . ' produk.');

        return self::SUCCESS;
    }

    protected function inactiveProducts()
    {
        return Product::query()
            ->with('category.parent.parent')
            ->withCount(['transactions', 'credentials', 'stocks'])
            ->where(function ($query) {
                $query->where('is_active', false)
                    ->orWhereNull('is_active')
                    ->orWhereNull('status')
                    ->orWhere('status', '!=', 'active');
            })
            ->orderBy('name')
            ->get();
    }
}
