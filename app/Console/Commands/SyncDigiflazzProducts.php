<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DigiflazzService;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SyncDigiflazzProducts extends Command
{
    protected $signature = 'sync:digiflazz';
    protected $description = 'Sync produk dari Digiflazz ke Database Wiboost dengan mapping kategori otomatis';

    public function handle()
    {
        $this->info('--- MEMULAI PROSES SINKRONISASI ---');
        $this->info('Mengambil data dari Digiflazz...');

        $digiService = new DigiflazzService();
        $response = $digiService->getPriceList();

        if (!isset($response['data']) || (isset($response['data']['rc']) && $response['data']['rc'] != '00')) {
            $msg = $response['data']['message'] ?? 'Unknown Error';
            $this->error("Gagal ambil data! Pesan: $msg");
            return;
        }

        $products = $response['data'];
        $total = count($products);
        $this->info("Ditemukan $total produk. Memulai mapping...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($products as $item) {
            try {
                // 1. Hitung Harga Jual
                $hargaModal = $item['price'];
                $markup = 1.10; // Untung 10%
                $hargaJual = ceil(($hargaModal * $markup) / 100) * 100;

                // 2. Mapping Kategori Cerdas
                // Silakan sesuaikan ID ini dengan isi tabel 'categories' kamu
                $brand = strtolower($item['brand']);
                $category = strtolower($item['category']);
                $categoryId = 1; // Default: Lain-lain

                if (str_contains($brand, 'mobile legends')) {
                    $categoryId = 8;
                } elseif (str_contains($brand, 'free fire')) {
                    $categoryId = 9;
                } elseif (str_contains($brand, 'pubg')) {
                    $categoryId = 10; // Contoh ID PUBG
                } elseif (str_contains($category, 'pulsa')) {
                    $categoryId = 2; // Contoh ID Pulsa
                } elseif (str_contains($category, 'e-money') || str_contains($brand, 'dana') || str_contains($brand, 'gopay')) {
                    $categoryId = 3; // Contoh ID E-Wallet
                }

                // 3. Eksekusi Update or Create
                Product::updateOrCreate(
                    ['provider_product_id' => $item['buyer_sku_code']], 
                    [
                        'category_id'         => $categoryId,
                        'process_type'        => 'api',
                        'name'                => $item['product_name'],
                        // Slug dibuat hanya jika produk baru (biar URL nggak berubah-ubah)
                        'slug'                => Str::slug($item['product_name']) . '-' . Str::random(5),
                        'description'         => $item['desc'] ?? '-',
                        'price'               => $hargaJual,
                        'provider_product_id' => $item['buyer_sku_code'],
                        'is_active'           => ($item['buyer_product_status'] && $item['seller_product_status']) ? 1 : 0,
                        'status'              => ($item['buyer_product_status'] && $item['seller_product_status']) ? 'active' : 'inactive',
                        'stock_reminder'      => 0
                    ]
                );
                
                $success++;
            } catch (\Exception $e) {
                $failed++;
                // Catat error di log agar terminal tetap bersih tapi kita tahu masalahnya
                Log::error("Gagal Sync SKU " . ($item['buyer_sku_code'] ?? 'Unknown') . ": " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\n--- HASIL SINKRONISASI ---");
        $this->info("Total Produk: $total");
        $this->info("Berhasil   : $success");
        if ($failed > 0) {
            $this->warn("Gagal      : $failed (Cek storage/logs/laravel.log untuk detail)");
        }
        $this->info("--- SELESAI ---");
    }
}