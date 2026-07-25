<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transactions')) {
            return;
        }

        if (! Schema::hasColumn('transactions', 'product_name_snapshot')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('product_name_snapshot')->nullable()->after('product_id');
            });
        }

        if (Schema::hasTable('products')) {
            DB::statement("
                UPDATE transactions
                LEFT JOIN products ON products.id = transactions.product_id
                SET transactions.product_name_snapshot = products.name
                WHERE transactions.product_name_snapshot IS NULL
                    AND products.name IS NOT NULL
            ");
        }

        if (Schema::hasTable('reseller_commissions')) {
            DB::statement("
                UPDATE transactions
                INNER JOIN reseller_commissions ON reseller_commissions.transaction_id = transactions.id
                SET transactions.product_name_snapshot = reseller_commissions.product_name
                WHERE transactions.product_name_snapshot IS NULL
                    AND reseller_commissions.product_name IS NOT NULL
                    AND reseller_commissions.product_name <> ''
            ");
        }

        if (Schema::hasTable('wallet_histories')) {
            DB::statement("
                UPDATE transactions
                INNER JOIN wallet_histories ON wallet_histories.invoice_number = transactions.invoice_number
                SET transactions.product_name_snapshot = TRIM(REPLACE(wallet_histories.description, 'Pembelian Produk:', ''))
                WHERE transactions.product_name_snapshot IS NULL
                    AND wallet_histories.description LIKE 'Pembelian Produk:%'
            ");
        }

        DB::statement("
            UPDATE transactions
            SET product_name_snapshot = 'Membership Wiboost 30 Hari'
            WHERE product_name_snapshot IS NULL
                AND (invoice_number LIKE 'MEM-%' OR response_data LIKE '%membership%')
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('transactions') || ! Schema::hasColumn('transactions', 'product_name_snapshot')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('product_name_snapshot');
        });
    }
};
