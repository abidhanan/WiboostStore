<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\User;
use App\Models\WalletHistory;
use Exception;
use Illuminate\Support\Facades\DB;

class TripayDepositSyncService
{
    public function __construct(private readonly XoftwarePaymentService $paymentService)
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
            $detail = $this->paymentService->transactionDetail($tripayReference);
        } catch (Exception $exception) {
            return;
        }

        $data = data_get($detail, 'data');
        $event = is_array($data) ? array_replace($detail, $data) : $detail;
        $status = strtoupper((string) data_get($event, 'status', ''));
        $paymentStatus = strtoupper((string) data_get($event, 'payment_status', ''));

        if (in_array($status, ['SUCCESS', 'PAID', 'SUCCEEDED', 'SETTLEMENT', 'CAPTURE'], true)
            || in_array($paymentStatus, ['SUCCESS', 'PAID', 'SUCCEEDED', 'SETTLEMENT', 'CAPTURE'], true)) {
            $this->markDepositAsPaid($pendingDeposit, $event, $detail);
        }

        if (in_array($status, ['FAILED', 'EXPIRED', 'REFUND', 'CANCELED', 'CANCELLED'], true)
            || in_array($paymentStatus, ['FAILED', 'EXPIRED', 'REFUND', 'CANCELED', 'CANCELLED'], true)) {
            $pendingDeposit->update([
                'payment_status' => 'failed',
                'payment_response' => array_merge($pendingDeposit->payment_response ?? [], [
                    'payment_gateway' => 'xoftware',
                    'xoftware_detail_sync' => $detail,
                    'synced_at' => now()->toDateTimeString(),
                ]),
            ]);
        }

        if (! in_array($status, ['SUCCESS', 'PAID', 'SUCCEEDED', 'SETTLEMENT', 'CAPTURE', 'FAILED', 'EXPIRED', 'REFUND', 'CANCELED', 'CANCELLED'], true)
            && ! in_array($paymentStatus, ['SUCCESS', 'PAID', 'SUCCEEDED', 'SETTLEMENT', 'CAPTURE', 'FAILED', 'EXPIRED', 'REFUND', 'CANCELED', 'CANCELLED'], true)) {
            $pendingDeposit->update([
                'payment_response' => array_merge($pendingDeposit->payment_response ?? [], [
                    'payment_gateway' => 'xoftware',
                    'xoftware_detail_sync' => $detail,
                    'tripay_detail_sync' => $detail,
                    'synced_at' => now()->toDateTimeString(),
                ]),
            ]);
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
                ?: data_get($data, 'channel_code')
                ?: data_get($data, 'payment_method_code')
                ?: 'qris'
            );
            $tripayReference = (string) (
                data_get($data, 'ref_id')
                ?: data_get($data, 'merchant_ref')
                ?: data_get($data, 'reference')
                ?: data_get($data, 'transaction_id')
                ?: $deposit->tripay_reference
            );

            $deposit->update([
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'paid_amount' => $paidAmount > 0 ? $paidAmount : $topupAmount,
                'tripay_reference' => filled($tripayReference) ? $tripayReference : $deposit->tripay_reference,
                'payment_response' => array_merge($deposit->payment_response ?? [], [
                    'payment_gateway' => 'xoftware',
                    'xoftware_reference' => filled($tripayReference) ? $tripayReference : $deposit->tripay_reference,
                    'xoftware_detail_sync' => $detail,
                    'tripay_detail_sync' => $detail,
                    'synced_at' => now()->toDateTimeString(),
                ]),
            ]);

            $user = User::whereKey($deposit->user_id)
                ->lockForUpdate()
                ->first();

            if ($user) {
                if ($deposit->isMembershipPayment()) {
                    if (! $deposit->membershipActivated()) {
                        $user->activateMembership((int) data_get($deposit->payment_response, 'membership_days', 30));
                        $deposit->markMembershipActivated();
                    }
                } else {
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

                    if ($walletHistory->wasRecentlyCreated) {
                        $user->increment('balance', $topupAmount);
                    }
                }
            }
        });
    }

    protected function resolveTripayReference(Deposit $deposit): ?string
    {
        if (filled($deposit->invoice_number)) {
            return (string) $deposit->invoice_number;
        }

        if (filled($deposit->tripay_reference)) {
            return (string) $deposit->tripay_reference;
        }

        $reference = data_get($deposit->payment_response, 'data.reference')
            ?: data_get($deposit->payment_response, 'data.ref_id')
            ?: data_get($deposit->payment_response, 'data.merchant_ref')
            ?: data_get($deposit->payment_response, 'data.transaction_id')
            ?: data_get($deposit->payment_response, 'reference')
            ?: data_get($deposit->payment_response, 'xoftware_reference')
            ?: data_get($deposit->payment_response, 'xoftware_callback.ref_id')
            ?: data_get($deposit->payment_response, 'xoftware_callback.order_id')
            ?: data_get($deposit->payment_response, 'xoftware_callback.transaction_id')
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

                $user = User::whereKey($deposit->user_id)
                    ->lockForUpdate()
                    ->first();

                if ($user) {
                    if ($deposit->isMembershipPayment()) {
                        if (! $deposit->membershipActivated()) {
                            $user->activateMembership((int) data_get($deposit->payment_response, 'membership_days', 30));
                            $deposit->markMembershipActivated();
                        }
                    } else {
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

                        if ($walletHistory->wasRecentlyCreated) {
                            $user->increment('balance', $topupAmount);
                        }
                    }
                }
            });
        }
    }
}
