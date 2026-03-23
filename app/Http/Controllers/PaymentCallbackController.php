<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Deposit;
use App\Models\User;
use App\Models\ProductCredential;
use App\Services\OrderSosmedService;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function handleNotification(Request $request)
    {
        if ($request->header('User-Agent') == 'Midtrans') {
            header('ngrok-skip-browser-warning: true');
        }

        Log::info('Midtrans Webhook Masuk:', $request->all());
        $payload = $request->getContent();
        $notification = json_decode($payload);

        if (!$notification) return response()->json(['message' => 'Invalid payload'], 400);

        $serverKey = env('MIDTRANS_SERVER_KEY');
        $signatureKey = hash("sha512", $notification->order_id . $notification->status_code . $notification->gross_amount . $serverKey);

        if ($signatureKey !== $notification->signature_key) {
            Log::error('Midtrans Signature Mismatch. Order ID: ' . $notification->order_id);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;

        // TOP UP SALDO
        if (str_starts_with($orderId, 'DEP-')) {
            $deposit = Deposit::where('invoice_number', $orderId)->first();
            if (!$deposit) return response()->json(['message' => 'Deposit not found'], 404);

            if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
                if ($deposit->payment_status == 'unpaid') {
                    $deposit->update([
                        'payment_status' => 'paid',
                        'payment_method' => $notification->payment_type
                    ]);
                    User::find($deposit->user_id)->increment('balance', $deposit->amount);
                }
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $deposit->update(['payment_status' => 'failed']);
            }
            return response()->json(['message' => 'Deposit handled successfully']);
        }

        // TRANSAKSI PRODUK
        if (str_starts_with($orderId, 'WIB-')) {
            $transaction = Transaction::where('invoice_number', $orderId)->first();
            if (!$transaction) return response()->json(['message' => 'Transaction not found'], 404);

            if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
                if ($transaction->payment_status == 'unpaid') {
                    $transaction->update(['payment_status' => 'paid']);
                    $this->processFulfillment($transaction);
                }
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $transaction->update([
                    'payment_status' => 'failed',
                    'order_status'   => 'failed'
                ]);
            }
            return response()->json(['message' => 'Transaction callback handled successfully']);
        }

        return response()->json(['message' => 'Unknown Order ID format'], 400);
    }

    private function processFulfillment($transaction)
    {
        $product = $transaction->product;

        if ($product->process_type === 'api') {
            $transaction->update(['order_status' => 'processing']);
            $orderSosmed = new OrderSosmedService();
            $apiResponse = $orderSosmed->placeOrder($product->provider_product_id, $transaction->target_data, 1000);

            if ($apiResponse['success']) {
                $transaction->update(['order_status' => 'success']);
            } else {
                $transaction->update([
                    'order_status' => 'failed',
                    'target_notes' => 'Gagal hit API Pusat: ' . $apiResponse['message']
                ]);
            }
        } 
        elseif ($product->process_type === 'account' || $product->process_type === 'number') {
            $credential = ProductCredential::where('product_id', $product->id)
                ->where('is_active', true)
                ->whereColumn('current_usage', '<', 'max_usage')
                ->first();

            if ($credential) {
                $credential->increment('current_usage');
                
                // Merekam data ke dalam brankas JSON
                $transaction->update([
                    'order_status' => 'success',
                    'credential_data' => json_encode([
                        'email'    => ($credential->data_1 !== '-' && $credential->data_1 !== null) ? $credential->data_1 : null,
                        'password' => $credential->data_2,
                        'profile'  => $credential->data_3,
                        'pin'      => $credential->data_4,
                        'link'     => $credential->data_5,
                        'type'     => $product->process_type
                    ])
                ]);
            } else {
                $transaction->update(['order_status' => 'pending']);
            }
        } 
        elseif ($product->process_type === 'manual') {
            $transaction->update(['order_status' => 'processing']);
        }
    }
}