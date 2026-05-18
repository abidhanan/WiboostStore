<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\User;
use App\Models\WalletHistory;
use Exception;
use Illuminate\Support\Facades\DB;

class TripayDepositSyncService
{
    public function __construct(private readonly TripayService $tripayService)
    {
    }

    public function syncForUser(int $userId, int $limit = 100): void
    {
        $pendingDeposits = Deposit::where('user_id', $userId)
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhereIn('payment_status', ['unpaid', 'pending']);
            })
            ->latest()
            ->take($limit)
            ->get();

        foreach ($pendingDeposits as $pendingDeposit) {
            $this->syncDepositFromProvider($pendingDeposit);
        }

        $this->creditPaidDepositsForUser($userId, $limit);
    }

    public function syncDepositFromProvider(Deposit $pendingDeposit): void
    {
        $tripayReference = $this->resolveTripayReference($pendingDeposit);

        if (! filled($tripayReference)) {
            return;
        }

        try {
            $detail = $this->tripayService->transactionDetail($tripayReference);
        } catch (Exception $exception) {
            return;
        }

        $data = data_get($detail, 'data', []);
        $status = strtoupper((string) data_get($data, 'status', ''));

        if ($status === 'PAID') {
            $this->markDepositAsPaid($pendingDeposit, $data, $detail);
        }

        if (in_array($status, ['FAILED', 'EXPIRED', 'REFUND'], true)) {
            $pendingDeposit->delete();
        }
    }

    protected function markDepositAsPaid(Deposit $pendingDeposit, array $data, array $detail): void
    {
        DB::transaction(function () use ($pendingDeposit, $data, $detail) {
            $deposit = Deposit::whereKey($pendingDeposit->id)
                ->lockForUpdate()
                ->first();

            if (! $deposit || ! in_array($deposit->payment_status, [null, 'unpaid', 'pending'], true)) {
                return;
            }

            $topupAmount = (float) $deposit->amount;
            $paidAmount = (int) round((float) data_get($data, 'total_amount', data_get($data, 'amount', $topupAmount)));
            $paymentMethod = (string) (
                data_get($data, 'payment_method')
                ?: data_get($data, 'payment_method_code')
                ?: 'qris'
            );
            $tripayReference = (string) (data_get($data, 'reference') ?: $deposit->tripay_reference);

            $deposit->update([
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'paid_amount' => $paidAmount > 0 ? $paidAmount : $topupAmount,
                'tripay_reference' => filled($tripayReference) ? $tripayReference : $deposit->tripay_reference,
                'payment_response' => array_merge($deposit->payment_response ?? [], [
                    'tripay_detail_sync' => $detail,
                    'synced_at' => now()->toDateTimeString(),
                ]),
            ]);

            $walletHistory = WalletHistory::firstOrCreate(
                [
                    'invoice_number' => $deposit->invoice_number,
                    'type' => 'topup',
                ],
                [
                    'user_id' => $deposit->user_id,
                    'amount' => $topupAmount,
                    'description' => 'Top Up Saldo via ' . strtoupper($paymentMethod),
                ]
            );

            $user = User::whereKey($deposit->user_id)
                ->lockForUpdate()
                ->first();

            if ($user && $walletHistory->wasRecentlyCreated) {
                $user->increment('balance', $topupAmount);
            }
        });
    }

    protected function resolveTripayReference(Deposit $deposit): ?string
    {
        if (filled($deposit->tripay_reference)) {
            return (string) $deposit->tripay_reference;
        }

        $reference = data_get($deposit->payment_response, 'data.reference')
            ?: data_get($deposit->payment_response, 'reference')
            ?: data_get($deposit->payment_response, 'tripay_reference')
            ?: data_get($deposit->payment_response, 'tripay_callback.reference');

        if (! filled($reference)) {
            return null;
        }

        $deposit->forceFill(['tripay_reference' => (string) $reference])->save();

        return (string) $reference;
    }

    public function creditPaidDepositsForUser(int $userId, int $limit = 100): void
    {
        $paidDeposits = Deposit::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->latest()
            ->take($limit)
            ->get();

        foreach ($paidDeposits as $paidDeposit) {
            DB::transaction(function () use ($paidDeposit) {
                $deposit = Deposit::whereKey($paidDeposit->id)
                    ->lockForUpdate()
                    ->first();

                if (! $deposit || $deposit->payment_status !== 'paid') {
                    return;
                }

                $topupAmount = (float) $deposit->amount;
                $paymentMethod = (string) ($deposit->payment_method ?: 'qris');

                $walletHistory = WalletHistory::firstOrCreate(
                    [
                        'invoice_number' => $deposit->invoice_number,
                        'type' => 'topup',
                    ],
                    [
                        'user_id' => $deposit->user_id,
                        'amount' => $topupAmount,
                        'description' => 'Top Up Saldo via ' . strtoupper($paymentMethod),
                    ]
                );

                $user = User::whereKey($deposit->user_id)
                    ->lockForUpdate()
                    ->first();

                if ($user && $walletHistory->wasRecentlyCreated) {
                    $user->increment('balance', $topupAmount);
                }
            });
        }
    }
}
