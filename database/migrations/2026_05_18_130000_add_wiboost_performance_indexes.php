<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->indexIfMissing('transactions', ['user_id', 'created_at'], 'trx_user_created_idx');
        $this->indexIfMissing('transactions', ['user_id', 'payment_method', 'payment_status', 'created_at'], 'trx_user_payment_created_idx');
        $this->indexIfMissing('transactions', ['payment_status', 'order_status', 'updated_at'], 'trx_payment_order_updated_idx');
        $this->indexIfMissing('transactions', ['product_id', 'payment_status', 'order_status'], 'trx_product_status_idx');

        $this->indexIfMissing('products', ['category_id', 'process_type', 'is_active', 'status'], 'products_catalog_admin_idx');
        $this->indexIfMissing('products', ['provider_source', 'process_type'], 'products_provider_idx');
        $this->indexIfMissing('categories', ['parent_id', 'slug'], 'categories_parent_slug_idx');

        $this->indexIfMissing('deposits', ['user_id', 'payment_method', 'payment_status', 'created_at'], 'deposits_user_payment_created_idx');
        $this->indexIfMissing('deposits', ['tripay_reference'], 'deposits_tripay_ref_idx');
        $this->indexIfMissing('wallet_histories', ['user_id', 'created_at'], 'wallet_user_created_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('wallet_histories', 'wallet_user_created_idx');
        $this->dropIndexIfExists('deposits', 'deposits_tripay_ref_idx');
        $this->dropIndexIfExists('deposits', 'deposits_user_payment_created_idx');
        $this->dropIndexIfExists('categories', 'categories_parent_slug_idx');
        $this->dropIndexIfExists('products', 'products_provider_idx');
        $this->dropIndexIfExists('products', 'products_catalog_admin_idx');
        $this->dropIndexIfExists('transactions', 'trx_product_status_idx');
        $this->dropIndexIfExists('transactions', 'trx_payment_order_updated_idx');
        $this->dropIndexIfExists('transactions', 'trx_user_payment_created_idx');
        $this->dropIndexIfExists('transactions', 'trx_user_created_idx');
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
