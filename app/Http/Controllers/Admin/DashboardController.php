<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. Pendapatan Hari Ini (Hanya yang LUNAS)
        $revenueToday = Transaction::where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('amount');

        // 2. Total Pendapatan Keseluruhan
        $totalRevenue = Transaction::where('payment_status', 'paid')->sum('amount');

        // 3. Pesanan Menggantung (Pending / Processing)
        $pendingOrders = Transaction::whereIn('order_status', ['pending', 'processing'])->count();

        // 4. Total Pelanggan Terdaftar (Asumsi Role ID 5 adalah Customer)
        $totalUsers = User::where('role_id', 5)->count();

        // 5. Ambil 5 Transaksi Paling Baru untuk preview tabel
        $recentTransactions = Transaction::with(['user', 'product'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'revenueToday', 
            'totalRevenue', 
            'pendingOrders', 
            'totalUsers', 
            'recentTransactions'
        ));
    }
}