<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\WalletHistory;
use App\Services\MidtransService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function index()
    {
        $deposits = Deposit::where('user_id', Auth::id())->latest()->get();

        return view('user.wallet.index', compact('deposits'));
    }

    public function store(Request $request, MidtransService $midtransService)
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

            $snapToken = $midtransService->getDepositSnapToken($deposit->fresh('user'), Auth::user());

            return view('user.wallet.checkout', compact('deposit', 'snapToken'));
        } catch (Exception $e) {
            return back()->with('error', 'Gagal terhubung ke server pembayaran: ' . $e->getMessage());
        }
    }

    public function pay($invoice_number, MidtransService $midtransService)
    {
        $deposit = Deposit::where('invoice_number', $invoice_number)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($deposit->payment_status !== 'unpaid') {
            return redirect()->route('user.wallet.index')->with('error', 'Tagihan ini sudah lunas atau dibatalkan.');
        }

        try {
            $snapToken = $midtransService->getDepositSnapToken($deposit->fresh('user'), Auth::user());

            return view('user.wallet.checkout', compact('deposit', 'snapToken'));
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memuat ulang pembayaran: ' . $e->getMessage());
        }
    }

    public function exchangePoints()
    {
        $user = Auth::user();

        if ($user->points < 5) {
            return back()->with('error', 'Poin kamu belum cukup. Kumpulkan minimal 5 poin.');
        }

        $user->decrement('points', 5);
        $user->increment('balance', 1000);

        WalletHistory::create([
            'user_id' => $user->id,
            'type' => 'poin',
            'amount' => 1000,
            'description' => 'Bonus penukaran 5 poin loyalty',
            'invoice_number' => 'POIN-' . strtoupper(Str::random(8)),
        ]);

        return back()->with('success', 'Selamat, 5 poin berhasil ditukar menjadi saldo Rp 1.000.');
    }
}
