<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\DiscordWebhookService;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentCallbackController extends Controller
{
    public function handleTripayCallback(
        Request $request,
        OrderFulfillmentService $orderFulfillmentService,
        DiscordWebhookService $discordWebhookService
    ) {
        $rawPayload = $request->getContent();
        $callbackSignature = (string) $request->header('X-Callback-Signature', '');
        $expectedSignature = hash_hmac('sha256', $rawPayload, (string) config('tripay.private_key'));

        if (! hash_equals($expectedSignature, $callbackSignature)) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
        }

        if ((string) $request->header('X-Callback-Event') !== 'payment_status') {
            return response()->json(['success' => false, 'message' => 'Unrecognized callback event'], 400);
        }

        $payload = json_decode($rawPayload, true);

        if (! is_array($payload)) {
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 400);
        }

        $merchantRef = (string) data_get($payload, 'merchant_ref', '');
        $status = strtoupper((string) data_get($payload, 'status', ''));
        $paidAmount = (int) round((float) data_get($payload, 'total_amount', data_get($payload, 'amount', 0)));
        $paymentMethod = (string) (
            data_get($payload, 'payment_method')
            ?: data_get($payload, 'payment_method_code')
            ?: 'tripay'
        );

        if (str_starts_with($merchantRef, 'DEP-')) {
            $result = DB::transaction(function () use ($merchantRef, $status, $paidAmount, $paymentMethod, $payload) {
                $deposit = Deposit::where('invoice_number', $merchantRef)
                    ->lockForUpdate()
                    ->first();

                if (! $deposit) {
                    return ['found' => false];
                }

                if ($status === 'PAID' && in_array($deposit->payment_status, [null, 'unpaid', 'pending'], true)) {
                    $topupAmount = (float) $deposit->amount;
                    $actualPaidAmount = $paidAmount > 0 ? $paidAmount : $topupAmount;

                    $deposit->update([
                        'payment_status' => 'paid',
                        'payment_method' => $paymentMethod,
                        'paid_amount' => $actualPaidAmount,
                        'tripay_reference' => data_get($payload, 'reference') ?: $deposit->tripay_reference,
                        'payment_response' => array_merge($deposit->payment_response ?? [], [
                            'tripay_callback' => $payload,
                            'callback_synced_at' => now()->toDateTimeString(),
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

                    return ['found' => true, 'paid' => true, 'deposit_id' => $deposit->id];
                }

                if (in_array($status, ['FAILED', 'EXPIRED', 'REFUND'], true) && in_array($deposit->payment_status, [null, 'unpaid', 'pending'], true)) {
                    $deposit->delete();
                }

                return ['found' => true, 'paid' => false];
            });

            if (! $result['found']) {
                return response()->json(['success' => false, 'message' => 'Deposit not found'], 404);
            }

            if ($result['paid'] ?? false) {
                $discordWebhookService->sendDepositAlert(
                    Deposit::with('user')->find($result['deposit_id']),
                    'Deposit berhasil dibayar',
                    'Saldo user sudah otomatis ditambahkan.'
                );
            }

            return response()->json(['success' => true]);
        }

        if (str_starts_with($merchantRef, 'WIB-')) {
            $result = DB::transaction(function () use ($merchantRef, $status, $paidAmount, $paymentMethod, $payload) {
                $transaction = Transaction::where('invoice_number', $merchantRef)
                    ->lockForUpdate()
                    ->first();

                if (! $transaction) {
                    return ['found' => false];
                }

                if ($status === 'PAID' && in_array($transaction->payment_status, ['unpaid', 'pending'], true)) {
                    $transaction->update([
                        'payment_status' => 'paid',
                        'payment_method' => $paymentMethod,
                        'amount' => $paidAmount > 0 ? $paidAmount : $transaction->amount,
                        'response_data' => array_merge($transaction->response_data ?? [], [
                            'payment_gateway' => 'tripay',
                            'tripay_reference' => data_get($payload, 'reference'),
                            'tripay_callback' => $payload,
                        ]),
                    ]);

                    return ['found' => true, 'paid' => true, 'transaction_id' => $transaction->id];
                }

                if (in_array($status, ['FAILED', 'EXPIRED', 'REFUND'], true) && in_array($transaction->payment_status, ['unpaid', 'pending'], true)) {
                    $transaction->delete();
                }

                return ['found' => true, 'paid' => false];
            });

            if (! $result['found']) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            if ($result['paid'] ?? false) {
                $transaction = Transaction::with(['product', 'user'])->find($result['transaction_id']);
                $orderFulfillmentService->handlePaidTransaction($transaction->fresh(['product', 'user']));
            }

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Unknown merchant_ref format'], 400);
    }

    public function handleNotification(
        Request $request,
        OrderFulfillmentService $orderFulfillmentService,
        DiscordWebhookService $discordWebhookService
    ) {
        $notification = json_decode($request->getContent());

        if (! $notification) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $serverKey = (string) config('midtrans.server_key');
        $signatureKey = hash(
            'sha512',
            $notification->order_id . $notification->status_code . $notification->gross_amount . $serverKey
        );

        if ($signatureKey !== $notification->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;
        $paidAmount = (int) round((float) $notification->gross_amount);

        if (str_starts_with($orderId, 'DEP-')) {
            $result = DB::transaction(function () use ($orderId, $transactionStatus, $paidAmount, $notification) {
                $deposit = Deposit::where('invoice_number', $orderId)
                    ->lockForUpdate()
                    ->first();

                if (! $deposit) {
                    return ['found' => false];
                }

                if (in_array($transactionStatus, ['settlement', 'capture'], true) && in_array($deposit->payment_status, ['unpaid', 'pending'], true)) {
                    $topupAmount = (float) $deposit->amount;
                    $actualPaidAmount = $paidAmount > 0 ? $paidAmount : $topupAmount;

                    $deposit->update([
                        'payment_status' => 'paid',
                        'payment_method' => $notification->payment_type,
                        'paid_amount' => $actualPaidAmount,
                    ]);

                    $walletHistory = WalletHistory::firstOrCreate(
                        [
                            'invoice_number' => $deposit->invoice_number,
                            'type' => 'topup',
                        ],
                        [
                            'user_id' => $deposit->user_id,
                            'amount' => $topupAmount,
                            'description' => 'Top Up Saldo via ' . strtoupper($notification->payment_type),
                        ]
                    );

                    $user = User::whereKey($deposit->user_id)
                        ->lockForUpdate()
                        ->first();

                    if ($user && $walletHistory->wasRecentlyCreated) {
                        $user->increment('balance', $topupAmount);
                    }

                    return ['found' => true, 'paid' => true, 'deposit_id' => $deposit->id];
                }

                if (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true) && in_array($deposit->payment_status, ['unpaid', 'pending'], true)) {
                    $deposit->delete();
                }

                return ['found' => true, 'paid' => false];
            });

            if (! $result['found']) {
                return response()->json(['message' => 'Deposit not found'], 404);
            }

            if ($result['paid'] ?? false) {
                $discordWebhookService->sendDepositAlert(
                    Deposit::with('user')->find($result['deposit_id']),
                    'Deposit berhasil dibayar',
                    'Saldo user sudah otomatis ditambahkan.'
                );
            }

            return response()->json(['message' => 'Deposit handled successfully']);
        }

        if (str_starts_with($orderId, 'WIB-')) {
            $transaction = Transaction::where('invoice_number', $orderId)->first();

            if (! $transaction) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            if (in_array($transactionStatus, ['settlement', 'capture'], true) && in_array($transaction->payment_status, ['unpaid', 'pending'], true)) {
                $transaction->update([
                    'payment_status' => 'paid',
                    'payment_method' => $notification->payment_type,
                    'amount' => $paidAmount,
                ]);

                $orderFulfillmentService->handlePaidTransaction($transaction->fresh(['product', 'user']));
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
                $transaction->update([
                    'payment_status' => 'failed',
                    'order_status' => 'failed',
                    'target_notes' => 'Pembayaran dibatalkan atau kedaluwarsa di Midtrans.',
                ]);
            }

            return response()->json(['message' => 'Transaction callback handled successfully']);
        }

        return response()->json(['message' => 'Unknown Order ID format'], 400);
    }
}
