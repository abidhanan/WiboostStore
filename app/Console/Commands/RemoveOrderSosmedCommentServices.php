<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveOrderSosmedCommentServices extends Command
{
    protected $signature = 'wiboost:remove-ordersosmed-comment-services
        {--execute : Jalankan penghapusan. Tanpa opsi ini hanya preview.}
        {--force-with-transactions : Tetap hapus walau ada transaksi terkait. Tidak disarankan.}';

    protected $description = 'Hapus layanan komentar OrderSosmed yang sudah terlanjur masuk ke website.';

    public function handle(): int
    {
        $products = $this->commentProducts();
        $productIds = $products->pluck('id');
        $transactionCount = $productIds->isEmpty()
            ? 0
            : DB::table('transactions')->whereIn('product_id', $productIds)->count();

        $this->table(
            ['Target', 'Produk OrderSosmed komentar', 'Transaksi terkait', 'Contoh produk'],
            [[
                'Layanan komentar / komen / reply',
                $products->count(),
                $transactionCount,
                $products->pluck('name')->take(6)->implode("\n") ?: '-',
            ]]
        );

        if (! $this->option('execute')) {
            $this->warn('Preview saja. Tambahkan --execute untuk benar-benar menghapus.');

            return self::SUCCESS;
        }

        if ($productIds->isEmpty()) {
            $this->info('Tidak ada layanan komentar OrderSosmed yang perlu dihapus.');

            return self::SUCCESS;
        }

        if ($transactionCount > 0 && ! $this->option('force-with-transactions')) {
            $this->error('Dibatalkan: ada transaksi terkait produk komentar OrderSosmed.');
            $this->warn('Ini sengaja diblok supaya riwayat order tidak terhapus. Kalau tetap ingin hapus semua, jalankan ulang dengan --force-with-transactions.');

            return self::FAILURE;
        }

        Product::query()
            ->whereIn('id', $productIds)
            ->delete();

        $this->info('Layanan komentar OrderSosmed berhasil dihapus.');

        return self::SUCCESS;
    }

    protected function commentProducts()
    {
        $needles = ['comment', 'comments', 'komentar', 'komen', 'reply', 'replies', 'balasan'];

        return Product::query()
            ->where('provider_source', 'ordersosmed')
            ->where(function ($query) use ($needles) {
                foreach ($needles as $needle) {
                    $query->orWhere('name', 'like', '%' . $needle . '%')
                        ->orWhere('description', 'like', '%' . $needle . '%');
                }
            })
            ->orderBy('price')
            ->orderBy('name')
            ->get(['id', 'name', 'price']);
    }
}
