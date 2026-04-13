<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'provider_source')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('provider_source')->nullable()->after('provider_product_id');
            });
        }

        if (!Schema::hasColumn('products', 'provider_quantity')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('provider_quantity')->default(1)->after('provider_source');
            });
        }

        if (!Schema::hasColumn('products', 'target_label')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('target_label')->nullable()->after('provider_quantity');
            });
        }

        if (!Schema::hasColumn('products', 'target_placeholder')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('target_placeholder')->nullable()->after('target_label');
            });
        }

        if (!Schema::hasColumn('products', 'target_hint')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('target_hint')->nullable()->after('target_placeholder');
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('products', 'provider_source') ? 'provider_source' : null,
            Schema::hasColumn('products', 'provider_quantity') ? 'provider_quantity' : null,
            Schema::hasColumn('products', 'target_label') ? 'target_label' : null,
            Schema::hasColumn('products', 'target_placeholder') ? 'target_placeholder' : null,
            Schema::hasColumn('products', 'target_hint') ? 'target_hint' : null,
        ]));

        if ($columns !== []) {
            Schema::table('products', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
