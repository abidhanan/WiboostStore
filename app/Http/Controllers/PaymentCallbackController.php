<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function handleNotification(Request $request)
    {
        // 1. Ambil data notifikasi dari Midtrans
        $payload = $request->getContent();
        $notification = json_decode($payload);

        // 2. Validasi Signature Key (Keamanan agar tidak bisa dipalsukan)
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $signatureKey = hash("sha512", $notification->order_id . $notification->status_code . $notification->gross_amount . $serverKey);

        if ($signatureKey !== $notification->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 3. Cari transaksi di database berdasarkan Invoice
        $transaction = Transaction::where('invoice_number', $notification->order_id)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // 4. Logika Update Status
        $transactionStatus = $notification->transaction_status;
        $type = $notification->payment_type;

        if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
            $transaction->update([
                'payment_status' => 'paid',
                'order_status'   => 'processing' // Langsung otomatis diproses!
            ]);
            // Di sini kamu bisa menambahkan fungsi KIRIM PRODUK OTOMATIS nantinya
        } elseif ($transactionStatus == 'pending') {
            $transaction->update(['payment_status' => 'unpaid']);
        } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            $transaction->update(['payment_status' => 'failed', 'order_status' => 'failed']);
        }

        return response()->json(['message' => 'Callback handled successfully']);
    }
}