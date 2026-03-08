<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        // Mengambil SEMUA transaksi dari semua user, urut dari yang paling baru
        // Kita gunakan 'with' untuk memanggil relasi data User dan Product sekaligus
        $transactions = Transaction::with(['user', 'product'])
                                   ->latest()
                                   ->get();

        return view('admin.transactions.index', compact('transactions'));
    }

    // Nanti kita bisa tambahkan fungsi reports() di sini untuk export Excel/PDF
}