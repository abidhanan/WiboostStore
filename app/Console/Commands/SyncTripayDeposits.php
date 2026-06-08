<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Models\User;
use App\Services\TripayDepositSyncService;
use App\Services\XoftwarePaymentService;
use Illuminate\Console\Command;

class SyncTripayDeposits extends Command
{
    protected $signature = 'wiboost:sync-tripay-deposits {--user_id=} {--email=} {--invoice=} {--reference=} {--limit=100}';

    protected $description = 'Sinkronkan deposit QRIS/Xoftware pending dengan status pembayaran provider.';

    public function handle(TripayDepositSyncService $depositSyncService, XoftwarePaymentService $paymentService): int
    {
        if ($reference = $this->option('reference')) {
            $detail = $paymentService->transactionDetail((string) $reference);
            $invoice = (string) (
                data_get($detail, 'data.ref_id')
                ?: data_get($detail, 'data.merchant_ref')
                ?: data_get($detail, 'data.order_id')
            );

            if ($invoice === '') {
                $this->error('Reference ditemukan, tetapi ref_id/invoice kosong.');

                return self::FAILURE;
            }

            $deposit = Deposit::where('invoice_number', $invoice)->first();

            if (! $deposit) {
                $this->error("Deposit {$invoice} tidak ditemukan.");

                return self::FAILURE;
            }

            $depositSyncService->syncDepositFromProvider($deposit);
            $depositSyncService->creditPaidDepositsForUser((int) $deposit->user_id, 200);

            $this->info("Sinkron deposit {$invoice} selesai.");

            return self::SUCCESS;
        }

        if ($invoice = $this->option('invoice')) {
            $deposit = Deposit::where('invoice_number', (string) $invoice)->first();

            if (! $deposit) {
                $this->error('Deposit dengan invoice tersebut tidak ditemukan.');

                return self::FAILURE;
            }

            $depositSyncService->syncDepositFromProvider($deposit);
            $depositSyncService->creditPaidDepositsForUser((int) $deposit->user_id, 200);

            $this->info("Sinkron deposit {$deposit->invoice_number} selesai.");

            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));

        if ($userId = $this->option('user_id')) {
            $depositSyncService->syncForUser((int) $userId, $limit);

            $this->info("Sinkron deposit QRIS selesai untuk user ID {$userId}.");

            return self::SUCCESS;
        }

        if ($email = $this->option('email')) {
            $user = User::where('email', (string) $email)->first();

            if (! $user) {
                $this->error('User dengan email tersebut tidak ditemukan.');

                return self::FAILURE;
            }

            $depositSyncService->syncForUser((int) $user->id, $limit);

            $this->info("Sinkron deposit QRIS selesai untuk {$user->email}.");

            return self::SUCCESS;
        }

        $query = Deposit::query()
            ->where(function ($depositQuery) {
                $depositQuery
                    ->where(function ($pendingQuery) {
                        $pendingQuery->where(function ($statusQuery) {
                            $statusQuery->whereNull('payment_status')
                                ->orWhereIn('payment_status', ['unpaid', 'pending']);
                        })->whereNotNull('tripay_reference');
                    })
                    ->orWhere('payment_status', 'paid');
            });

        $userIds = $query
            ->latest()
            ->limit($limit)
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            $this->info('Tidak ada deposit pending QRIS yang perlu disinkronkan.');

            return self::SUCCESS;
        }

        foreach ($userIds as $userId) {
            $depositSyncService->syncForUser((int) $userId, $limit);
        }

        $this->info('Sinkron deposit QRIS selesai untuk ' . $userIds->count() . ' user.');

        return self::SUCCESS;
    }
}
