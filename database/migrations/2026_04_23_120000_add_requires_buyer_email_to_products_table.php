<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('requires_buyer_email')->default(false)->after('target_hint');
        });

        $premiumRootIds = DB::table('categories')
            ->where('slug', 'aplikasi-premium')
            ->pluck('id');

        $premiumCategoryIds = $premiumRootIds
            ->merge(
                DB::table('categories')
                    ->whereIn('parent_id', $premiumRootIds)
                    ->pluck('id')
            )
            ->filter()
            ->unique()
            ->values();

        if ($premiumCategoryIds->isNotEmpty()) {
            DB::table('products')
                ->where('process_type', 'account')
                ->whereIn('category_id', $premiumCategoryIds->all())
                ->update(['requires_buyer_email' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('requires_buyer_email');
        });
    }
};
