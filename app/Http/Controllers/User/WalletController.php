<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Exception;

class WalletController extends Controller
{
    public function index()
    {
        $deposits = Deposit::where('user_id', Auth::id())->latest()->get();
        return view('user.wallet.index', compact('deposits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        try {
            $deposit = Deposit::create([
                'user_id' => Auth::id(),
                'invoice_number' => 'DEP-' . strtoupper(Str::random(10)),
                'amount' => $request->amount,
                'payment_status' => 'unpaid',
            ]);

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

            $snapToken = \Midtrans\Snap::getSnapToken($params);
            return view('user.wallet.checkout', compact('deposit', 'snapToken'));

        } catch (Exception $e) {
            return back()->with('error', 'Gagal terhubung ke server pembayaran: ' . $e->getMessage());
        }
    }

    public function pay($invoice_number)
    {
        $deposit = Deposit::where('invoice_number', $invoice_number)
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        if ($deposit->payment_status !== 'unpaid') {
            return redirect()->route('user.wallet.index')->with('error', 'Tagihan ini sudah lunas atau dibatalkan.');
        }

        try {
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

            $snapToken = \Midtrans\Snap::getSnapToken($params);
            return view('user.wallet.checkout', compact('deposit', 'snapToken'));

        } catch (Exception $e) {
            return back()->with('error', 'Gagal memuat ulang pembayaran: ' . $e->getMessage());
        }
    }

    // --- FUNGSI BARU: TUKAR POIN ---
    public function exchangePoints()
    {
        $user = Auth::user();

        if ($user->points < 5) {
            return back()->with('error', 'Poin kamu belum cukup. Kumpulkan minimal 5 poin!');
        }

        $user->decrement('points', 5);
        $user->increment('balance', 1000);

        WalletHistory::create([
            'user_id' => $user->id,
            'type' => 'topup',
            'amount' => 1000,
            'description' => 'Bonus Penukaran 5 Poin Loyalty ⭐',
            'invoice_number' => 'POIN-' . strtoupper(Str::random(8)),
        ]);

        return back()->with('success', 'Selamat! 5 Poin berhasil ditukar menjadi Saldo Rp 1.000 🥳');
    }
}