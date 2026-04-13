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
