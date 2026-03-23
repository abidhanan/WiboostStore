<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoRefundPendingOrders extends Command
{
    // Nama perintah untuk dieksekusi
    protected $signature = 'wiboost:auto-refund';

    // Deskripsi perintah
    protected $description = 'Refund otomatis untuk pesanan LUNAS namun PENDING lebih dari 1x24 Jam';

    public function handle()
    {
        // Cari transaksi: sudah lunas (paid), status pesanan (pending), usia sejak update terakhir > 24 jam
        $transactions = Transaction::where('payment_status', 'paid')
            ->where('order_status', 'pending')
            ->where('updated_at', '<=', Carbon::now()->subHours(24))
            ->get();

        $count = 0;
        $totalRefund = 0;

        foreach ($transactions as $trx) {
            $user = User::find($trx->user_id);
            
            if ($user) {
                // 1. Kembalikan saldo ke dompet pengguna
                $user->increment('balance', $trx->amount);

                // 2. Ubah status transaksi menjadi failed & tambahkan catatan
                $trx->update([
                    'order_status' => 'failed',
                    'target_notes' => 'Otomatis Refund: Stok kosong melebihi 1x24 Jam. Saldo telah dikembalikan ke akun Anda.'
                ]);

                Log::info("AUTO-REFUND: Invoice {$trx->invoice_number} dikembalikan ke Saldo User ID {$user->id} (Rp {$trx->amount})");
                
                $count++;
                $totalRefund += $trx->amount;
            }
        }

        $this->info("Berhasil memproses refund untuk $count pesanan. Total dana dikembalikan: Rp $totalRefund");
    }
}