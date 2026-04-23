<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Promo;
use App\Models\Tutorial;
use App\Support\WiboostCatalog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $catalogSlugs = WiboostCatalog::coreCategorySlugs();
        $categoryOrderSql = WiboostCatalog::categoryOrderSql();

        $categories = Category::whereNull('parent_id')
            ->whereIn('slug', $catalogSlugs)
            ->withCount(['children', 'products'])
            ->orderByRaw($categoryOrderSql)
            ->get();

        $promos = Promo::where('is_active', true)->latest()->get();

        $totalThisMonth = Transaction::where('user_id', $user->id)
                            ->where('payment_status', 'paid')
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->count();

        $totalSpent = Transaction::where('user_id', $user->id)
                            ->where('payment_status', 'paid')
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->sum('amount');

        $tutorials = Tutorial::where('is_active', true)->latest()->get();
        
        // Ambil daftar kategori unik untuk menu Filter di Dashboard User
        $tutorialCategories = Tutorial::where('is_active', true)->select('category')->distinct()->pluck('category');

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

        return view('user.dashboard', compact(
            'categories', 
            'promos', 
            'totalThisMonth', 
            'totalSpent', 
            'tutorials',
            'tutorialCategories',
            'recentFomoPurchases'
        ));
    }
}
