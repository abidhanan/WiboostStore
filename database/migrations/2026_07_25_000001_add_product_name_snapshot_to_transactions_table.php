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
            DB::table('transactions')
                ->select(['id', 'product_id'])
                ->whereNull('product_name_snapshot')
                ->whereNotNull('product_id')
                ->orderBy('id')
                ->chunkById(200, function ($transactions) {
                    $productNames = DB::table('products')
                        ->whereIn('id', $transactions->pluck('product_id')->filter()->unique()->values())
                        ->pluck('name', 'id');

                    foreach ($transactions as $transaction) {
                        $productName = trim((string) ($productNames[$transaction->product_id] ?? ''));

                        if ($productName === '') {
                            continue;
                        }

                        DB::table('transactions')
                            ->where('id', $transaction->id)
                            ->whereNull('product_name_snapshot')
                            ->update(['product_name_snapshot' => $productName]);
                    }
                });
        }

        if (Schema::hasTable('reseller_commissions')) {
            DB::table('transactions')
                ->select('id')
                ->whereNull('product_name_snapshot')
                ->orderBy('id')
                ->chunkById(200, function ($transactions) {
                    $commissionProductNames = DB::table('reseller_commissions')
                        ->select(['transaction_id', 'product_name'])
                        ->whereIn('transaction_id', $transactions->pluck('id'))
                        ->whereNotNull('product_name')
                        ->where('product_name', '<>', '')
                        ->orderBy('id')
                        ->get()
                        ->unique('transaction_id')
                        ->pluck('product_name', 'transaction_id');

                    foreach ($transactions as $transaction) {
                        $productName = trim((string) ($commissionProductNames[$transaction->id] ?? ''));

                        if ($productName === '') {
                            continue;
                        }

                        DB::table('transactions')
                            ->where('id', $transaction->id)
                            ->whereNull('product_name_snapshot')
                            ->update(['product_name_snapshot' => $productName]);
                    }
                });
        }

        if (Schema::hasTable('wallet_histories')) {
            DB::table('transactions')
                ->select(['id', 'invoice_number'])
                ->whereNull('product_name_snapshot')
                ->whereNotNull('invoice_number')
                ->orderBy('id')
                ->chunkById(200, function ($transactions) {
                    $walletDescriptions = DB::table('wallet_histories')
                        ->select(['invoice_number', 'description'])
                        ->whereIn('invoice_number', $transactions->pluck('invoice_number')->filter()->unique()->values())
                        ->where('description', 'like', 'Pembelian Produk:%')
                        ->orderBy('id')
                        ->get()
                        ->unique('invoice_number')
                        ->mapWithKeys(function ($history) {
                            return [
                                $history->invoice_number => trim(str_replace('Pembelian Produk:', '', (string) $history->description)),
                            ];
                        });

                    foreach ($transactions as $transaction) {
                        $productName = trim((string) ($walletDescriptions[$transaction->invoice_number] ?? ''));

                        if ($productName === '') {
                            continue;
                        }

                        DB::table('transactions')
                            ->where('id', $transaction->id)
                            ->whereNull('product_name_snapshot')
                            ->update(['product_name_snapshot' => $productName]);
                    }
                });
        }

        DB::table('transactions')
            ->whereNull('product_name_snapshot')
            ->where(function ($query) {
                $query->where('invoice_number', 'like', 'MEM-%')
                    ->orWhere('response_data', 'like', '%membership%');
            })
            ->update(['product_name_snapshot' => 'Membership Wiboost 30 Hari']);
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
