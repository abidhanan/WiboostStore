<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tutorials', function (Blueprint $table) {
            if (! Schema::hasColumn('tutorials', 'main_category')) {
                $table->string('main_category')->nullable()->after('category');
            }

            if (! Schema::hasColumn('tutorials', 'sub_category')) {
                $table->string('sub_category')->nullable()->after('main_category');
            }

            if (! Schema::hasColumn('tutorials', 'sub_category_image')) {
                $table->string('sub_category_image')->nullable()->after('sub_category');
            }
        });

        DB::table('tutorials')
            ->whereNull('main_category')
            ->update(['main_category' => DB::raw('category')]);

        DB::table('tutorials')
            ->whereNull('sub_category')
            ->update(['sub_category' => DB::raw('category')]);

        DB::table('tutorials')
            ->whereNull('sub_category_image')
            ->whereNotNull('image')
            ->update(['sub_category_image' => DB::raw('image')]);
    }

    public function down(): void
    {
        Schema::table('tutorials', function (Blueprint $table) {
            $columns = [];

            foreach (['main_category', 'sub_category', 'sub_category_image'] as $column) {
                if (Schema::hasColumn('tutorials', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
