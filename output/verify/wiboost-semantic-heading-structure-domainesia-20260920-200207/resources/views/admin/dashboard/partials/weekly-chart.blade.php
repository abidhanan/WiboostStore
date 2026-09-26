@php
    \Carbon\Carbon::setLocale('id');

    $chartPoints = collect($dashboardCharts ?? [])->values();
    if ($chartPoints->isEmpty()) {
        $chartPoints = collect([['label' => '-', 'transactions' => 0, 'revenue' => 0, 'users' => 0]]);
    }

    $transactionMax = max(1, (int) $chartPoints->max('transactions'));
    $revenueMax = max(1, (int) $chartPoints->max('revenue'));
    $userMax = max(1, (int) $chartPoints->max('users'));
    $chartPolyline = static function (string $key, int $max) use ($chartPoints): string {
        $count = max(1, $chartPoints->count() - 1);

        return $chartPoints
            ->map(function ($point, $index) use ($key, $max, $count) {
                $x = 12 + (($index / $count) * 216);
                $value = max(0, (float) ($point[$key] ?? 0));
                $y = 78 - (($value / max(1, $max)) * 58);

                return round($x, 1) . ',' . round($y, 1);
            })
            ->implode(' ');
    };
    $chartDots = static function (string $key, int $max) use ($chartPoints): array {
        $count = max(1, $chartPoints->count() - 1);

        return $chartPoints
            ->map(function ($point, $index) use ($key, $max, $count) {
                $value = max(0, (float) ($point[$key] ?? 0));

                return [
                    'x' => round(12 + (($index / $count) * 216), 1),
                    'y' => round(78 - (($value / max(1, $max)) * 58), 1),
                    'label' => $point['label'] ?? '-',
                    'value' => $value,
                ];
            })
            ->all();
    };

    $currentAdminRoleId = (int) (auth()->user()?->role_id ?? 0);
    $dashboardCanAccess = static function (string $area) use ($currentAdminRoleId): bool {
        if ($currentAdminRoleId === 1) {
            return true;
        }

        $accessMap = [
            4 => ['dashboard', 'commissions', 'users', 'transactions', 'refunds', 'deposits', 'withdrawals'],
            6 => ['dashboard', 'users', 'categories', 'products', 'tutorials', 'transactions'],
            7 => ['dashboard', 'users', 'transactions', 'promos', 'displays'],
            8 => ['dashboard', 'commissions', 'users', 'transactions', 'deposits', 'reports'],
        ];

        return in_array($area, $accessMap[$currentAdminRoleId] ?? [], true);
    };

    $showDashboardTransactions = $dashboardCanAccess('transactions');
    $showDashboardRevenue = $dashboardCanAccess('transactions') || $dashboardCanAccess('deposits') || $dashboardCanAccess('commissions');
    $showDashboardUsers = $dashboardCanAccess('users');
    $selectedMonth = $selectedMonth ?? request('month', now()->format('m'));
    $selectedYear = $selectedYear ?? request('year', now()->format('Y'));
    $dashboardChartWeekStartLabel = isset($dashboardChartWeekStart)
        ? \Carbon\Carbon::parse($dashboardChartWeekStart)->locale('id')->translatedFormat('d M Y')
        : '-';
    $dashboardChartWeekEndLabel = isset($dashboardChartWeekEnd)
        ? \Carbon\Carbon::parse($dashboardChartWeekEnd)->locale('id')->translatedFormat('d M Y')
        : '-';
    $dashboardChartBaseQuery = ['month' => $selectedMonth, 'year' => $selectedYear];
    $dashboardChartPrevQuery = array_filter(array_merge($dashboardChartBaseQuery, ['chart_week' => $dashboardChartPrevWeek ?? null]));
    $dashboardChartNextQuery = array_filter(array_merge($dashboardChartBaseQuery, ['chart_week' => $dashboardChartNextWeek ?? null]));
    $dashboardChartPrevUrl = route('admin.dashboard', $dashboardChartPrevQuery) . '#dashboard-weekly-chart';
    $dashboardChartNextUrl = route('admin.dashboard', $dashboardChartNextQuery) . '#dashboard-weekly-chart';
    $dashboardChartPrevFetchUrl = route('admin.dashboard.weekly_chart', $dashboardChartPrevQuery);
    $dashboardChartNextFetchUrl = route('admin.dashboard.weekly_chart', $dashboardChartNextQuery);
    $dashboardChartCards = collect([
        ['enabled' => $showDashboardTransactions, 'key' => 'transactions', 'title' => 'Transaksi', 'max' => $transactionMax, 'stroke' => '#5a76c8', 'bg' => 'bg-[#f8fbff]', 'text' => 'text-[#8faaf3]', 'suffix' => ' transaksi'],
        ['enabled' => $showDashboardRevenue, 'key' => 'revenue', 'title' => 'Pendapatan', 'max' => $revenueMax, 'stroke' => '#4bc6b9', 'bg' => 'bg-[#f6fffb]', 'text' => 'text-emerald-500', 'suffix' => ''],
        ['enabled' => $showDashboardUsers, 'key' => 'users', 'title' => 'Pengguna Baru', 'max' => $userMax, 'stroke' => '#f59e0b', 'bg' => 'bg-[#fffdf7]', 'text' => 'text-amber-500', 'suffix' => ' user'],
    ])->filter(fn ($chart) => $chart['enabled'])->values();
@endphp

@if($dashboardChartCards->isNotEmpty())
<section id="dashboard-weekly-chart" class="scroll-mt-24 overflow-hidden rounded-[2rem] border-4 border-white bg-white shadow-xl shadow-[#bde0fe]/20 sm:scroll-mt-28 sm:rounded-[2.5rem]">
    <div class="flex items-start justify-between gap-3 border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff] px-5 py-5 sm:items-center sm:px-7 sm:py-6">
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Grafik</p>
            <div class="mt-1 flex min-w-0 items-center justify-between gap-3 sm:block">
                <h2 class="min-w-0 text-xl font-black tracking-tight text-[#2b3a67] sm:text-2xl">Grafik Mingguan</h2>
                <div class="flex shrink-0 items-center justify-end gap-2 sm:hidden">
                    <a href="{{ $dashboardChartPrevUrl }}" data-dashboard-week-nav data-dashboard-week-fetch="{{ $dashboardChartPrevFetchUrl }}" data-no-page-loader="true" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95" title="Minggu Sebelumnya">
                        <span class="text-base font-black">&lt;</span>
                    </a>
                    <a href="{{ $dashboardChartNextUrl }}" data-dashboard-week-nav data-dashboard-week-fetch="{{ $dashboardChartNextFetchUrl }}" data-no-page-loader="true" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95" title="Minggu Selanjutnya">
                        <span class="text-base font-black">&gt;</span>
                    </a>
                </div>
            </div>
            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $dashboardChartWeekStartLabel }} - {{ $dashboardChartWeekEndLabel }}</p>
        </div>
        <div class="hidden shrink-0 items-center justify-end gap-2 sm:flex">
            <a href="{{ $dashboardChartPrevUrl }}" data-dashboard-week-nav data-dashboard-week-fetch="{{ $dashboardChartPrevFetchUrl }}" data-no-page-loader="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem]" title="Minggu Sebelumnya">
                <span class="text-lg font-black">&lt;</span>
            </a>
            <a href="{{ $dashboardChartNextUrl }}" data-dashboard-week-nav data-dashboard-week-fetch="{{ $dashboardChartNextFetchUrl }}" data-no-page-loader="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem]" title="Minggu Selanjutnya">
                <span class="text-lg font-black">&gt;</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 p-5 sm:p-7 lg:grid-cols-3">
        @foreach($dashboardChartCards as $chart)
            <?php $dots = $chartDots($chart['key'], $chart['max']); ?>
            <article class="{{ $chart['bg'] }} rounded-[1.75rem] border-2 border-white p-4 shadow-inner">
                <p class="mb-3 text-[10px] font-black uppercase tracking-[0.24em] text-[#8faaf3]">{{ $chart['title'] }}</p>
                <svg viewBox="0 0 240 88" class="h-32 w-full overflow-visible" role="img" aria-label="{{ $chart['title'] }} 7 hari terakhir">
                    <path d="M12 20 H228 M12 49 H228 M12 78 H228" stroke="#dfeaff" stroke-width="2" stroke-dasharray="5 6" fill="none"/>
                    <polyline points="{{ $chartPolyline($chart['key'], $chart['max']) }}" fill="none" stroke="{{ $chart['stroke'] }}" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                    @foreach($dots as $dot)
                        <circle cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="4.5" fill="#ffffff" stroke="{{ $chart['stroke'] }}" stroke-width="3">
                            <title>{{ $dot['label'] }}: {{ $chart['key'] === 'revenue' ? 'Rp ' . number_format($dot['value'], 0, ',', '.') : number_format($dot['value'], 0, ',', '.') . $chart['suffix'] }}</title>
                        </circle>
                    @endforeach
                </svg>
                <div class="relative mt-2 h-5 text-[9px] font-black uppercase tracking-widest {{ $chart['text'] }}">
                    @foreach($dots as $dot)
                        <span class="absolute top-0 -translate-x-1/2 whitespace-nowrap" style="left: {{ ($dot['x'] / 240) * 100 }}%">{{ $dot['label'] }}</span>
                    @endforeach
                </div>
            </article>
        @endforeach
    </div>
</section>
@endif
