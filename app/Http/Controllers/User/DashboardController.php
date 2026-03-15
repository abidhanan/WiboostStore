<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Promo; // <-- Model Promo dipanggil
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Mengambil semua kategori yang aktif beserta jumlah produknya
        $categories = Category::withCount('products')->get();

        // 2. Mengambil semua banner promo yang statusnya aktif (is_active = 1)
        $promos = Promo::where('is_active', true)->latest()->get();

        // 3. Menghitung statistik transaksi sukses milik user ini
        $totalAllTime = Transaction::where('user_id', $user->id)
                            ->where('payment_status', 'paid')
                            ->count();

        $totalThisMonth = Transaction::where('user_id', $user->id)
                            ->where('payment_status', 'paid')
                            ->whereMonth('created_at', Carbon::now()->month)
                            ->whereYear('created_at', Carbon::now()->year)
                            ->count();

        // 4. Menghitung total uang yang sudah dihabiskan user
        $totalSpent = Transaction::where('user_id', $user->id)
                            ->where('payment_status', 'paid')
                            ->sum('amount');

        // Lempar semua data ke view
        return view('user.dashboard', compact('categories', 'promos', 'totalAllTime', 'totalThisMonth', 'totalSpent'));
    }
}