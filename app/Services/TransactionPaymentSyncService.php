<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionPaymentSyncService
{
    public function __construct(
        private readonly XoftwarePaymentService $paymentService,
        private readonly OrderFulfillmentService $orderFulfillmentService,
    ) {
    }

    public function sync(Transaction $pendingTransaction): void
    {
        if (! in_array($pendingTransaction->payment_status, ['unpaid', 'pending', 'paid'], true)) {
            return;
        }

        if ($this->expireIfPastDue($pendingTransaction)) {
            return;
        }

        if ($pendingTransaction->payment_status === 'paid') {
            if ($this->activateMembershipIfNeeded($pendingTransaction)) {
                return;
            }

            $this->orderFulfillmentService->handlePaidTransaction(
                $pendingTransaction->fresh(['product', 'user'])
            );

            return;
        }

        try {
            $detail = $this->paymentService->transactionDetail($pendingTransaction->invoice_number);
        } catch (Throwable) {
            return;
        }

        $data = data_get($detail, 'data');
        $event = is_array($data) ? array_replace($detail, $data) : $detail;
        $status = strtoupper((string) ($event['status'] ?? ''));
        $paymentStatus = strtoupper((string) ($event['payment_status'] ?? ''));

        $isPaid = in_array($status, ['SUCCESS', 'PAID', 'SUCCEEDED', 'SETTLEMENT', 'CAPTURE'], true)
            || in_array($paymentStatus, ['SUCCESS', 'PAID', 'SUCCEEDED', 'SETTLEMENT', 'CAPTURE'], true);
        $isFailed = in_array($status, ['FAILED', 'EXPIRED', 'REFUND', 'CANCELED', 'CANCELLED'], true)
            || in_array($paymentStatus, ['FAILED', 'EXPIRED', 'REFUND', 'CANCELED', 'CANCELLED'], true);

        if ($isPaid) {
            $transactionId = DB::transaction(function () use ($pendingTransaction, $event, $detail) {
                $transaction = Transaction::query()
                    ->lockForUpdate()
                    ->find($pendingTransaction->id);

                if (! $transaction) {
                    return null;
                }

                $paidAmount = (int) round((float) (
                    $event['total_amount']
                    ?? $event['paid_amount']
                    ?? $event['amount']
                    ?? $transaction->amount
                ));
                $paymentMethod = (string) (
                    $event['channel_code']
                    ?? $event['payment_method']
                    ?? $event['payment_method_code']
                    ?? 'QRIS'
                );

                $transaction->update([
                    'payment_status' => 'paid',
                    'payment_method' => $paymentMethod,
                    'paid_amount' => $paidAmount > 0 ? $paidAmount : $transaction->amount,
                    'response_data' => array_merge($transaction->response_data ?? [], [
                        'payment_gateway' => 'xoftware',
                        'xoftware_detail_sync' => $detail,
                        'payment_synced_at' => now()->toDateTimeString(),
                    ]),
                ]);

                return $transaction->id;
            });

            if ($transactionId) {
                $transaction = Transaction::with(['product', 'user'])->find($transactionId);

                if ($transaction) {
                    if ($this->activateMembershipIfNeeded($transaction)) {
                        return;
                    }

                    $this->orderFulfillmentService->handlePaidTransaction($transaction);
                }
            }

            return;
        }

        if ($isFailed) {
            DB::transaction(function () use ($pendingTransaction, $detail, $status, $paymentStatus) {
                $transaction = Transaction::query()
                    ->lockForUpdate()
                    ->find($pendingTransaction->id);

                if (! $transaction || $transaction->payment_status === 'paid') {
                    return;
                }

                $finalStatus = $status ?: $paymentStatus ?: 'FAILED';

                $transaction->update([
                    'payment_status' => 'failed',
                    'order_status' => 'failed',
                    'target_notes' => 'Pembayaran QRIS berstatus ' . $finalStatus . '.',
                    'response_data' => array_merge($transaction->response_data ?? [], [
                        'payment_gateway' => 'xoftware',
                        'xoftware_detail_sync' => $detail,
                        'payment_synced_at' => now()->toDateTimeString(),
                    ]),
                ]);
            });
        }

        if (! $isPaid && ! $isFailed) {
            $pendingTransaction->update([
                'response_data' => array_merge($pendingTransaction->response_data ?? [], [
                    'payment_gateway' => 'xoftware',
                    'xoftware_detail_sync' => $detail,
                    'payment_synced_at' => now()->toDateTimeString(),
                ]),
            ]);
        }
    }

    public function expireIfPastDue(Transaction $pendingTransaction): bool
    {
        if (! in_array($pendingTransaction->payment_status, ['unpaid', 'pending'], true)
            || ! $pendingTransaction->usesQrisPayment()
            || ! $pendingTransaction->qris_expires_at
            || $pendingTransaction->qris_expires_at->isFuture()) {
            return false;
        }

        return DB::transaction(function () use ($pendingTransaction) {
            $transaction = Transaction::query()
                ->lockForUpdate()
                ->find($pendingTransaction->id);

            if (! $transaction
                || ! in_array($transaction->payment_status, ['unpaid', 'pending'], true)
                || ! $transaction->usesQrisPayment()
                || ! $transaction->qris_expires_at
                || $transaction->qris_expires_at->isFuture()) {
                return false;
            }

            $transaction->update([
                'payment_status' => 'failed',
                'order_status' => 'failed',
                'target_notes' => 'Pembayaran QRIS dibatalkan karena masa berlaku telah habis.',
                'response_data' => array_merge($transaction->response_data ?? [], [
                    'payment_cancel_reason' => 'expired',
                    'payment_expired_at' => $transaction->qris_expires_at->toDateTimeString(),
                ]),
            ]);

            return true;
        });
    }

    protected function activateMembershipIfNeeded(Transaction $transaction): bool
    {
        if (! $transaction->isMembershipPayment()) {
            return false;
        }

        DB::transaction(function () use ($transaction) {
            $lockedTransaction = Transaction::query()
                ->with('user')
                ->lockForUpdate()
                ->find($transaction->id);

            if (! $lockedTransaction
                || $lockedTransaction->payment_status !== 'paid'
                || ! $lockedTransaction->isMembershipPayment()
                || $lockedTransaction->membershipActivated()) {
                return;
            }

            $lockedTransaction->user?->activateMembership((int) data_get($lockedTransaction->response_data, 'membership_days', 30));
            $lockedTransaction->forceFill([
                'order_status' => 'success',
                'target_notes' => 'Membership Wiboost 30 hari berhasil aktif.',
                'response_data' => array_merge($lockedTransaction->response_data ?? [], [
                    'purpose' => 'membership',
                    'membership_activated_at' => now()->toDateTimeString(),
                ]),
            ])->save();
        });

        return true;
    }
}
