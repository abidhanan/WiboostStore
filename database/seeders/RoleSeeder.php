<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::updateOrCreate(['id' => 1], ['name' => 'Super Admin']);
        Role::updateOrCreate(['id' => 2], ['name' => 'Buyer']);
        Role::updateOrCreate(['id' => 3], ['name' => 'Reseller']);
        Role::updateOrCreate(['id' => 4], ['name' => 'Keuangan']);
        Role::updateOrCreate(['id' => 5], ['name' => 'Buyer']);
        Role::updateOrCreate(['id' => 6], ['name' => 'Gudang']);
        Role::updateOrCreate(['id' => 7], ['name' => 'Marketing']);
        Role::updateOrCreate(['id' => 8], ['name' => 'Customer Service']);
        Role::updateOrCreate(['id' => 9], ['name' => 'Sales']);
    }
}
