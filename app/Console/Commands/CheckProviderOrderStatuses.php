<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\OrderFulfillmentService;
use Illuminate\Console\Command;

class CheckProviderOrderStatuses extends Command
{
    protected $signature = 'wiboost:check-provider-orders {--limit=50 : Jumlah maksimal pesanan yang dicek dalam satu run}';

    protected $description = 'Cek status pesanan API yang masih processing ke provider Digiflazz dan OrderSosmed';

    public function handle(OrderFulfillmentService $orderFulfillmentService): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $transactions = Transaction::query()
            ->with(['product', 'user'])
            ->where('payment_status', 'paid')
            ->where('order_status', 'processing')
            ->whereHas('product', function ($query) {
                $query->where('process_type', 'api')
                    ->whereIn('provider_source', ['digiflazz', 'ordersosmed']);
            })
            ->oldest('updated_at')
            ->limit($limit)
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('Tidak ada pesanan provider yang perlu dicek.');

            return self::SUCCESS;
        }

        $summary = [
            'success' => 0,
            'failed' => 0,
            'processing' => 0,
            'skipped' => 0,
        ];

        foreach ($transactions as $transaction) {
            $result = $orderFulfillmentService->syncProviderStatus($transaction);
            $summary[$result] = ($summary[$result] ?? 0) + 1;
        }

        $this->info(sprintf(
            'Cek status selesai. Sukses: %d, gagal/refund: %d, masih proses: %d, dilewati: %d.',
            $summary['success'],
            $summary['failed'],
            $summary['processing'],
            collect($summary)->except(['success', 'failed', 'processing'])->sum()
        ));

        return self::SUCCESS;
    }
}
