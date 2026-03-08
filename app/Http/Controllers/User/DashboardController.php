<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Mengambil semua kategori yang aktif
        $categories = Category::all();

        // Menghitung statistik transaksi sukses milik user ini (patokan: sudah dibayar/paid)
        $totalAllTime = Transaction::where('user_id', $user->id)
                            ->where('payment_status', 'paid')
                            ->count();

        $totalThisMonth = Transaction::where('user_id', $user->id)
                            ->where('payment_status', 'paid')
                            ->whereMonth('created_at', Carbon::now()->month)
                            ->whereYear('created_at', Carbon::now()->year)
                            ->count();

        // Tambahan: Menghitung total uang yang sudah dihabiskan user
        $totalSpent = Transaction::where('user_id', $user->id)
                            ->where('payment_status', 'paid')
                            ->sum('amount');

        return view('user.dashboard', compact('categories', 'totalAllTime', 'totalThisMonth', 'totalSpent'));
    }
}