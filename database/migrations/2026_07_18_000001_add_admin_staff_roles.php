<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $roles = [
            1 => 'Super Admin',
            2 => 'Buyer',
            3 => 'Reseller',
            4 => 'Keuangan',
            5 => 'Buyer',
            6 => 'Gudang',
            7 => 'Marketing',
            8 => 'Customer Service',
        ];

        foreach ($roles as $id => $name) {
            DB::table('roles')->updateOrInsert(
                ['id' => $id],
                [
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('id', [4, 6, 7, 8])->delete();
        DB::table('roles')->where('id', 1)->update(['name' => 'Admin', 'updated_at' => now()]);
    }
};
