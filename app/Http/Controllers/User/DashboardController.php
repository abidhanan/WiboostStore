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

        // Menghitung statistik transaksi sukses milik user ini
        $totalAllTime = Transaction::where('user_id', $user->id)
                            ->where('order_status', 'success')
                            ->count();

        $totalThisMonth = Transaction::where('user_id', $user->id)
                            ->where('order_status', 'success')
                            ->whereMonth('created_at', Carbon::now()->month)
                            ->whereYear('created_at', Carbon::now()->year)
                            ->count();

        return view('user.dashboard', compact('categories', 'totalAllTime', 'totalThisMonth'));
    }
}