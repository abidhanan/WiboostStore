<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Deposit; 
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // 1. Total Transaksi (Pesanan) Bulan Ini
        $totalTransactionsMonth = Transaction::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        // 2. Pendapatan Bulan Ini (Hanya transaksi yang LUNAS)
        $revenueMonth = Transaction::where('payment_status', 'paid')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount');

        // 3. Total Pelanggan Terdaftar (Role ID 2 = Buyer)
        $totalUsers = User::where('role_id', 2)->count(); // <-- Diubah menjadi 2

        // 4. Ambil 5 Transaksi Paling Baru untuk preview tabel
        $recentTransactions = Transaction::with(['user', 'product'])
            ->latest()
            ->take(5)
            ->get();

        // 5. Ambil 5 Deposit Paling Baru untuk preview tabel
        $recentDeposits = collect(); 
        if (class_exists(Deposit::class)) {
            $recentDeposits = Deposit::with('user')
                ->latest()
                ->take(5)
                ->get();
        }

        return view('admin.dashboard', compact(
            'totalTransactionsMonth', 
            'revenueMonth', 
            'totalUsers', 
            'recentTransactions',
            'recentDeposits'
        ));
    }
}