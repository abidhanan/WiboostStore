<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Product;
use App\Support\WiboostCatalog;

class FrontendController extends Controller
{
    /**
     * Menampilkan Landing Page dengan data dinamis.
     */
    public function index()
    {
        $catalogSlugs = WiboostCatalog::coreCategorySlugs();
        $categoryOrderSql = WiboostCatalog::categoryOrderSql();

        // 1. Mengambil data untuk statistik metrik real-time
        $totalUsers = User::where('role_id', 2)->count();
        $totalTransactions = Transaction::where('payment_status', 'paid')->count();
        $activeProducts = Product::where('is_active', true)->count();
        
        // 2. Mengambil semua kategori beserta jumlah produk yang ada di dalamnya
        $categories = Category::whereIn('slug', $catalogSlugs)
            ->withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderByRaw($categoryOrderSql)
            ->get();

        $recentFomoPurchases = Transaction::with('product')
            ->where('payment_status', 'paid')
            ->whereIn('order_status', ['processing', 'success'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Transaction $transaction) => [
                'name' => 'Member Wiboost',
                'product' => $transaction->product?->name ?? 'Produk Wiboost',
            ])
            ->values();

        // 3. Melempar semua data ke file tampilan 'welcome.blade.php'
        return view('welcome', compact('totalUsers', 'totalTransactions', 'activeProducts', 'categories', 'recentFomoPurchases'));
    }
}
