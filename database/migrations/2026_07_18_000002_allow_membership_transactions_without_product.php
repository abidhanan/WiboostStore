<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transactions') || ! Schema::hasColumn('transactions', 'product_id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        try {
            Schema::table('transactions', function ($table) {
                $table->dropForeign(['product_id']);
            });
        } catch (Throwable) {
            // Some hosting databases may already have this FK changed.
        }

        DB::statement('ALTER TABLE transactions MODIFY product_id BIGINT UNSIGNED NULL');

        try {
            Schema::table('transactions', function ($table) {
                $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            });
        } catch (Throwable) {
            // If the foreign key already exists, keep the column nullable.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('transactions') || ! Schema::hasColumn('transactions', 'product_id')) {
            return;
        }

        try {
            Schema::table('transactions', function ($table) {
                $table->dropForeign(['product_id']);
            });
        } catch (Throwable) {
            //
        }
    }
};
