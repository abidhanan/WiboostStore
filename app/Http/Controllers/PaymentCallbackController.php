<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Deposit;
use App\Models\User;
use App\Services\OrderSosmedService;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    /**
     * Menangani Webhook/Notifikasi dari Midtrans.
     */
    public function handleNotification(Request $request)
    {
        // 1. Bypass halaman peringatan Ngrok (Khusus tahap development lokal)
        if ($request->header('User-Agent') == 'Midtrans') {
            header('ngrok-skip-browser-warning: true');
        }

        // 2. Catat semua data yang masuk untuk keperluan audit & debugging
        Log::info('Midtrans Webhook Masuk:', $request->all());

        $payload = $request->getContent();
        $notification = json_decode($payload);

        if (!$notification) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // 3. Validasi Signature Key (Keamanan anti-hacker)
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $signatureKey = hash("sha512", $notification->order_id . $notification->status_code . $notification->gross_amount . $serverKey);

        if ($signatureKey !== $notification->signature_key) {
            Log::error('Midtrans Signature Mismatch. Order ID: ' . $notification->order_id);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;

        // =================================================================
        // SKENARIO A: PEMBAYARAN TOP UP SALDO (Wiboost Wallet)
        // =================================================================
        if (str_starts_with($orderId, 'DEP-')) {
            $deposit = Deposit::where('invoice_number', $orderId)->first();

            if (!$deposit) {
                return response()->json(['message' => 'Deposit not found'], 404);
            }

            if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
                if ($deposit->payment_status == 'unpaid') {
                    // Update status deposit jadi lunas
                    $deposit->update([
                        'payment_status' => 'paid',
                        'payment_method' => $notification->payment_type
                    ]);

                    // Tambahkan saldo ke user
                    $user = User::find($deposit->user_id);
                    $user->increment('balance', $deposit->amount);

                    Log::info('Deposit Saldo Sukses: ' . $deposit->invoice_number);
                }
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $deposit->update(['payment_status' => 'failed']);
            }

            return response()->json(['message' => 'Deposit callback handled successfully']);
        }

        // =================================================================
        // SKENARIO B: PEMBAYARAN PRODUK & LAYANAN (Transaksi Utama)
        // =================================================================
        if (str_starts_with($orderId, 'WIB-')) {
            $transaction = Transaction::where('invoice_number', $orderId)->first();

            if (!$transaction) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
                if ($transaction->payment_status == 'unpaid') {
                    
                    // Ubah status pembayaran
                    $transaction->update([
                        'payment_status' => 'paid',
                        'order_status'   => 'processing'
                    ]);

                    Log::info("Pembayaran Lunas untuk Invoice: {$transaction->invoice_number}. Memulai proses fulfillment...");

                    // Otomatisasi Provider (Suntik Sosmed)
                    if ($transaction->product->category_id == 1) {
                        $orderSosmed = new OrderSosmedService();
                        
                        $providerServiceId = $transaction->product->provider_product_id;
                        $quantity = 1000; // Sesuaikan dengan kuantitas aslinya nanti

                        $apiResponse = $orderSosmed->placeOrder(
                            $providerServiceId, 
                            $transaction->target_data, 
                            $quantity
                        );

                        if ($apiResponse['success']) {
                            $transaction->update(['order_status' => 'success']);
                            Log::info("Fulfillment Sukses Invoice {$transaction->invoice_number}");
                        } else {
                            $transaction->update([
                                'order_status' => 'failed',
                                'target_notes' => 'Gagal hit API Pusat: ' . $apiResponse['message']
                            ]);
                            Log::error("Fulfillment Gagal Invoice {$transaction->invoice_number}");
                        }
                    } else {
                        Log::info("Menunggu integrasi provider lain untuk Kategori ID: " . $transaction->product->category_id);
                    }
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
}