<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Product; // Pastikan Model Product dipanggil

class FrontendController extends Controller
{
    public function index()
    {
        // Mengambil data untuk statistik real-time di Landing Page
        $totalUsers = User::where('role_id', 5)->count();
        $totalTransactions = Transaction::where('payment_status', 'paid')->count();
        
        // Tambahan: Menghitung total layanan/produk yang aktif dijual
        $activeProducts = Product::where('is_active', true)->count();
        
        // Mengambil semua kategori untuk ditampilkan di menu
        $categories = Category::all();

        // Mengirim data tersebut ke file tampilan (view) 'welcome.blade.php'
        return view('welcome', compact('totalUsers', 'totalTransactions', 'activeProducts', 'categories'));
    }
}