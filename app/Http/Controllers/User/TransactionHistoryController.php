<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionHistoryController extends Controller
{
    public function index()
    {
        // Mengambil semua transaksi milik user yang sedang login
        $transactions = Transaction::where('user_id', Auth::id())
                                    ->with('product') // Mengambil data produk terkait
                                    ->orderBy('created_at', 'desc') // Urutkan dari yang terbaru
                                    ->get();

        return view('user.history', compact('transactions'));
    }
}