<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Exception;

class WalletController extends Controller
{
    /**
     * Menampilkan halaman dompet dan riwayat top up.
     */
    public function index()
    {
        $deposits = Deposit::where('user_id', Auth::id())->latest()->get();
        return view('user.wallet.index', compact('deposits'));
    }

    /**
     * Memproses permintaan top up saldo dan membuat Snap Token Midtrans.
     */
    public function store(Request $request)
    {
        // Validasi minimal top up Rp 10.000
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        try {
            // 1. Buat tiket deposit di database dengan status unpaid
            $deposit = Deposit::create([
                'user_id' => Auth::id(),
                'invoice_number' => 'DEP-' . strtoupper(Str::random(10)),
                'amount' => $request->amount,
                'payment_status' => 'unpaid',
            ]);

            // 2. Konfigurasi Midtrans
            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            // 3. Siapkan Payload (Data Tagihan untuk Midtrans)
            $params = [
                'transaction_details' => [
                    'order_id' => $deposit->invoice_number,
                    'gross_amount' => $deposit->amount,
                ],
                'item_details' => [
                    [
                        'id' => 'TOPUP-WIBOOST',
                        'price' => $deposit->amount,
                        'quantity' => 1,
                        'name' => 'Top Up Saldo Wiboost'
                    ]
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ]
            ];

            // 4. Dapatkan Snap Token dari API Midtrans
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // 5. Arahkan pengguna ke halaman Checkout khusus Top Up
            return view('user.wallet.checkout', compact('deposit', 'snapToken'));

        } catch (Exception $e) {
            // Jika ada masalah dengan server Midtrans atau konfigurasi
            return back()->with('error', 'Gagal terhubung ke server pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Memanggil ulang halaman pembayaran untuk deposit yang berstatus unpaid.
     */
    public function pay($invoice_number)
    {
        // Cari deposit milik user yang sedang login
        $deposit = Deposit::where('invoice_number', $invoice_number)
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        // Pastikan statusnya masih unpaid
        if ($deposit->payment_status !== 'unpaid') {
            return redirect()->route('user.wallet.index')->with('error', 'Tagihan ini sudah lunas atau dibatalkan.');
        }

        try {
            // Konfigurasi Midtrans
            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $deposit->invoice_number,
                    'gross_amount' => $deposit->amount,
                ],
                'item_details' => [
                    [
                        'id' => 'TOPUP-WIBOOST',
                        'price' => $deposit->amount,
                        'quantity' => 1,
                        'name' => 'Top Up Saldo Wiboost'
                    ]
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ]
            ];

            // Midtrans cukup pintar, jika order_id sama dan belum expired, dia akan mengembalikan token yang sama
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            return view('user.wallet.checkout', compact('deposit', 'snapToken'));

        } catch (Exception $e) {
            return back()->with('error', 'Gagal memuat ulang pembayaran: ' . $e->getMessage());
        }
    }
}