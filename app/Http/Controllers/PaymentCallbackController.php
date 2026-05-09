<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\DiscordWebhookService;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\Request;

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
        $realAmount = (int) round((float) data_get($payload, 'total_amount', data_get($payload, 'amount', 0)));
        $paymentMethod = (string) (
            data_get($payload, 'payment_method')
            ?: data_get($payload, 'payment_method_code')
            ?: 'tripay'
        );

        if (str_starts_with($merchantRef, 'DEP-')) {
            $deposit = Deposit::where('invoice_number', $merchantRef)->first();

            if (! $deposit) {
                return response()->json(['success' => false, 'message' => 'Deposit not found'], 404);
            }

            if ($status === 'PAID' && $deposit->payment_status === 'unpaid') {
                $deposit->update([
                    'payment_status' => 'paid',
                    'payment_method' => $paymentMethod,
                    'amount' => $realAmount,
                ]);

                $user = User::find($deposit->user_id);

                if ($user) {
                    $user->increment('balance', $realAmount);
                }

                $historyExists = WalletHistory::where('invoice_number', $deposit->invoice_number)
                    ->where('type', 'topup')
                    ->exists();

                if (! $historyExists) {
                    WalletHistory::create([
                        'user_id' => $deposit->user_id,
                        'type' => 'topup',
                        'amount' => $realAmount,
                        'description' => 'Top Up Saldo via ' . strtoupper($paymentMethod),
                        'invoice_number' => $deposit->invoice_number,
                    ]);
                }

                $discordWebhookService->sendDepositAlert(
                    $deposit->fresh('user'),
                    'Deposit berhasil dibayar',
                    'Saldo user sudah otomatis ditambahkan.'
                );
            } elseif (in_array($status, ['FAILED', 'EXPIRED', 'REFUND'], true) && $deposit->payment_status === 'unpaid') {
                $deposit->update(['payment_status' => 'failed']);
            }

            return response()->json(['success' => true]);
        }

        if (str_starts_with($merchantRef, 'WIB-')) {
            $transaction = Transaction::where('invoice_number', $merchantRef)->first();

            if (! $transaction) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            if ($status === 'PAID' && $transaction->payment_status === 'unpaid') {
                $transaction->update([
                    'payment_status' => 'paid',
                    'payment_method' => $paymentMethod,
                    'amount' => $realAmount,
                    'response_data' => array_merge($transaction->response_data ?? [], [
                        'payment_gateway' => 'tripay',
                        'tripay_reference' => data_get($payload, 'reference'),
                        'tripay_callback' => $payload,
                    ]),
                ]);

                $orderFulfillmentService->handlePaidTransaction($transaction->fresh(['product', 'user']));
            } elseif (in_array($status, ['FAILED', 'EXPIRED', 'REFUND'], true) && $transaction->payment_status === 'unpaid') {
                $transaction->update([
                    'payment_status' => 'failed',
                    'order_status' => 'failed',
                    'target_notes' => 'Pembayaran dibatalkan atau kedaluwarsa di Tripay.',
                    'response_data' => array_merge($transaction->response_data ?? [], [
                        'payment_gateway' => 'tripay',
                        'tripay_reference' => data_get($payload, 'reference'),
                        'tripay_callback' => $payload,
                    ]),
                ]);
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
        $realAmount = (int) round((float) $notification->gross_amount);

        if (str_starts_with($orderId, 'DEP-')) {
            $deposit = Deposit::where('invoice_number', $orderId)->first();

            if (! $deposit) {
                return response()->json(['message' => 'Deposit not found'], 404);
            }

            if (in_array($transactionStatus, ['settlement', 'capture'], true) && $deposit->payment_status === 'unpaid') {
                $deposit->update([
                    'payment_status' => 'paid',
                    'payment_method' => $notification->payment_type,
                    'amount' => $realAmount,
                ]);

                $user = User::find($deposit->user_id);

                if ($user) {
                    $user->increment('balance', $realAmount);
                }

                $historyExists = WalletHistory::where('invoice_number', $deposit->invoice_number)
                    ->where('type', 'topup')
                    ->exists();

                if (! $historyExists) {
                    WalletHistory::create([
                        'user_id' => $deposit->user_id,
                        'type' => 'topup',
                        'amount' => $realAmount,
                        'description' => 'Top Up Saldo via ' . strtoupper($notification->payment_type),
                        'invoice_number' => $deposit->invoice_number,
                    ]);
                }

                $discordWebhookService->sendDepositAlert(
                    $deposit->fresh('user'),
                    'Deposit berhasil dibayar',
                    'Saldo user sudah otomatis ditambahkan.'
                );
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
                $deposit->update(['payment_status' => 'failed']);
            }

            return response()->json(['message' => 'Deposit handled successfully']);
        }

        if (str_starts_with($orderId, 'WIB-')) {
            $transaction = Transaction::where('invoice_number', $orderId)->first();

            if (! $transaction) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            if (in_array($transactionStatus, ['settlement', 'capture'], true) && $transaction->payment_status === 'unpaid') {
                $transaction->update([
                    'payment_status' => 'paid',
                    'payment_method' => $notification->payment_type,
                    'amount' => $realAmount,
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
