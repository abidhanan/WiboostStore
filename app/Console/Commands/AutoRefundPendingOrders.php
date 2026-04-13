<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\OrderFulfillmentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoRefundPendingOrders extends Command
{
    protected $signature = 'wiboost:auto-refund';

    protected $description = 'Refund otomatis untuk pesanan lunas namun pending lebih dari 24 jam';

    public function handle(OrderFulfillmentService $orderFulfillmentService): int
    {
        $transactions = Transaction::where('payment_status', 'paid')
            ->where('order_status', 'pending')
            ->where('updated_at', '<=', Carbon::now()->subHours(24))
            ->with('product', 'user')
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('Tidak ada transaksi yang perlu auto-refund.');

            return self::SUCCESS;
        }

        $refundedCount = 0;

        foreach ($transactions as $transaction) {
            $orderFulfillmentService->markAsFailed(
                $transaction,
                'Otomatis refund: pesanan masih pending lebih dari 24 jam. Saldo telah dikembalikan ke akun pelanggan.'
            );

            $refundedCount++;
            Log::info("AUTO-REFUND: Invoice {$transaction->invoice_number} berhasil diproses.");
        }

        $this->info("Auto-refund selesai. {$refundedCount} transaksi dipindahkan ke status gagal dan direfund.");

        return self::SUCCESS;
    }
}
