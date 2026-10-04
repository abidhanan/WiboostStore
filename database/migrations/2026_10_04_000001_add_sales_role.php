<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        DB::table('roles')->updateOrInsert(
            ['id' => 9],
            ['name' => 'Sales', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $roleIsUsed = Schema::hasTable('users')
            && DB::table('users')->where('role_id', 9)->exists();

        if (! $roleIsUsed) {
            DB::table('roles')->where('id', 9)->delete();
        }
    }
};
