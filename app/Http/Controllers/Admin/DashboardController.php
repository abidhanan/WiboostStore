<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminReport;
use App\Models\Category;
use App\Models\Product;
use App\Models\Refund;
use App\Models\ResellerCommission;
use App\Models\SiteSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\ResellerCommissionService;
use App\Support\WiboostAdminContact;
use App\Support\WiboostCatalog;
use App\Support\WiboostInputRules;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $activeProductOrMembership = function ($query) {
            $query->where('invoice_number', 'like', 'MEM-%')
                ->orWhereHas('product', fn ($productQuery) => $productQuery->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()));
        };

        $totalTransactionsMonth = Transaction::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('order_status', '!=', 'failed')
            ->where(function ($query) {
                $query->where('payment_method', '!=', 'tripay')
                    ->orWhereNull('payment_method')
                    ->orWhereNotIn('payment_status', ['unpaid', 'pending']);
            })
            ->where($activeProductOrMembership)
            ->count();

        $revenueMonth = Transaction::where('payment_status', 'paid')
            ->where('order_status', '!=', 'failed')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where($activeProductOrMembership)
            ->sum('amount');

        $totalUsers = User::count();
        $totalAdmins = User::whereIn('role_id', [1, 4, 6, 7, 8])->count();
        $totalMembers = User::where('membership_expires_at', '>', now())->count();
        $totalResellers = User::where('role_id', 3)
            ->where(function ($query) {
                $query->whereNull('membership_expires_at')
                    ->orWhere('membership_expires_at', '<=', now());
            })
            ->count();
        $totalBuyers = User::whereIn('role_id', [2, 5])
            ->where(function ($query) {
                $query->whereNull('membership_expires_at')
                    ->orWhere('membership_expires_at', '<=', now());
            })
            ->count();

        $recentTransactions = Transaction::with(['user', 'product'])
            ->where(function ($query) {
                $query->where('payment_method', '!=', 'tripay')
                    ->orWhereNull('payment_method')
                    ->orWhereNotIn('payment_status', ['unpaid', 'pending']);
            })
            ->where($activeProductOrMembership)
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
            ->where($activeProductOrMembership)
            ->get(['created_at', 'amount']);

        $newUsersForChart = User::whereIn('role_id', [2, 3, 5])
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

        $commissionSummary = ['count' => 0, 'amount' => 0, 'pending' => 0];
        $commissionMonth = 0;
        $latestIncomeCommissions = collect();
        $withdrawalSummary = ['count' => 0, 'amount' => 0, 'pending' => 0];
        $refundMonth = 0;

        if (Schema::hasTable('reseller_commissions')) {
            $commissionService = app(ResellerCommissionService::class);
            $commissionService->syncRecentMissing();
            $commissionStartDate = $commissionService->commissionStartDate();

            $commissionSummary = [
                'count' => ResellerCommission::where('type', 'income')
                    ->whereHas('transaction', fn ($query) => $query->where('created_at', '>=', $commissionStartDate))
                    ->count(),
                'amount' => (float) ResellerCommission::where('type', 'income')
                    ->whereHas('transaction', fn ($query) => $query->where('created_at', '>=', $commissionStartDate))
                    ->sum('amount'),
                'pending' => (float) ResellerCommission::where('type', 'income')
                    ->where('status', 'pending')
                    ->whereHas('transaction', fn ($query) => $query->where('created_at', '>=', $commissionStartDate))
                    ->sum('amount'),
            ];

            $commissionMonth = (float) ResellerCommission::where('type', 'income')
                ->where('status', 'success')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('amount');

            $latestIncomeCommissions = ResellerCommission::query()
                ->with(['user', 'transaction.product'])
                ->where('type', 'income')
                ->whereHas('transaction', fn ($query) => $query->where('created_at', '>=', $commissionStartDate))
                ->orderByDesc(Transaction::select('created_at')->whereColumn('transactions.id', 'reseller_commissions.transaction_id')->limit(1))
                ->orderByDesc('created_at')
                ->take(5)
                ->get()
                ->map(function (ResellerCommission $commission) {
                    $commissionTime = $commission->transaction?->created_at ?: $commission->created_at;

                    return [
                        'invoice' => $commission->transaction?->invoice_number ?: '-',
                        'time' => optional($commissionTime)->diffForHumans() ?: '-',
                        'product' => $commission->product_name ?: ($commission->transaction?->product?->name ?: '-'),
                        'reseller' => $commission->user?->name ?: '-',
                        'amount' => (float) ($commission->amount ?? 0),
                    ];
                });

            $withdrawalSummary = [
                'count' => ResellerCommission::where('type', 'withdrawal')->count(),
                'amount' => (float) ResellerCommission::where('type', 'withdrawal')->sum('amount'),
                'pending' => (float) ResellerCommission::where('type', 'withdrawal')->whereIn('status', ['pending', 'processing'])->sum('amount'),
            ];
        }

        if (Schema::hasTable('refunds')) {
            $refundMonth = Refund::query()
                ->with('transaction')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->get()
                ->sum(fn (Refund $refund) => (float) ($refund->transaction?->display_total_payable ?? 0));
        }

        $adminCsWhatsapp = WiboostAdminContact::csWhatsappNumber();
        $adminOtpWhatsapp = WiboostAdminContact::otpWhatsappNumber();

        return view('admin.dashboard', compact(
            'totalTransactionsMonth',
            'revenueMonth',
            'totalUsers',
            'totalAdmins',
            'totalBuyers',
            'totalResellers',
            'totalMembers',
            'recentTransactions',
            'globalWalletLogs',
            'lowStockProducts',
            'dashboardCharts',
            'commissionSummary',
            'commissionMonth',
            'refundMonth',
            'latestIncomeCommissions',
            'withdrawalSummary',
            'latestReports',
            'topProductsByRootCategory',
        ));
    }

    public function updateContactNumbers(Request $request)
    {
        $validated = $request->validate([
            'admin_cs_whatsapp' => WiboostInputRules::nullableNumberRules(40),
            'admin_otp_whatsapp' => WiboostInputRules::nullableNumberRules(40),
        ], WiboostInputRules::validationMessages());

        SiteSetting::setValue('admin_cs_whatsapp', self::normalizeWhatsappInput($validated['admin_cs_whatsapp'] ?? null));
        SiteSetting::setValue('admin_otp_whatsapp', self::normalizeWhatsappInput($validated['admin_otp_whatsapp'] ?? null));

        return back()->with('success', 'Nomor admin CS dan OTP berhasil diperbarui.');
    }

    protected static function normalizeWhatsappInput(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
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
