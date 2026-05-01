<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addedColumn = false;

        if (! Schema::hasColumn('categories', 'fulfillment_type')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->enum('fulfillment_type', ['auto_api', 'stock_based', 'manual_action'])
                    ->default('auto_api')
                    ->after('slug');
            });

            $addedColumn = true;
        }

        if (! Schema::hasColumn('categories', 'fulfillment_type')) {
            return;
        }

        $stockBased = DB::table('categories')
            ->whereIn('slug', ['aplikasi-premium']);

        $manualAction = DB::table('categories')
            ->whereIn('slug', ['nomor-luar', 'buzzer']);

        if (! $addedColumn) {
            $stockBased->whereNull('fulfillment_type');
            $manualAction->whereNull('fulfillment_type');
        }

        $stockBased->update(['fulfillment_type' => 'stock_based']);
        $manualAction->update(['fulfillment_type' => 'manual_action']);

        DB::table('categories')
            ->whereNull('fulfillment_type')
            ->update(['fulfillment_type' => 'auto_api']);
    }

    public function down(): void
    {
        // This migration only protects upgraded installations from an old schema.
    }
};
