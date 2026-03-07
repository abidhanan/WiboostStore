<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Akun Super Admin Anda
        User::create([
            'name' => 'Super Admin Wiboost',
            'email' => 'admin@wiboost.store',
            'password' => Hash::make('password123'), // Silakan ganti passwordnya nanti
            'role_id' => 1, // 1 adalah ID untuk Super Admin
        ]);

        // Akun Dummy User untuk testing
        User::create([
            'name' => 'Pembeli Dummy',
            'email' => 'user@wiboost.store',
            'password' => Hash::make('password123'),
            'role_id' => 5, // 5 adalah ID untuk User biasa
        ]);
    }
}