<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'google_avatar')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->text('google_avatar')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'google_avatar')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('google_avatar', 2048)->nullable()->change();
            });
        }
    }
};
