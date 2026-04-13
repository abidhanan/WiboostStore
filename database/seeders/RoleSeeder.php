<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::updateOrCreate(['id' => 1], ['name' => 'Admin']);
        Role::updateOrCreate(['id' => 2], ['name' => 'Buyer']);
    }
}
