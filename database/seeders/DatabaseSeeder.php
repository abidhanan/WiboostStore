<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Urutan ini sangat penting karena User butuh Role
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
        ]);
    }
}