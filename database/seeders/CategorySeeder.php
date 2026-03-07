<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            // Kategori Otomatis (Tembak API)
            ['name' => 'Suntik Sosmed', 'fulfillment_type' => 'auto_api'],
            ['name' => 'Top Up Game', 'fulfillment_type' => 'auto_api'],
            ['name' => 'Kuota Murah', 'fulfillment_type' => 'auto_api'],
            
            // Kategori Berbasis Stok (Tarik dari database)
            ['name' => 'Aplikasi Premium', 'fulfillment_type' => 'stock_based'],
            
            // Kategori Manual (Dikerjakan Admin)
            ['name' => 'Nomor Luar', 'fulfillment_type' => 'manual_action'],
            ['name' => 'Buzzer', 'fulfillment_type' => 'manual_action'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'fulfillment_type' => $category['fulfillment_type'],
            ]);
        }
    }
}