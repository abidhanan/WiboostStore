<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminReport;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Support\WiboostCatalog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $totalTransactionsMonth = Transaction::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('order_status', '!=', 'failed')
            ->where(function ($query) {
                $query->where('payment_method', '!=', 'tripay')
                    ->orWhereNull('payment_method')
                    ->orWhereNotIn('payment_status', ['unpaid', 'pending']);
            })
            ->whereHas('product', fn ($query) => $query->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()))
            ->count();

        $revenueMonth = Transaction::where('payment_status', 'paid')
            ->where('order_status', '!=', 'failed')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->whereHas('product', fn ($query) => $query->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()))
            ->sum('amount');

        $totalUsers = User::where('role_id', 2)->count();

        $recentTransactions = Transaction::with(['user', 'product'])
            ->where(function ($query) {
                $query->where('payment_method', '!=', 'tripay')
                    ->orWhereNull('payment_method')
                    ->orWhereNotIn('payment_status', ['unpaid', 'pending']);
            })
            ->whereHas('product', fn ($query) => $query->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()))
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

        $lowStockProducts = Product::where('process_type', 'account')
            ->get()
            ->filter(function ($product) {
                return $product->stock_reminder > 0 && $product->available_stock <= $product->stock_reminder;
            });

        $chartStart = Carbon::now()->subDays(6)->startOfDay();
        $paidTransactionsForChart = Transaction::where('created_at', '>=', $chartStart)
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'failed')
            ->whereHas('product', fn ($query) => $query->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()))
            ->get(['created_at', 'amount']);

        $newUsersForChart = User::where('role_id', 2)
            ->where('created_at', '>=', $chartStart)
            ->get(['created_at']);

        $dashboardCharts = collect(range(6, 0))->map(function (int $daysAgo) use ($paidTransactionsForChart, $newUsersForChart) {
            $date = Carbon::now()->subDays($daysAgo);
            $dateKey = $date->toDateString();

            $dailyTransactions = $paidTransactionsForChart->filter(
                fn ($transaction) => optional($transaction->created_at)->toDateString() === $dateKey
            );

            return [
                'label' => $date->format('d'),
                'transactions' => $dailyTransactions->count(),
                'revenue' => (int) $dailyTransactions->sum('amount'),
                'users' => $newUsersForChart->filter(
                    fn ($user) => optional($user->created_at)->toDateString() === $dateKey
                )->count(),
            ];
        })->values();

        $latestReports = AdminReport::with('user')
            ->latest()
            ->take(5)
            ->get();

        $rootCategories = Category::whereNull('parent_id')
            ->whereIn('slug', WiboostCatalog::coreCategorySlugs())
            ->get()
            ->keyBy('slug');

        $paidProductSummaries = Transaction::query()
            ->select('product_id')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(amount), 0) as revenue_sum')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'failed')
            ->whereNotNull('product_id')
            ->whereHas('product', fn ($query) => $query->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()))
            ->groupBy('product_id')
            ->get();

        $productsById = Product::with('category.parent.parent.parent')
            ->whereIn('id', $paidProductSummaries->pluck('product_id')->filter()->all())
            ->get()
            ->keyBy('id');

        $topProductsByRootCategory = collect(WiboostCatalog::coreCategorySlugs())
            ->map(function (string $rootSlug) use ($rootCategories, $paidProductSummaries, $productsById) {
                $categoryProductSummaries = $paidProductSummaries->filter(function ($summary) use ($rootSlug, $productsById) {
                    $product = $productsById->get($summary->product_id);

                    return $this->resolveRootCategorySlug($product?->category) === $rootSlug;
                });

                $topSummary = $categoryProductSummaries
                    ->sortByDesc(fn ($summary) => (int) $summary->orders_count)
                    ->first();

                return [
                    'slug' => $rootSlug,
                    'category_name' => $rootCategories->get($rootSlug)?->name ?: str($rootSlug)->replace('-', ' ')->title()->toString(),
                    'product' => $topSummary ? $productsById->get($topSummary->product_id) : null,
                    'orders' => $topSummary ? (int) $topSummary->orders_count : 0,
                    'revenue' => $topSummary ? (float) $topSummary->revenue_sum : 0,
                ];
            })
            ->values();

        return view('admin.dashboard', compact(
            'totalTransactionsMonth',
            'revenueMonth',
            'totalUsers',
            'recentTransactions',
            'globalWalletLogs',
            'lowStockProducts',
            'dashboardCharts',
            'latestReports',
            'topProductsByRootCategory'
        ));
    }

    protected function resolveRootCategorySlug(?Category $category): ?string
    {
        if (! $category) {
            return null;
        }

        $category->loadMissing('parent.parent.parent');

        while ($category->parent) {
            $category = $category->parent;
        }

        return $category->slug;
    }
}
