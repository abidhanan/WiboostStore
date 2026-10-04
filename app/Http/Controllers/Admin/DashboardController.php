<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminReport;
use App\Models\Category;
use App\Models\Product;
use App\Models\OtpRequest;
use App\Models\Refund;
use App\Models\ResellerCommission;
use App\Models\SiteSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\ResellerCommissionService;
use App\Support\SimpleXlsx;
use App\Support\WiboostAdminContact;
use App\Support\WiboostCatalog;
use App\Support\WiboostInputRules;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $selectedMonth = (string) $request->query('month', now()->format('m'));
        $selectedYear = (string) $request->query('year', now()->format('Y'));

        if (! preg_match('/^(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $currentMonth = (int) $selectedMonth;
        $currentYear = (int) $selectedYear;

        $activeProductOrMembership = function ($query) {
            $query->where('invoice_number', 'like', 'MEM-%')
                ->orWhereHas('product', fn ($productQuery) => $productQuery->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()));
        };

        $totalTransactionsMonth = Transaction::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('order_status', '!=', 'failed')
            ->whereNull('refunded_at')
            ->doesntHave('refund')
            ->where(function ($query) {
                $query->where('payment_method', '!=', 'tripay')
                    ->orWhereNull('payment_method')
                    ->orWhereNotIn('payment_status', ['unpaid', 'pending']);
            })
            ->where($activeProductOrMembership)
            ->count();

        $revenueMonth = Transaction::where('payment_status', 'paid')
            ->where('order_status', '!=', 'failed')
            ->whereNull('refunded_at')
            ->doesntHave('refund')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where($activeProductOrMembership)
            ->sum('amount');

        $totalUsers = User::count();
        $totalAdmins = User::whereIn('role_id', User::ADMIN_ROLE_IDS)->count();
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
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->latest()
            ->take(5)
            ->get();

        $globalWalletLogs = collect();
        if (class_exists(WalletHistory::class)) {
            $globalWalletLogs = WalletHistory::with('user')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->latest()
                ->take(5)
                ->get();
        }

        $lowStockProducts = Product::where('process_type', 'account')
            ->get()
            ->filter(function ($product) {
                return $product->stock_reminder > 0 && $product->available_stock <= $product->stock_reminder;
            });

        $selectedPeriod = Carbon::createFromDate($currentYear, $currentMonth, 1);
        $selectedPeriodStart = $selectedPeriod->copy()->startOfMonth();
        $selectedPeriodEnd = $selectedPeriod->copy()->endOfMonth();
        $latestChartStart = $selectedPeriodEnd->copy()->subDays(6)->startOfDay();
        if ($latestChartStart->lt($selectedPeriodStart)) {
            $latestChartStart = $selectedPeriodStart->copy();
        }
        $defaultChartEnd = $selectedPeriod->isSameMonth(now())
            ? now()->endOfDay()
            : $selectedPeriodEnd->copy();
        $requestedChartWeek = (string) $request->query('chart_week', '');
        $chartStart = null;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedChartWeek)) {
            try {
                $chartStart = Carbon::createFromFormat('Y-m-d', $requestedChartWeek, config('app.timezone'))->startOfDay();
            } catch (\Throwable) {
                $chartStart = null;
            }
        }

        if (! $chartStart) {
            $chartStart = $defaultChartEnd->copy()->subDays(6)->startOfDay();
            if ($chartStart->lt($selectedPeriodStart)) {
                $chartStart = $selectedPeriodStart->copy();
            }
        } elseif ($chartStart->lt($selectedPeriodStart)) {
            $chartStart = $selectedPeriodStart->copy();
        } elseif ($chartStart->gt($latestChartStart)) {
            $chartStart = $latestChartStart->copy();
        }

        $chartEnd = $chartStart->copy()->addDays(6)->endOfDay();
        if ($chartEnd->gt($selectedPeriodEnd)) {
            $chartEnd = $selectedPeriodEnd->copy();
        }
        $paidTransactionsForChart = Transaction::whereBetween('created_at', [$chartStart, $chartEnd])
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'failed')
            ->whereNull('refunded_at')
            ->doesntHave('refund')
            ->where($activeProductOrMembership)
            ->get(['created_at', 'amount']);

        $newUsersForChart = User::whereIn('role_id', [2, 3, 5])
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->get(['created_at']);

        $dashboardCharts = collect(range(0, 6))->map(function (int $dayOffset) use ($paidTransactionsForChart, $newUsersForChart, $chartStart) {
            $date = $chartStart->copy()->addDays($dayOffset);
            $dateKey = $date->toDateString();

            $dailyTransactions = $paidTransactionsForChart->filter(
                fn ($transaction) => optional($transaction->created_at)->toDateString() === $dateKey
            );

            return [
                'label' => $date->format('d/m'),
                'transactions' => $dailyTransactions->count(),
                'revenue' => (int) $dailyTransactions->sum('amount'),
                'users' => $newUsersForChart->filter(
                    fn ($user) => optional($user->created_at)->toDateString() === $dateKey
                )->count(),
            ];
        })->values();
        $dashboardChartWeekStart = $chartStart->toDateString();
        $dashboardChartWeekEnd = $chartEnd->toDateString();
        $dashboardChartPrevWeek = $chartStart->copy()->subWeek()->toDateString();
        $dashboardChartNextWeek = $chartStart->copy()->addWeek()->toDateString();

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
            ->whereNull('refunded_at')
            ->doesntHave('refund')
            ->whereNotNull('product_id')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
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
        $withdrawalMonth = 0;
        $latestWithdrawals = collect();
        $refundMonth = 0;
        $latestRefunds = collect();
        $latestExpiredDurations = collect();
        $latestOtpRequests = collect();

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
                        'time' => optional($commissionTime)->format('d M Y, H:i') ?: '-',
                        'product' => $commission->product_name ?: ($commission->transaction?->display_product_name ?: '-'),
                        'reseller' => $commission->user?->name ?: '-',
                        'amount' => (float) ($commission->amount ?? 0),
                    ];
                });

            $withdrawalSummary = [
                'count' => ResellerCommission::where('type', 'withdrawal')->count(),
                'amount' => (float) ResellerCommission::where('type', 'withdrawal')->sum('amount'),
                'pending' => (float) ResellerCommission::where('type', 'withdrawal')->whereIn('status', ['pending', 'processing'])->sum('amount'),
            ];

            $withdrawalMonth = (float) ResellerCommission::where('type', 'withdrawal')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('amount');

            $latestWithdrawals = ResellerCommission::query()
                ->with('user')
                ->where('type', 'withdrawal')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->latest()
                ->take(5)
                ->get();
        }

        if (Schema::hasTable('refunds')) {
            $refundsThisMonth = Refund::query()
                ->with('transaction')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->get();

            $refundMonth = $refundsThisMonth->sum(fn (Refund $refund) => (float) $refund->manual_refund_amount);

            $latestRefunds = Refund::query()
                ->with(['transaction.user', 'transaction.product', 'admin'])
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->latest()
                ->take(5)
                ->get();
        }

        if (Schema::hasColumn('transactions', 'credential_expires_at')) {
            $latestExpiredDurations = Transaction::query()
                ->with(['user', 'product'])
                ->where('payment_status', 'paid')
                ->whereIn('order_status', ['processing', 'success'])
                ->whereNotNull('credential_expires_at')
                ->where('credential_expires_at', '<=', now())
                ->whereMonth('credential_expires_at', $currentMonth)
                ->whereYear('credential_expires_at', $currentYear)
                ->orderByDesc('credential_expires_at')
                ->take(5)
                ->get();
        }

        if (Schema::hasTable('otp_requests')) {
            $latestOtpRequests = OtpRequest::query()
                ->with(['user', 'transaction.product'])
                ->whereMonth('requested_at', $currentMonth)
                ->whereYear('requested_at', $currentYear)
                ->orderByDesc('requested_at')
                ->take(5)
                ->get();
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
            'dashboardChartWeekStart',
            'dashboardChartWeekEnd',
            'dashboardChartPrevWeek',
            'dashboardChartNextWeek',
            'commissionSummary',
            'commissionMonth',
            'refundMonth',
            'selectedMonth',
            'selectedYear',
            'latestIncomeCommissions',
            'withdrawalSummary',
            'withdrawalMonth',
            'latestWithdrawals',
            'latestReports',
            'topProductsByRootCategory',
            'latestRefunds',
            'latestExpiredDurations',
            'latestOtpRequests',
        ));
    }

    public function weeklyChart(Request $request)
    {
        return view('admin.dashboard.partials.weekly-chart', $this->weeklyChartData($request));
    }

    protected function weeklyChartData(Request $request): array
    {
        Carbon::setLocale('id');

        $selectedMonth = (string) $request->query('month', now()->format('m'));
        $selectedYear = (string) $request->query('year', now()->format('Y'));

        if (! preg_match('/^(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $currentMonth = (int) $selectedMonth;
        $currentYear = (int) $selectedYear;
        $selectedPeriod = Carbon::createFromDate($currentYear, $currentMonth, 1);
        $selectedPeriodStart = $selectedPeriod->copy()->startOfMonth();
        $selectedPeriodEnd = $selectedPeriod->copy()->endOfMonth();
        $latestChartStart = $selectedPeriodEnd->copy()->subDays(6)->startOfDay();

        if ($latestChartStart->lt($selectedPeriodStart)) {
            $latestChartStart = $selectedPeriodStart->copy();
        }

        $defaultChartEnd = $selectedPeriod->isSameMonth(now())
            ? now()->endOfDay()
            : $selectedPeriodEnd->copy();
        $requestedChartWeek = (string) $request->query('chart_week', '');
        $chartStart = null;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedChartWeek)) {
            try {
                $chartStart = Carbon::createFromFormat('Y-m-d', $requestedChartWeek, config('app.timezone'))->startOfDay();
            } catch (\Throwable) {
                $chartStart = null;
            }
        }

        if (! $chartStart) {
            $chartStart = $defaultChartEnd->copy()->subDays(6)->startOfDay();

            if ($chartStart->lt($selectedPeriodStart)) {
                $chartStart = $selectedPeriodStart->copy();
            }
        } elseif ($chartStart->lt($selectedPeriodStart)) {
            $chartStart = $selectedPeriodStart->copy();
        } elseif ($chartStart->gt($latestChartStart)) {
            $chartStart = $latestChartStart->copy();
        }

        $chartEnd = $chartStart->copy()->addDays(6)->endOfDay();
        if ($chartEnd->gt($selectedPeriodEnd)) {
            $chartEnd = $selectedPeriodEnd->copy();
        }

        $activeProductOrMembership = function ($query) {
            $query->where('invoice_number', 'like', 'MEM-%')
                ->orWhereHas('product', fn ($productQuery) => $productQuery->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()));
        };

        $paidTransactionsForChart = Transaction::whereBetween('created_at', [$chartStart, $chartEnd])
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'failed')
            ->whereNull('refunded_at')
            ->doesntHave('refund')
            ->where($activeProductOrMembership)
            ->get(['created_at', 'amount']);

        $newUsersForChart = User::whereIn('role_id', [2, 3, 5])
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->get(['created_at']);

        $dashboardCharts = collect(range(0, 6))->map(function (int $dayOffset) use ($paidTransactionsForChart, $newUsersForChart, $chartStart) {
            $date = $chartStart->copy()->addDays($dayOffset);
            $dateKey = $date->toDateString();

            $dailyTransactions = $paidTransactionsForChart->filter(
                fn ($transaction) => optional($transaction->created_at)->toDateString() === $dateKey
            );

            return [
                'label' => $date->format('d/m'),
                'transactions' => $dailyTransactions->count(),
                'revenue' => (int) $dailyTransactions->sum('amount'),
                'users' => $newUsersForChart->filter(
                    fn ($user) => optional($user->created_at)->toDateString() === $dateKey
                )->count(),
            ];
        })->values();

        return [
            'dashboardCharts' => $dashboardCharts,
            'dashboardChartWeekStart' => $chartStart->toDateString(),
            'dashboardChartWeekEnd' => $chartEnd->toDateString(),
            'dashboardChartPrevWeek' => $chartStart->copy()->subWeek()->toDateString(),
            'dashboardChartNextWeek' => $chartStart->copy()->addWeek()->toDateString(),
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
        ];
    }

    public function updateContactNumbers(Request $request)
    {
        $validated = $request->validate([
            'admin_cs_name' => ['nullable', 'string', 'max:80'],
            'admin_cs_whatsapp' => WiboostInputRules::nullableNumberRules(40),
        ], WiboostInputRules::validationMessages());

        SiteSetting::setValue('admin_cs_name', trim((string) ($validated['admin_cs_name'] ?? '')) ?: null);
        SiteSetting::setValue('admin_cs_whatsapp', self::normalizeWhatsappInput($validated['admin_cs_whatsapp'] ?? null));

        return back()->with('success', 'Data Admin CS berhasil diperbarui.');
    }

    public function exportExcel(Request $request)
    {
        Carbon::setLocale('id');

        $selectedMonth = (string) $request->query('month', now()->format('m'));
        $selectedYear = (string) $request->query('year', now()->format('Y'));

        if (! preg_match('/^(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $currentMonth = (int) $selectedMonth;
        $currentYear = (int) $selectedYear;
        $monthName = Carbon::createFromDate($currentYear, $currentMonth, 1)->translatedFormat('F Y');

        $activeProductOrMembership = function ($query) {
            $query->where('invoice_number', 'like', 'MEM-%')
                ->orWhereHas('product', fn ($productQuery) => $productQuery->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()));
        };

        $validTransactions = Transaction::query()
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'failed')
            ->whereNull('refunded_at')
            ->doesntHave('refund')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where($activeProductOrMembership);

        $totalTransactionsMonth = (clone $validTransactions)->count();
        $revenueMonth = (float) (clone $validTransactions)->sum('amount');
        $totalUsers = User::count();
        $totalAdmins = User::whereIn('role_id', User::ADMIN_ROLE_IDS)->count();
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

        $commissionMonth = 0.0;
        $withdrawalMonth = 0.0;
        if (Schema::hasTable('reseller_commissions')) {
            $commissionMonth = (float) ResellerCommission::where('type', 'income')
                ->where('status', 'success')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('amount');
            $withdrawalMonth = (float) ResellerCommission::where('type', 'withdrawal')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('amount');
        }

        $refundMonth = Schema::hasTable('refunds')
            ? (float) Refund::query()
                ->with('transaction')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->get()
                ->sum(fn (Refund $refund) => (float) $refund->manual_refund_amount)
            : 0.0;

        $reportCount = Schema::hasTable('admin_reports')
            ? AdminReport::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count()
            : 0;

        $expiredDurationCount = Schema::hasColumn('transactions', 'credential_expires_at')
            ? Transaction::query()
                ->whereNotNull('credential_expires_at')
                ->whereMonth('credential_expires_at', $currentMonth)
                ->whereYear('credential_expires_at', $currentYear)
                ->count()
            : 0;

        $otpRequestCount = Schema::hasTable('otp_requests')
            ? OtpRequest::whereMonth('requested_at', $currentMonth)->whereYear('requested_at', $currentYear)->count()
            : 0;

        $rows = [
            ['Ringkasan', 'Pendapatan Bulan Ini', 'Rp ' . number_format($revenueMonth, 0, ',', '.'), 'Pendapatan valid pada periode terpilih'],
            ['Ringkasan', 'Komisi Bulan Ini', 'Rp ' . number_format($commissionMonth, 0, ',', '.'), 'Komisi reseller sukses pada periode terpilih'],
            ['Ringkasan', 'Penarikan Bulan Ini', 'Rp ' . number_format($withdrawalMonth, 0, ',', '.'), 'Total penarikan pada periode terpilih'],
            ['Ringkasan', 'Refund Bulan Ini', 'Rp ' . number_format($refundMonth, 0, ',', '.'), 'Nominal refund pada periode terpilih'],
            ['Ringkasan', 'Transaksi Bulan Ini', number_format($totalTransactionsMonth, 0, ',', '.'), 'Transaksi paid, tidak gagal, dan belum refund'],
            ['Pengguna', 'Total Akun', number_format($totalUsers, 0, ',', '.'), 'Semua akun terdaftar'],
            ['Pengguna', 'Total Admin', number_format($totalAdmins, 0, ',', '.'), 'Super admin dan staf'],
            ['Pengguna', 'Total Member', number_format($totalMembers, 0, ',', '.'), 'Membership aktif'],
            ['Pengguna', 'Total Reseller', number_format($totalResellers, 0, ',', '.'), 'Reseller aktif'],
            ['Pengguna', 'Total Buyer', number_format($totalBuyers, 0, ',', '.'), 'User biasa dan member nonaktif'],
            ['Operasional', 'Durasi Habis', number_format($expiredDurationCount, 0, ',', '.'), 'Akses produk yang habis pada periode terpilih'],
            ['Operasional', 'Permintaan OTP', number_format($otpRequestCount, 0, ',', '.'), 'Permintaan OTP pada periode terpilih'],
            ['Laporan', 'Laporan Kendala', number_format($reportCount, 0, ',', '.'), 'Laporan masuk pada periode terpilih'],
        ];

        return SimpleXlsx::download(
            'Wiboost Store - Dashboard Admin',
            'Dashboard',
            'laporan-dashboard-' . str_replace(' ', '-', strtolower($monthName)) . '.xlsx',
            'Periode: ' . $monthName,
            ['Kategori', 'Metrik', 'Nilai', 'Keterangan'],
            $rows,
            [18, 30, 24, 62]
        );
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
