<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;

class CleanupUnpaidTripayOrders extends Command
{
    protected $signature = 'wiboost:cleanup-unpaid-tripay-orders
        {--execute : Jalankan penghapusan. Tanpa opsi ini hanya preview}
        {--older-than=0 : Hanya hapus invoice yang lebih lama dari jumlah menit ini}';

    protected $description = 'Hapus invoice order Tripay yang masih unpaid/pending supaya tidak masuk riwayat pesanan.';

    public function handle(): int
    {
        $olderThanMinutes = max(0, (int) $this->option('older-than'));

        $query = Transaction::query()
            ->where('payment_method', 'tripay')
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->where('order_status', 'pending');

        if ($olderThanMinutes > 0) {
            $query->where('created_at', '<=', now()->subMinutes($olderThanMinutes));
        }

        $previewRows = (clone $query)
            ->latest()
            ->limit(20)
            ->get(['invoice_number', 'amount', 'payment_status', 'created_at']);

        $count = (clone $query)->count();

        $this->table(
            ['Invoice', 'Nominal', 'Status Bayar', 'Dibuat'],
            $previewRows->map(fn (Transaction $transaction) => [
                $transaction->invoice_number,
                'Rp ' . number_format((float) $transaction->amount, 0, ',', '.'),
                $transaction->payment_status,
                optional($transaction->created_at)->format('Y-m-d H:i:s'),
            ])->all()
        );

        $this->info("Total invoice Tripay unpaid/pending yang cocok: {$count}");

        if (! $this->option('execute')) {
            $this->warn('Preview saja. Tambahkan --execute untuk benar-benar menghapus.');

            return self::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("Berhasil menghapus {$deleted} invoice Tripay unpaid/pending.");

        return self::SUCCESS;
    }
}
