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
        User::updateOrCreate(['email' => 'admin@wiboost.store'], [
            'name' => 'Super Admin Wiboost',
            'password' => Hash::make('password123'), // Silakan ganti passwordnya nanti
            'role_id' => 1,
        ]);

        // Akun Dummy User untuk testing
        User::updateOrCreate(['email' => 'user@wiboost.store'], [
            'name' => 'Pembeli Dummy',
            'password' => Hash::make('password123'),
            'role_id' => 2,
        ]);
    }
}
