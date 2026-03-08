<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function index()
    {
        // Menampilkan riwayat top up milik user
        $deposits = Deposit::where('user_id', Auth::id())->latest()->get();
        return view('user.wallet.index', compact('deposits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000', // Minimal top up Rp 10.000
        ]);

        // Membuat tiket deposit baru
        $deposit = Deposit::create([
            'user_id' => Auth::id(),
            'invoice_number' => 'DEP-' . strtoupper(Str::random(10)),
            'amount' => $request->amount,
            'payment_status' => 'unpaid',
        ]);

        // Karena akun Midtrans/Digiflazz kamu sedang tahap konfirmasi, 
        // kita arahkan ke halaman instruksi manual terlebih dahulu.
        return redirect()->route('user.wallet.index')->with('success', 'Tiket deposit berhasil dibuat! Silakan lakukan pembayaran.');
    }
}