<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory; // <-- Import
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoRefundPendingOrders extends Command
{
    protected $signature = 'wiboost:auto-refund';
    protected $description = 'Refund otomatis untuk pesanan LUNAS namun PENDING lebih dari 1x24 Jam';

    public function handle()
    {
        $transactions = Transaction::where('payment_status', 'paid')
            ->where('order_status', 'pending')
            ->where('updated_at', '<=', Carbon::now()->subHours(24))
            ->get();

        foreach ($transactions as $trx) {
            $user = User::find($trx->user_id);
            if ($user) {
                $user->increment('balance', $trx->amount);
                $trx->update([
                    'order_status' => 'failed',
                    'target_notes' => 'Otomatis Refund: Stok kosong melebihi 1x24 Jam. Saldo telah dikembalikan ke akun Anda.'
                ]);

                // CATAT LOG REFUND (+)
                WalletHistory::create([
                    'user_id' => $user->id,
                    'type' => 'refund',
                    'amount' => $trx->amount,
                    'description' => 'Refund Otomatis (Pending > 24 Jam): ' . $trx->product->name,
                    'invoice_number' => $trx->invoice_number,
                ]);

                Log::info("AUTO-REFUND: Invoice {$trx->invoice_number} berhasil direfund.");
            }
        }
    }
}