<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Hitung total uang dari transaksi yang 'paid'
        $totalPendapatan = Transaction::where('payment_status', 'paid')->sum('amount');

        // 2. Hitung jumlah pesanan yang sudah dibayar
        $pesananSukses = Transaction::where('payment_status', 'paid')->count();

        // 3. Hitung total pelanggan (role_id = 5)
        $totalPengguna = User::where('role_id', 5)->count();

        // Lempar data ini ke tampilan (view)
        return view('admin.dashboard', compact('totalPendapatan', 'pesananSukses', 'totalPengguna'));
    }
}