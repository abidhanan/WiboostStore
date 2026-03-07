<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
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

        // 3. Ambil payload dari Midtrans
        $payload = $request->getContent();
        $notification = json_decode($payload);

        if (!$notification) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // 4. Validasi Signature Key (Keamanan anti-hacker/pemalsuan struk)
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $signatureKey = hash("sha512", $notification->order_id . $notification->status_code . $notification->gross_amount . $serverKey);

        if ($signatureKey !== $notification->signature_key) {
            Log::error('Midtrans Signature Mismatch. Order ID: ' . $notification->order_id);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 5. Cari data transaksi di database Wiboost Store
        $transaction = Transaction::where('invoice_number', $notification->order_id)->first();

        // Jika transaksi tidak ditemukan (misal Midtrans mengirim ID testing)
        if (!$transaction) {
            Log::warning('Midtrans Webhook: Transaksi tidak ditemukan - ' . $notification->order_id);
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // 6. LOGIKA UTAMA: Update Status Pembayaran & Pengiriman Otomatis
        $transactionStatus = $notification->transaction_status;

        if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
            
            // --- PEMBAYARAN LUNAS ---
            // Ubah status pembayaran jadi paid, status order jadi processing
            $transaction->update([
                'payment_status' => 'paid',
                'order_status'   => 'processing'
            ]);

            Log::info("Pembayaran Lunas untuk Invoice: {$transaction->invoice_number}. Memulai proses fulfillment...");

            // --- OTOMATISASI PENGIRIMAN KE PROVIDER (ORDERSOSMED) ---
            // Cek apakah ini produk kategori Suntik Sosmed (Misal Category ID = 1)
            if ($transaction->product->category_id == 1) {
                $orderSosmed = new OrderSosmedService();
                
                // TODO: Nanti 150 ini diganti dengan $transaction->product->provider_product_id
                $providerServiceId = $transaction->product->provider_product_id;
                // TODO: Nanti 1000 ini diganti dengan qty aktual (misal kita tambahkan kolom qty/jumlah)
                $quantity = 1000; 

                // Tembak API Pusat
                $apiResponse = $orderSosmed->placeOrder(
                    $providerServiceId, 
                    $transaction->target_data, 
                    $quantity
                );

                // Cek respon dari pusat
                if ($apiResponse['success']) {
                    $transaction->update(['order_status' => 'success']);
                    Log::info("Fulfillment Sukses Invoice {$transaction->invoice_number}. Provider ID: " . $apiResponse['provider_order_id']);
                } else {
                    $transaction->update([
                        'order_status' => 'failed',
                        'target_notes' => 'Gagal hit API Pusat: ' . $apiResponse['message']
                    ]);
                    Log::error("Fulfillment Gagal Invoice {$transaction->invoice_number}. Alasan: " . $apiResponse['message']);
                }
            } else {
                // Jika bukan kategori 1 (Misal Top Up Game), biarkan statusnya processing (Nanti kita tambahkan Digiflazz)
                Log::info("Menunggu integrasi provider lain untuk Kategori ID: " . $transaction->product->category_id);
            }

        } elseif ($transactionStatus == 'pending') {
            
            // --- PEMBAYARAN PENDING ---
            $transaction->update(['payment_status' => 'unpaid']);
            
        } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            
            // --- PEMBAYARAN GAGAL/KADALUARSA ---
            $transaction->update([
                'payment_status' => 'failed',
                'order_status'   => 'failed'
            ]);
            Log::info("Pembayaran Gagal/Cancel untuk Invoice: {$transaction->invoice_number}");
            
        }

        // 7. Berikan respon sukses ke server Midtrans agar mereka berhenti mengirim ping
        return response()->json(['message' => 'Callback handled successfully']);
    }
}