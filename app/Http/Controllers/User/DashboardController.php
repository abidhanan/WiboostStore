<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Promo;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Mengambil HANYA kategori utama (parent_id = null)
        // Sekalian hitung jumlah sub-kategori (children) dan produk langsungnya
        $categories = Category::whereNull('parent_id')
                                ->withCount(['children', 'products'])
                                ->get();

        // 2. Mengambil semua banner promo yang statusnya aktif
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