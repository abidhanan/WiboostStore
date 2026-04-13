<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Product;

class FrontendController extends Controller
{
    /**
     * Menampilkan Landing Page dengan data dinamis.
     */
    public function index()
    {
        // 1. Mengambil data untuk statistik metrik real-time
        $totalUsers = User::where('role_id', 2)->count();
        $totalTransactions = Transaction::where('payment_status', 'paid')->count();
        $activeProducts = Product::where('is_active', true)->count();
        
        // 2. Mengambil semua kategori beserta jumlah produk yang ada di dalamnya
        $categories = Category::withCount(['products' => function ($query) {
            $query->where('is_active', true);
        }])->get();

        // 3. Melempar semua data ke file tampilan 'welcome.blade.php'
        return view('welcome', compact('totalUsers', 'totalTransactions', 'activeProducts', 'categories'));
    }
}
