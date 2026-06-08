<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use Illuminate\Console\Command;

class CleanupUnpaidTripayDeposits extends Command
{
    protected $signature = 'wiboost:cleanup-unpaid-tripay-deposits
        {--execute : Jalankan penghapusan. Tanpa opsi ini hanya preview}
        {--older-than=0 : Hanya hapus deposit yang lebih lama dari jumlah menit ini}';

    protected $description = 'Hapus deposit QRIS unpaid/pending yang tidak jadi checkout supaya tidak terlihat dobel.';

    public function handle(): int
    {
        $olderThanMinutes = max(0, (int) $this->option('older-than'));

        $query = Deposit::query()
            ->whereIn('payment_status', ['unpaid', 'pending']);

        if ($olderThanMinutes > 0) {
            $query->where('created_at', '<=', now()->subMinutes($olderThanMinutes));
        }

        $previewRows = (clone $query)
            ->latest()
            ->limit(20)
            ->get(['invoice_number', 'amount', 'payment_status', 'payment_method', 'created_at']);

        $count = (clone $query)->count();

        $this->table(
            ['Invoice', 'Nominal', 'Status', 'Metode', 'Dibuat'],
            $previewRows->map(fn (Deposit $deposit) => [
                $deposit->invoice_number,
                'Rp ' . number_format((float) $deposit->amount, 0, ',', '.'),
                $deposit->payment_status,
                $deposit->payment_method ?: '-',
                optional($deposit->created_at)->format('Y-m-d H:i:s'),
            ])->all()
        );

        $this->info("Total deposit unpaid/pending yang cocok: {$count}");

        if (! $this->option('execute')) {
            $this->warn('Preview saja. Tambahkan --execute untuk benar-benar menghapus.');

            return self::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("Berhasil menghapus {$deleted} deposit unpaid/pending.");

        return self::SUCCESS;
    }
}
