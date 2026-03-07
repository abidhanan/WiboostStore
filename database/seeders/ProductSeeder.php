<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contoh Produk Suntik Sosmed (Category ID: 1)
        Product::create([
            'category_id' => 1,
            'name' => '1000 Followers Instagram (Mix)',
            'description' => 'Proses 1-24 Jam, High Quality',
            'price' => 15000,
            'status' => 'active'
        ]);

        // Contoh Produk Diamond Mobile Legends (Category ID: 2)
        Product::create([
            'category_id' => 2,
            'name' => '86 Diamonds (77 + 9 Bonus)',
            'description' => 'Proses Instant 1-5 Menit',
            'price' => 20000,
            'status' => 'active'
        ]);
        
        // Contoh Produk Aplikasi Premium (Category ID: 4)
        Product::create([
            'category_id' => 4,
            'name' => 'Netflix Premium 1 Bulan',
            'description' => 'Shared Account, 4K Ultra HD',
            'price' => 35000,
            'status' => 'active'
        ]);
    }
}