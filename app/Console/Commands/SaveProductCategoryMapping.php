<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Support\ProductCategoryMapping;
use Illuminate\Console\Command;

class SaveProductCategoryMapping extends Command
{
    protected $signature = 'wiboost:save-product-category-mapping
        {--normalize-descriptions : Ubah semua deskripsi kategori/sub kategori menjadi teks standar}';

    protected $description = 'Simpan snapshot mapping produk ke kategori saat ini agar sync provider tidak mengacak susunan katalog.';

    public function handle(): int
    {
        $payload = ProductCategoryMapping::saveFromCurrentProducts();

        $this->info('Mapping produk berhasil disimpan.');
        $this->line('Jumlah mapping: ' . number_format((int) ($payload['count'] ?? 0), 0, ',', '.'));
        $this->line('Jumlah produk : ' . number_format((int) ($payload['product_count'] ?? 0), 0, ',', '.'));
        $this->line('Jumlah kategori: ' . number_format((int) ($payload['category_count'] ?? 0), 0, ',', '.'));
        $this->line('Lokasi file : ' . ProductCategoryMapping::storagePath());

        if (filled($payload['backup_path'] ?? null)) {
            $this->line('Backup file : ' . ProductCategoryMapping::backupStoragePath((string) $payload['backup_path']));
        }

        if ($this->option('normalize-descriptions')) {
            $updated = Category::query()->update([
                'description' => ProductCategoryMapping::CATEGORY_DESCRIPTION,
                'updated_at' => now(),
            ]);

            $this->info('Deskripsi kategori/sub kategori dinormalisasi: ' . number_format($updated, 0, ',', '.') . ' kategori.');
        }

        return self::SUCCESS;
    }
}
