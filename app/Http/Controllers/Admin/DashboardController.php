<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $totalTransactionsMonth = Transaction::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $revenueMonth = Transaction::where('payment_status', 'paid')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount');

        $totalUsers = User::where('role_id', 2)->count();

        $recentTransactions = Transaction::with(['user', 'product'])
            ->latest()
            ->take(5)
            ->get();

        $globalWalletLogs = collect();
        if (class_exists(WalletHistory::class)) {
            $globalWalletLogs = WalletHistory::with('user')
                ->latest()
                ->take(5)
                ->get();
        }

        $lowStockProducts = Product::whereIn('process_type', ['account', 'number'])
            ->get()
            ->filter(function ($product) {
                return $product->stock_reminder > 0 && $product->available_stock <= $product->stock_reminder;
            });

        return view('admin.dashboard', compact(
            'totalTransactionsMonth',
            'revenueMonth',
            'totalUsers',
            'recentTransactions',
            'globalWalletLogs',
            'lowStockProducts'
        ));
    }
}
