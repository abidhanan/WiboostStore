<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            if (Schema::hasTable('wallet_histories') && Schema::hasTable('users') && Schema::hasColumn('users', 'balance')) {
                $badTopups = DB::table('wallet_histories')
                    ->select('user_id', DB::raw('COALESCE(SUM(amount), 0) as total_amount'))
                    ->where('type', 'topup')
                    ->where('invoice_number', 'like', 'REFUND-%')
                    ->groupBy('user_id')
                    ->get();

                foreach ($badTopups as $badTopup) {
                    $userId = (int) ($badTopup->user_id ?? 0);
                    $amount = max(0, (float) ($badTopup->total_amount ?? 0));

                    if ($userId <= 0 || $amount <= 0) {
                        continue;
                    }

                    $amountSql = number_format($amount, 2, '.', '');

                    DB::table('users')
                        ->where('id', $userId)
                        ->update([
                            'balance' => DB::raw('GREATEST(COALESCE(balance, 0) - ' . $amountSql . ', 0)'),
                        ]);
                }

                DB::table('wallet_histories')
                    ->where('type', 'topup')
                    ->where('invoice_number', 'like', 'REFUND-%')
                    ->delete();
            }

            if (Schema::hasTable('deposits')) {
                DB::table('deposits')
                    ->where(function ($query) {
                        $query->where('invoice_number', 'like', 'REFUND-%')
                            ->orWhere('payment_method', 'refund')
                            ->orWhere('payment_response->purpose', 'refund_wallet');
                    })
                    ->update([
                        'payment_status' => 'refund',
                        'payment_method' => 'refund',
                    ]);
            }
        });
    }

    public function down(): void
    {
        //
    }
};
