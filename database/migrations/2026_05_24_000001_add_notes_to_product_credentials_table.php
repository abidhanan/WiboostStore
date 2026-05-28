<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_credentials') && ! Schema::hasColumn('product_credentials', 'notes')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('data_5');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_credentials') && Schema::hasColumn('product_credentials', 'notes')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};
