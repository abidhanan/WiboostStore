<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionHistoryController extends Controller
{
    public function index()
    {
        // Mengambil transaksi milik user yang login, diurutkan dari terbaru
        // Menggunakan paginate() agar fungsi links() di view berjalan normal
        $transactions = Transaction::where('user_id', Auth::id())
                                    ->with('product') 
                                    ->orderBy('created_at', 'desc') 
                                    ->paginate(10); 

        return view('user.history', compact('transactions'));
    }
}