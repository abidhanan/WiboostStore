<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->indexIfMissing('products', ['category_id', 'is_active', 'process_type', 'price'], 'products_public_category_price_idx');
        $this->indexIfMissing('products', ['provider_source', 'provider_product_id'], 'products_provider_product_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('products', 'products_provider_product_idx');
        $this->dropIndexIfExists('products', 'products_public_category_price_idx');
    }

    private function indexIfMissing(string $table, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $indexName)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                return collect(DB::select("PRAGMA index_list('{$table}')"))
                    ->contains(fn ($index) => ($index->name ?? null) === $indexName);
            }

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                return count(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName])) > 0;
            }
        } catch (Throwable) {
            return false;
        }

        return false;
    }
};
