@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('admin_header_subtitle', 'Ringkasan transaksi dan aktivitas toko.')
@section('content')
    @php
        $paymentMethodMeta = static function ($trx): array {
            $label = $trx->display_payment_method;
            if ($label === 'SALDO') { return ['label' => 'Saldo', 'class' => 'bg-[#e0fbfc] text-[#5a76c8] border-white']; }
            if ($label === 'BELUM PILIH') { return ['label' => 'Menunggu', 'class' => 'bg-[#fff5eb] text-amber-500 border-white']; }
            return ['label' => $label, 'class' => 'bg-white text-[#5a76c8] border-[#f0f5ff]'];
        };
        $transactionStatusMeta = static function ($trx): array {
            return match ($trx->display_status) {
                'Sukses' => ['label' => 'Sukses', 'class' => 'bg-[#e6fff7] text-emerald-500 border-white'],
                'Proses' => ['label' => 'Proses', 'class' => 'bg-[#e0fbfc] text-[#5a76c8] border-white'],
                'Batal', 'Gagal' => ['label' => $trx->display_status, 'class' => 'bg-[#ffe5e5] text-[#ff6b6b] border-white'],
                default => ['label' => 'Menunggu', 'class' => 'bg-[#fff5eb] text-amber-500 border-white'],
            };
        };
        $walletLogMeta = static function ($log): array {
            $displayType = (string) ($log->type ?? '');
            if (str_contains((string) ($log->invoice_number ?? ''), 'POIN')) { $displayType = 'poin'; }
            $map = ['refund'=>['label'=>'Refund','class'=>'bg-[#e6fff7] text-emerald-500 border-white'],'topup'=>['label'=>'Top Up','class'=>'bg-[#f4f9ff] text-[#5a76c8] border-white'],'purchase'=>['label'=>'Belanja','class'=>'bg-[#ffe5e5] text-[#ff6b6b] border-white'],'poin'=>['label'=>'Poin','class'=>'bg-[#fff5eb] text-amber-500 border-white']];
            return $map[$displayType] ?? ['label' => strtoupper($displayType ?: '-'), 'class' => 'bg-slate-100 text-slate-500 border-white'];
        };
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
    @endphp

    <div class="space-y-6 sm:space-y-8 pb-12 relative z-10 w-full min-w-0">
        
        <!-- Monthly Summary -->
        <section class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
            <article class="overflow-hidden rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem] sm:p-8">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8faaf3]">Transaksi Bulan Ini</p>
                <p class="mt-3 text-4xl font-black tracking-tight text-[#2b3a67]">{{ number_format($totalTransactionsMonth ?? 0, 0, ',', '.') }}</p>
            </article>

            <article class="overflow-hidden rounded-[2rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] p-6 text-white shadow-xl shadow-[#5a76c8]/35 sm:rounded-[2.5rem] sm:p-8">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#e0fbfc]">Pendapatan Bulan Ini</p>
                <p class="mt-3 truncate text-4xl font-black tracking-tight">Rp {{ number_format($revenueMonth ?? 0, 0, ',', '.') }}</p>
            </article>

            <article class="overflow-hidden rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem] sm:p-8">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8faaf3]">Total Pelanggan</p>
                <p class="mt-3 text-4xl font-black tracking-tight text-[#2b3a67]">{{ number_format($totalUsers ?? 0, 0, ',', '.') }}</p>
                <div class="mt-5 grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-2xl border-2 border-white bg-[#f4f9ff] px-2 py-2 shadow-sm">
                        <p class="text-[8px] font-black uppercase tracking-widest text-[#8faaf3]">Buyer</p>
                        <p class="text-sm font-black text-[#5a76c8]">{{ number_format($totalBuyers ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-2xl border-2 border-white bg-[#fff7d6] px-2 py-2 shadow-sm">
                        <p class="text-[8px] font-black uppercase tracking-widest text-[#d99b16]">Reseller</p>
                        <p class="text-sm font-black text-[#d99b16]">{{ number_format($totalResellers ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-2xl border-2 border-white bg-[#ede9fe] px-2 py-2 shadow-sm">
                        <p class="text-[8px] font-black uppercase tracking-widest text-[#8b5cf6]">Member</p>
                        <p class="text-sm font-black text-[#8b5cf6]">{{ number_format($totalMembers ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-[2rem] border-4 border-white bg-white shadow-xl shadow-[#bde0fe]/20 sm:rounded-[2.5rem]">
            <div class="flex flex-col gap-2 border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff] px-5 py-5 sm:px-7 sm:py-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Grafik</p>
                    <h3 class="mt-1 text-xl font-black tracking-tight text-[#2b3a67] sm:text-2xl">Grafik 7 hari terakhir</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 sm:p-7 lg:grid-cols-3">
                @foreach([
                    ['key' => 'transactions', 'title' => 'Transaksi', 'max' => $transactionMax, 'stroke' => '#5a76c8', 'bg' => 'bg-[#f8fbff]', 'text' => 'text-[#8faaf3]', 'suffix' => ' transaksi'],
                    ['key' => 'revenue', 'title' => 'Pendapatan', 'max' => $revenueMax, 'stroke' => '#4bc6b9', 'bg' => 'bg-[#f6fffb]', 'text' => 'text-emerald-500', 'suffix' => ''],
                    ['key' => 'users', 'title' => 'Pelanggan Baru', 'max' => $userMax, 'stroke' => '#f59e0b', 'bg' => 'bg-[#fffdf7]', 'text' => 'text-amber-500', 'suffix' => ' user'],
                ] as $chart)
                    @php($dots = $chartDots($chart['key'], $chart['max']))
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

        <section class="overflow-hidden rounded-[2rem] border-4 border-white bg-white shadow-xl shadow-[#bde0fe]/20 sm:rounded-[2.5rem]">
            <div class="border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff] px-5 py-5 sm:px-7 sm:py-6">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Produk Terlaris</p>
                <h3 class="mt-1 text-xl font-black tracking-tight text-[#2b3a67] sm:text-2xl">Paling Sering Dibeli per Kategori</h3>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-7 xl:grid-cols-4">
                @forelse(collect($topProductsByRootCategory ?? []) as $summary)
                    <article class="flex min-h-[230px] flex-col rounded-[1.75rem] border-4 border-white bg-[#f8fbff] p-5 shadow-inner">
                        <p class="truncate text-[10px] font-black uppercase tracking-[0.24em] text-[#8faaf3]">{{ $summary['category_name'] }}</p>
                        @if($summary['product'])
                            <a href="{{ route('admin.products.index', ['search' => $summary['product']->name]) }}" class="mt-3 block line-clamp-3 text-lg font-black leading-tight text-[#2b3a67] transition-colors hover:text-[#5a76c8]">
                                {{ $summary['product']->name }}
                            </a>
                            <div class="mt-auto grid grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] gap-2 pt-5">
                                <div class="min-w-0 rounded-2xl border-2 border-white bg-white px-3 py-2 shadow-sm">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-[#8faaf3]">Dibeli</p>
                                    <p class="text-xl font-black text-[#5a76c8]">{{ number_format($summary['orders'], 0, ',', '.') }}</p>
                                </div>
                                <div class="min-w-0 overflow-hidden rounded-2xl border-2 border-white bg-white px-3 py-2 shadow-sm">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-[#8faaf3]">Omzet</p>
                                    <p class="whitespace-nowrap text-xs font-black text-[#4bc6b9] xl:text-[13px]">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="mt-auto rounded-[1.5rem] border-4 border-dashed border-[#d6e5ff] bg-white px-4 py-8 text-center">
                                <p class="text-sm font-black text-[#8faaf3]">Belum ada pembelian.</p>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="col-span-full rounded-[2rem] border-4 border-dashed border-[#d6e5ff] bg-[#f8fbff] px-5 py-10 text-center">
                        <p class="text-sm font-black text-[#8faaf3]">Data produk terlaris belum tersedia.</p>
                    </div>
                @endforelse
            </div>
        </section>

        @if(false)
        <section class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
            <article class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white p-6 sm:p-7 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:-translate-y-1 relative min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8faaf3]">Transaksi Bulan Ini</p>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm">{{ number_format($totalTransactionsMonth ?? 0, 0, ',', '.') }}</p>
                <div class="mt-5 flex h-20 items-end gap-1.5 rounded-[1.5rem] border-2 border-white bg-[#f8fbff] px-3 py-3 shadow-inner">
                    @foreach($chartPoints as $point)
                        <span title="{{ $point['label'] }}: {{ $point['transactions'] }} transaksi" class="flex-1 rounded-t-full bg-[#5a76c8]/80 shadow-sm" style="height: {{ max(12, (int) round(((int) $point['transactions'] / $transactionMax) * 56)) }}px"></span>
                    @endforeach
                </div>
                <p class="mt-3 text-[10px] font-black uppercase tracking-[0.2em] text-[#8faaf3]">Grafik 7 hari terakhir</p>
            </article>

            <article class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] p-6 sm:p-7 text-white shadow-xl shadow-[#5a76c8]/40 transition-transform duration-300 hover:-translate-y-1 relative min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#e0fbfc]">Pendapatan Bulan Ini</p>
                <p class="mt-2 truncate text-3xl font-black tracking-tight drop-shadow-md">Rp {{ number_format($revenueMonth ?? 0, 0, ',', '.') }}</p>
                <div class="mt-5 flex h-20 items-end gap-1.5 rounded-[1.5rem] border-2 border-white/30 bg-white/15 px-3 py-3 shadow-inner">
                    @foreach($chartPoints as $point)
                        <span title="{{ $point['label'] }}: Rp {{ number_format($point['revenue'], 0, ',', '.') }}" class="flex-1 rounded-t-full bg-white/80 shadow-sm" style="height: {{ max(12, (int) round(((int) $point['revenue'] / $revenueMax) * 56)) }}px"></span>
                    @endforeach
                </div>
                <p class="mt-3 text-[10px] font-black uppercase tracking-[0.2em] text-[#e0fbfc]">Grafik 7 hari terakhir</p>
            </article>

            <article class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white p-6 sm:p-7 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:-translate-y-1 relative min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8faaf3]">Total Pelanggan</p>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm">{{ number_format($totalUsers ?? 0, 0, ',', '.') }}</p>
                <div class="mt-5 flex h-20 items-end gap-1.5 rounded-[1.5rem] border-2 border-white bg-[#f6fffb] px-3 py-3 shadow-inner">
                    @foreach($chartPoints as $point)
                        <span title="{{ $point['label'] }}: {{ $point['users'] }} user baru" class="flex-1 rounded-t-full bg-emerald-400/80 shadow-sm" style="height: {{ max(12, (int) round(((int) $point['users'] / $userMax) * 56)) }}px"></span>
                    @endforeach
                </div>
                <p class="mt-3 text-[10px] font-black uppercase tracking-[0.2em] text-[#8faaf3]">User baru 7 hari terakhir</p>
            </article>
        </section>
        @endif

        <!-- Stock Risk + Latest Reports -->
        @if(false)
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <article class="rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-6 sm:p-8 shadow-xl shadow-[#bde0fe]/30 flex flex-col min-w-0">
            <div class="mb-6 flex items-center gap-3 border-b-4 border-dashed border-[#f4f9ff] pb-6">
                <div class="text-3xl">🛡️</div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Risiko Stok</p>
                    <h4 class="text-2xl font-black text-[#2b3a67]">Produk Perlu Restock</h4>
                </div>
            </div>

            @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
                <div class="space-y-4 flex-1 overflow-y-auto pr-2 max-h-[300px]">
                    @foreach($lowStockProducts as $product)
                        <a href="{{ route('admin.credentials.index', $product->id) }}" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 rounded-[1.5rem] border-4 border-white bg-[#f8fbff] p-5 shadow-sm transition-transform hover:scale-[1.02] hover:border-[#bde0fe] group">
                            <div>
                                <p class="font-black text-[#2b3a67] text-lg group-hover:text-[#5a76c8] transition-colors">{{ $product->name }}</p>
                                <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Batas Pengingat: {{ $product->stock_reminder }}</p>
                            </div>
                            <span class="rounded-full bg-[#ffe5e5] px-4 py-2 text-xs font-black text-[#ff6b6b] border border-white shadow-sm animate-pulse w-full sm:w-auto text-center">Sisa {{ $product->available_stock }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-[2rem] border-4 border-white bg-[#f6fffb] px-5 py-12 text-center shadow-inner h-full flex flex-col items-center justify-center">
                    <div class="w-20 h-20 bg-[#e6fff7] border-2 border-emerald-100 rounded-3xl flex items-center justify-center text-4xl mb-4 shadow-sm animate-float">
                        🛡️
                    </div>
                    <p class="text-2xl font-black text-emerald-500 drop-shadow-sm">Stok Aman</p>
                    <p class="mt-3 text-sm font-bold text-emerald-600 bg-emerald-50 px-5 py-2 rounded-xl border border-emerald-100">Belum ada produk inventory yang menyentuh batas minimum.</p>
                </div>
            @endif
        </article>

        <article class="rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-6 sm:p-8 shadow-xl shadow-[#bde0fe]/30 flex flex-col min-w-0">
            <div class="mb-6 flex items-center justify-between gap-3 border-b-4 border-dashed border-[#f4f9ff] pb-6">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.25rem] border-2 border-white bg-[#f4f9ff] text-2xl shadow-inner">&#128229;</div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Laporan User</p>
                        <h4 class="truncate text-xl font-black text-[#2b3a67] sm:text-2xl">Laporan Terbaru</h4>
                    </div>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="hidden shrink-0 rounded-full border-2 border-white bg-[#5a76c8] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white shadow-md transition-transform hover:scale-105 sm:inline-flex">Lihat</a>
            </div>

            @if(isset($latestReports) && $latestReports->count() > 0)
                <div class="space-y-4 flex-1 overflow-y-auto pr-2 max-h-[300px]">
                    @foreach($latestReports as $report)
                        <a href="{{ route('admin.reports.index', ['search' => $report->related_invoice ?: 'LAP-' . str_pad((string) $report->id, 5, '0', STR_PAD_LEFT)]) }}" class="block rounded-[1.5rem] border-4 border-white bg-[#f8fbff] p-5 shadow-sm transition-transform hover:scale-[1.02] hover:border-[#bde0fe]">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-[#5a76c8]">{{ $report->related_invoice ?: 'LAP-' . str_pad((string) $report->id, 5, '0', STR_PAD_LEFT) }}</p>
                                    <p class="mt-1 truncate text-lg font-black text-[#2b3a67]">{{ $report->user?->name ?: '-' }}</p>
                                </div>
                                <span class="{{ $report->status === 'closed' ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#fff5eb] text-amber-500' }} shrink-0 rounded-full border-2 border-white px-3 py-1 text-[9px] font-black uppercase tracking-widest shadow-sm">
                                    {{ $report->status === 'closed' ? 'Selesai' : 'Baru' }}
                                </span>
                            </div>
                            <p class="mt-3 line-clamp-2 text-xs font-bold leading-relaxed text-[#8faaf3]">{{ $report->issue }}</p>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-[2rem] border-4 border-white bg-[#f8fbff] px-5 py-10 text-center shadow-inner h-full flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-white border-2 border-[#f0f5ff] rounded-3xl flex items-center justify-center text-3xl mb-4 shadow-sm">&#128229;</div>
                    <p class="text-2xl font-black text-[#5a76c8] drop-shadow-sm">Belum Ada Laporan</p>
                    <p class="mt-3 text-sm font-bold text-[#8faaf3] bg-white px-5 py-2 rounded-xl border border-[#f0f5ff]">Laporan user terbaru akan muncul di sini.</p>
                </div>
            @endif
        </article>
        </section>

        @endif

        <section class="grid grid-cols-1 gap-6 sm:gap-8 xl:grid-cols-2 w-full min-w-0">
            <article class="flex flex-col overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white shadow-xl shadow-[#bde0fe]/20 w-full min-w-0">
                <div class="flex flex-col gap-3 border-b-4 border-dashed border-[#f4f9ff] bg-[#fffdf7] px-5 py-5 sm:px-6 sm:py-6 md:flex-row md:items-center md:justify-between">
                    <div class="min-w-0">
                        <p class="mb-1 truncate text-[10px] font-black uppercase tracking-[0.3em] text-amber-500">Risiko Stok</p>
                        <h3 class="truncate text-xl font-black tracking-tight text-[#2b3a67] sm:text-2xl">Produk Perlu Restock</h3>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="inline-flex w-full shrink-0 items-center justify-center rounded-full border-2 border-white bg-amber-500 px-5 py-2.5 text-center text-xs font-black text-white shadow-md shadow-amber-500/20 transition-transform hover:scale-105 active:scale-95 md:w-auto">
                        Kelola Produk
                    </a>
                </div>
                <div class="w-full min-w-0 p-4 sm:p-6 md:p-8">
                    <div class="space-y-4 md:hidden">
                        @forelse(collect($lowStockProducts ?? [])->take(5) as $product)
                            <a href="{{ route('admin.credentials.index', $product->id) }}" class="block w-full min-w-0 rounded-2xl border-4 border-white bg-[#f8fbff] p-4 shadow-sm transition hover:border-amber-200">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-[#2b3a67]">{{ $product->name }}</p>
                                        <p class="mt-0.5 text-[9px] font-bold uppercase tracking-wider text-[#8faaf3]">Batas {{ $product->stock_reminder }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full border border-white bg-[#ffe5e5] px-3 py-1 text-[9px] font-black uppercase tracking-widest text-[#ff6b6b] shadow-sm">Sisa {{ $product->available_stock }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-[2rem] border-4 border-dashed border-emerald-100 bg-[#f6fffb] px-5 py-8 text-center">
                                <p class="text-sm font-black text-emerald-500">Stok aman.</p>
                                <p class="mt-2 text-xs font-bold text-emerald-600">Belum ada produk inventory yang menyentuh batas minimum.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="hidden w-full overflow-hidden pb-2 md:block">
                        <table class="w-full table-fixed text-left">
                            <thead>
                                <tr class="border-b-4 border-dashed border-[#f4f9ff] text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3]">
                                    <th class="w-[60%] px-3 py-3">Produk</th>
                                    <th class="w-[20%] px-3 py-3 text-center">Sisa</th>
                                    <th class="w-[20%] px-3 py-3 text-center">Batas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                                @forelse(collect($lowStockProducts ?? [])->take(5) as $product)
                                    <tr class="cursor-pointer transition hover:bg-[#fffdf7]" onclick="window.location='{{ route('admin.credentials.index', $product->id) }}'">
                                        <td class="min-w-0 px-3 py-3">
                                            <a href="{{ route('admin.credentials.index', $product->id) }}" class="block truncate text-xs font-black text-[#2b3a67] xl:text-sm">{{ $product->name }}</a>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-flex rounded-full border-2 border-white bg-[#ffe5e5] px-3 py-1 text-[9px] font-black uppercase tracking-widest text-[#ff6b6b] shadow-sm">{{ $product->available_stock }}</span>
                                        </td>
                                        <td class="px-3 py-3 text-center text-xs font-black text-[#8faaf3] xl:text-sm">{{ $product->stock_reminder }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-10 text-center">
                                            <p class="text-sm font-black text-emerald-500">Stok aman.</p>
                                            <p class="mt-2 text-xs font-bold text-emerald-600">Belum ada produk inventory yang menyentuh batas minimum.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>

            <article class="flex flex-col overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white shadow-xl shadow-[#bde0fe]/20 w-full min-w-0">
                <div class="flex flex-col gap-3 border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff] px-5 py-5 sm:px-6 sm:py-6 md:flex-row md:items-center md:justify-between">
                    <div class="min-w-0">
                        <p class="mb-1 truncate text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Laporan User</p>
                        <h3 class="truncate text-xl font-black tracking-tight text-[#2b3a67] sm:text-2xl">Laporan Terbaru</h3>
                    </div>
                    <a href="{{ route('admin.reports.index') }}" class="inline-flex w-full shrink-0 items-center justify-center rounded-full border-2 border-white bg-[#5a76c8] px-5 py-2.5 text-center text-xs font-black text-white shadow-md shadow-[#5a76c8]/30 transition-transform hover:scale-105 active:scale-95 md:w-auto">
                        Lihat Semua
                    </a>
                </div>
                <div class="w-full min-w-0 p-4 sm:p-6 md:p-8">
                    <div class="space-y-4 md:hidden">
                        @forelse(collect($latestReports ?? [])->take(5) as $report)
                            @php($reportInvoice = $report->related_invoice ?: 'LAP-' . str_pad((string) $report->id, 5, '0', STR_PAD_LEFT))
                            <a href="{{ route('admin.reports.index', ['search' => $reportInvoice]) }}" class="block w-full min-w-0 rounded-2xl border-4 border-white bg-[#f8fbff] p-4 shadow-sm transition hover:border-[#bde0fe]">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-[#2b3a67]">{{ $reportInvoice }}</p>
                                        <p class="mt-0.5 truncate text-[10px] font-bold uppercase tracking-wider text-[#8faaf3]">{{ $report->category_label ?: '-' }}</p>
                                    </div>
                                    <span class="{{ $report->status === 'closed' ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#fff5eb] text-amber-500' }} shrink-0 rounded-full border border-white px-3 py-1 text-[9px] font-black uppercase tracking-widest shadow-sm">{{ $report->status === 'closed' ? 'Selesai' : 'Baru' }}</span>
                                </div>
                                <p class="mt-3 truncate text-xs font-black text-[#2b3a67]">{{ $report->user?->name ?: '-' }}</p>
                            </a>
                        @empty
                            <div class="rounded-[2rem] border-4 border-dashed border-[#d6e5ff] bg-[#f8fbff] px-5 py-8 text-center">
                                <p class="text-sm font-black text-[#2b3a67]">Belum ada laporan.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="hidden w-full overflow-x-auto pb-2 md:block">
                        <table class="min-w-[620px] w-full table-auto text-left">
                            <thead>
                                <tr class="border-b-4 border-dashed border-[#f4f9ff] text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3]">
                                    <th class="w-[30%] px-3 py-3">Invoice</th>
                                    <th class="w-[25%] px-3 py-3">User</th>
                                    <th class="w-[25%] px-3 py-3">Jenis</th>
                                    <th class="w-[20%] px-3 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                                @forelse(collect($latestReports ?? [])->take(5) as $report)
                                    @php($reportInvoice = $report->related_invoice ?: 'LAP-' . str_pad((string) $report->id, 5, '0', STR_PAD_LEFT))
                                    <tr class="transition hover:bg-[#f8faff]">
                                        <td class="min-w-0 px-3 py-3">
                                            <a href="{{ route('admin.reports.index', ['search' => $reportInvoice]) }}" class="block whitespace-nowrap text-xs font-black text-[#2b3a67] xl:text-sm">{{ $reportInvoice }}</a>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-3 text-xs font-black text-[#2b3a67] xl:text-sm">{{ $report->user?->name ?: '-' }}</td>
                                        <td class="whitespace-nowrap px-3 py-3 text-xs font-black text-[#8faaf3] xl:text-sm">{{ $report->category_label ?: '-' }}</td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="{{ $report->status === 'closed' ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#fff5eb] text-amber-500' }} inline-flex whitespace-nowrap rounded-full border-2 border-white px-2.5 py-1 text-[8px] font-black uppercase tracking-widest shadow-sm xl:text-[9px]">
                                                {{ $report->status === 'closed' ? 'Selesai' : 'Baru' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-10 text-center">
                                            <p class="text-sm font-black text-[#2b3a67]">Belum ada laporan.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        </section>

        <!-- Low Stock Alert -->
        @if(false && isset($lowStockProducts) && $lowStockProducts->count() > 0)
            <section class="rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#fffdf7] p-6 sm:p-8 shadow-xl shadow-amber-500/20 relative overflow-hidden">
                <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between w-full">
                    <div class="max-w-2xl min-w-0">
                        <p class="inline-flex px-3 py-1 bg-[#fff5eb] text-[10px] font-black uppercase tracking-[0.3em] text-amber-500 rounded-full border border-amber-200 mb-2 shadow-sm">Perlu Perhatian</p>
                        <h3 class="mt-2 text-xl sm:text-2xl font-black tracking-tight text-amber-600 md:text-3xl drop-shadow-sm">Stok produk mulai menipis!</h3>
                        <p class="mt-3 text-xs sm:text-sm font-bold text-amber-700 bg-amber-50 p-3 sm:p-4 rounded-2xl border-2 border-white shadow-inner">Beberapa produk akun premium sudah menyentuh batas pengingat stok. Segera restock supaya order dari pengguna tetap lancar.</p>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="w-full sm:w-auto text-center inline-flex items-center justify-center gap-2 rounded-full border-4 border-white bg-amber-500 px-6 py-3 sm:py-4 text-sm font-black text-white shadow-lg shadow-amber-500/30 transition-transform active:scale-95 hover:bg-amber-600 mt-2 shrink-0">
                        Kelola Produk 📦
                    </a>
                </div>
                <div class="mt-6 flex flex-col sm:flex-row sm:flex-wrap gap-3 relative z-10">
                    @foreach($lowStockProducts as $lowStock)
                        <a href="{{ route('admin.credentials.index', $lowStock->id) }}" class="flex items-center justify-between sm:justify-start gap-3 rounded-full border-2 border-white bg-[#fff5eb] px-4 sm:px-5 py-2 sm:py-3 text-xs sm:text-sm font-black text-amber-600 shadow-sm transition-transform hover:scale-105 hover:border-amber-200 group">
                            <span class="truncate max-w-[150px] sm:max-w-[200px] group-hover:text-amber-700">{{ $lowStock->name }}</span>
                            <span class="shrink-0 rounded-full bg-[#ffe5e5] px-2 sm:px-3 py-1 text-[10px] sm:text-[11px] text-[#ff6b6b] border border-white shadow-inner">Sisa {{ $lowStock->available_stock }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Stats Grid -->
        @if(false)
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <article class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white p-6 sm:p-8 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:-translate-y-2 relative group min-w-0">
                <div class="relative z-10">
                    <div class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-inner text-2xl sm:text-3xl group-hover:scale-110 transition-transform">
                        📊
                    </div>
                    <p class="mt-4 sm:mt-6 text-[10px] font-black uppercase tracking-[0.2em] text-[#8faaf3] truncate">Transaksi Bulan Ini</p>
                    <p class="mt-1 text-3xl sm:text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm truncate">{{ number_format($totalTransactionsMonth ?? 0, 0, ',', '.') }}</p>
                </div>
            </article>

            <article class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] p-6 sm:p-8 text-white shadow-xl shadow-[#5a76c8]/40 transition-transform duration-300 hover:-translate-y-2 relative group min-w-0">
                <div class="relative z-10">
                    <div class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-2xl border-2 border-white/30 bg-white/20 shadow-inner text-2xl sm:text-3xl group-hover:scale-110 transition-transform backdrop-blur-sm">
                        💸
                    </div>
                    <p class="mt-4 sm:mt-6 text-[10px] font-black uppercase tracking-[0.2em] text-[#e0fbfc] truncate">Pendapatan Bulan Ini</p>
                    <p class="mt-1 text-2xl sm:text-4xl font-black tracking-tight drop-shadow-md truncate">Rp {{ number_format($revenueMonth ?? 0, 0, ',', '.') }}</p>
                </div>
            </article>

            <article class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white p-6 sm:p-8 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:-translate-y-2 relative group min-w-0 sm:col-span-2 lg:col-span-1">
                <div class="relative z-10">
                    <div class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-2xl border-2 border-white bg-[#e6fff7] text-emerald-500 shadow-inner text-2xl sm:text-3xl group-hover:scale-110 transition-transform">
                        👤
                    </div>
                    <p class="mt-4 sm:mt-6 text-[10px] font-black uppercase tracking-[0.2em] text-[#8faaf3] truncate">Total Pelanggan</p>
                    <p class="mt-1 text-3xl sm:text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm truncate">{{ number_format($totalUsers ?? 0, 0, ',', '.') }}</p>
                </div>
            </article>
        </section>
        @endif

        <!-- Tables Section -->
        <section class="grid grid-cols-1 xl:grid-cols-2 gap-6 sm:gap-8 w-full min-w-0">
            
            <!-- Realtime Order -->
            <article class="flex flex-col overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white shadow-xl shadow-[#bde0fe]/20 w-full min-w-0">
                <div class="flex flex-col gap-3 border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff] px-5 sm:px-6 py-5 sm:py-6 md:flex-row md:items-center md:justify-between w-full">
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3] mb-1 truncate">Realtime Order</p>
                        <h3 class="text-xl sm:text-2xl font-black tracking-tight text-[#2b3a67] truncate">Transaksi Terbaru ⚡</h3>
                    </div>
                    <a href="{{ route('admin.transactions.index') }}" class="shrink-0 text-center w-full md:w-auto inline-flex items-center justify-center rounded-full border-2 border-white bg-[#5a76c8] px-5 py-2.5 text-xs font-black text-white shadow-md shadow-[#5a76c8]/30 transition-transform hover:scale-105 active:scale-95">
                        Lihat Semua
                    </a>
                </div>
                <div class="p-4 sm:p-6 md:p-8 w-full min-w-0">
                    <!-- Mobile Card View -->
                    <div class="space-y-4 md:hidden w-full">
                        @forelse(collect($recentTransactions ?? [])->take(5) as $trx)
                            @php($method = $paymentMethodMeta($trx))
                            @php($status = $transactionStatusMeta($trx))
                            <div class="rounded-2xl border-4 border-white bg-[#f8fbff] p-4 shadow-sm w-full min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs sm:text-sm font-black text-[#2b3a67]">{{ $trx->invoice_number }}</p>
                                        <p class="mt-0.5 text-[9px] font-bold text-[#8faaf3] uppercase tracking-wider">{{ optional($trx->created_at)->diffForHumans() ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="rounded-xl border-2 border-white bg-white px-3 py-2 shadow-inner min-w-0">
                                        <p class="text-[8px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-0.5 truncate">User</p>
                                        <p class="truncate text-xs font-black text-[#2b3a67]">{{ $trx->user->name ?? 'Guest' }}</p>
                                    </div>
                                    <div class="rounded-xl border-2 border-white bg-white px-3 py-2 shadow-inner min-w-0">
                                        <p class="text-[8px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-0.5 truncate">Nominal</p>
                                        <p class="truncate text-xs font-black text-emerald-500">Rp {{ number_format($trx->display_total_payable, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-xl border-2 border-white bg-white px-3 py-2 shadow-inner min-w-0">
                                        <p class="text-[8px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-0.5 truncate">Status</p>
                                        <span class="inline-flex max-w-full truncate rounded-full border px-2 py-1 text-[8px] font-black uppercase tracking-widest {{ $status['class'] }}">{{ $status['label'] }}</span>
                                    </div>
                                    <div class="rounded-xl border-2 border-white bg-white px-3 py-2 text-left shadow-inner min-w-0">
                                        <p class="text-[8px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-0.5 truncate">Metode</p>
                                        <span class="inline-flex max-w-full truncate rounded-full border px-2 py-1 text-[8px] font-black uppercase tracking-widest {{ $method['class'] }}">{{ $method['label'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-[2rem] border-4 border-dashed border-[#d6e5ff] bg-[#f8fbff] px-5 py-8 text-center">
                                <span class="text-3xl opacity-50">📭</span>
                                <p class="mt-2 text-xs sm:text-sm font-black text-[#2b3a67]">Belum ada transaksi.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden w-full overflow-x-auto md:block pb-2">
                        <table class="min-w-[720px] w-full table-auto text-left">
                            <thead>
                                <tr class="text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">
                                    <th class="w-[28%] px-3 py-3">Invoice</th>
                                    <th class="w-[20%] px-3 py-3">User</th>
                                    <th class="w-[18%] px-3 py-3">Nominal</th>
                                    <th class="w-[17%] px-3 py-3 text-center">Status</th>
                                    <th class="w-[17%] px-3 py-3 text-center">Metode</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                                @forelse(collect($recentTransactions ?? [])->take(5) as $trx)
                                    @php($method = $paymentMethodMeta($trx))
                                    @php($status = $transactionStatusMeta($trx))
                                    <tr class="transition hover:bg-[#f8faff] group">
                                        <td class="px-3 py-3 min-w-0">
                                            <p class="whitespace-nowrap text-xs font-black text-[#2b3a67] xl:text-sm">{{ $trx->invoice_number }}</p>
                                            <p class="mt-0.5 whitespace-nowrap text-[9px] font-bold text-[#8faaf3] uppercase tracking-wider">{{ optional($trx->created_at)->diffForHumans() ?? '-' }}</p>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-3 text-xs font-black text-[#2b3a67] xl:text-sm">{{ $trx->user->name ?? 'Guest' }}</td>
                                        <td class="whitespace-nowrap px-3 py-3 text-xs font-black text-emerald-500 xl:text-sm">Rp {{ number_format($trx->display_total_payable, 0, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-flex whitespace-nowrap rounded-full border-2 border-white px-2.5 py-1 text-[8px] font-black uppercase tracking-widest shadow-sm xl:text-[9px] {{ $status['class'] }}">{{ $status['label'] }}</span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-flex whitespace-nowrap rounded-full border-2 border-white px-2.5 py-1 text-[8px] font-black uppercase tracking-widest shadow-sm xl:text-[9px] {{ $method['class'] }}">{{ $method['label'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-10 text-center">
                                            <span class="text-3xl opacity-50 block mb-2">📭</span>
                                            <p class="text-sm font-black text-[#2b3a67]">Belum ada transaksi.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>

            <!-- Arus Saldo -->
            <article class="flex flex-col overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white shadow-xl shadow-[#bde0fe]/20 w-full min-w-0">
                <div class="flex flex-col gap-3 border-b-4 border-dashed border-[#f4f9ff] bg-[#fffcf0] px-5 sm:px-6 py-5 sm:py-6 md:flex-row md:items-center md:justify-between w-full">
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-amber-500 mb-1 truncate">Arus Saldo</p>
                        <h3 class="text-xl sm:text-2xl font-black tracking-tight text-[#2b3a67] truncate">Mutasi Saldo 🔄</h3>
                    </div>
                    <a href="{{ route('admin.deposits.index') }}" class="shrink-0 text-center w-full md:w-auto inline-flex items-center justify-center rounded-full border-2 border-white bg-amber-500 px-5 py-2.5 text-xs font-black text-white shadow-md shadow-amber-500/30 transition-transform hover:scale-105 active:scale-95">
                        Lihat Semua
                    </a>
                </div>
                <div class="p-4 sm:p-6 md:p-8 w-full min-w-0">
                    <!-- Mobile Card View -->
                    <div class="space-y-4 md:hidden w-full">
                        @forelse(collect($globalWalletLogs ?? [])->take(5) as $log)
                            @php($logMeta = $walletLogMeta($log))
                            <div class="rounded-2xl border-4 border-white bg-[#fffdf7] p-4 shadow-sm w-full min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs sm:text-sm font-black text-[#2b3a67]">{{ $log->invoice_number ?? '-' }}</p>
                                        <p class="mt-0.5 text-[9px] font-bold text-[#8faaf3] uppercase tracking-wider">{{ optional($log->created_at)->diffForHumans() ?? '-' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2 py-1 text-[8px] uppercase tracking-widest border border-white shadow-sm font-black {{ $logMeta['class'] }}">{{ $logMeta['label'] }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="rounded-xl border-2 border-white bg-white px-3 py-2 shadow-inner min-w-0">
                                        <p class="text-[8px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-0.5 truncate">User</p>
                                        <p class="truncate text-xs font-black text-[#2b3a67]">{{ $log->user->name ?? 'Deleted' }}</p>
                                    </div>
                                    <div class="rounded-xl border-2 border-white bg-white px-3 py-2 shadow-inner min-w-0">
                                        <p class="text-[8px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-0.5 truncate">Nominal</p>
                                        <p class="truncate text-xs font-black {{ $log->type === 'purchase' ? 'text-[#ff6b6b]' : 'text-emerald-500' }}">
                                            {{ $log->type === 'purchase' ? '-' : '+' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                @if($log->description)
                                    <p class="mt-2 text-[10px] sm:text-xs font-bold text-[#8faaf3] bg-white px-3 py-2 rounded-lg border border-[#f0f5ff] truncate">{{ $log->description }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-[2rem] border-4 border-dashed border-[#f5dfb7] bg-[#fffdf7] px-5 py-8 text-center">
                                <span class="text-3xl opacity-50 block mb-2">📁</span>
                                <p class="text-xs sm:text-sm font-black text-[#2b3a67]">Belum ada mutasi saldo.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden w-full overflow-x-auto md:block pb-2">
                        <table class="min-w-[660px] w-full table-auto text-left">
                            <thead>
                                <tr class="text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">
                                    <th class="w-[34%] px-3 py-3">Invoice</th>
                                    <th class="w-[24%] px-3 py-3">User</th>
                                    <th class="w-[22%] px-3 py-3">Nominal</th>
                                    <th class="w-[20%] px-3 py-3 text-center">Tipe</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                                @forelse(collect($globalWalletLogs ?? [])->take(5) as $log)
                                    @php($logMeta = $walletLogMeta($log))
                                    <tr class="transition hover:bg-[#fffdf7] group">
                                        <td class="px-3 py-3 min-w-0">
                                            <p class="whitespace-nowrap text-xs font-black text-[#2b3a67] xl:text-sm">{{ $log->invoice_number ?? '-' }}</p>
                                            <p class="mt-0.5 whitespace-nowrap text-[9px] font-bold text-[#8faaf3] uppercase tracking-wider">{{ optional($log->created_at)->diffForHumans() ?? '-' }}</p>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-3 text-xs font-black text-[#2b3a67] xl:text-sm">{{ $log->user->name ?? 'Deleted' }}</td>
                                        <td class="whitespace-nowrap px-3 py-3 text-xs font-black xl:text-sm {{ $log->type === 'purchase' ? 'text-[#ff6b6b]' : 'text-emerald-500' }}">
                                            {{ $log->type === 'purchase' ? '-' : '+' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-flex whitespace-nowrap rounded-full border-2 border-white px-2.5 py-1 text-[8px] font-black uppercase tracking-widest shadow-sm xl:text-[9px] {{ $logMeta['class'] }}" title="{{ $log->description }}">
                                                {{ $logMeta['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-10 text-center">
                                            <span class="text-3xl opacity-50 block mb-2">📁</span>
                                            <p class="text-sm font-black text-[#2b3a67]">Belum ada mutasi saldo.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        </section>

        <section class="mt-6 sm:mt-8 overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white shadow-xl shadow-[#bde0fe]/20 w-full min-w-0">
            <div class="flex flex-col gap-3 border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff] px-5 sm:px-6 py-5 sm:py-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3] mb-1">Kontak Admin</p>
                    <h3 class="text-xl sm:text-2xl font-black tracking-tight text-[#2b3a67]">Nomor WhatsApp Aktif</h3>
                </div>
                <div class="grid grid-cols-2 gap-2 text-[9px] font-black uppercase tracking-widest text-[#5a76c8] sm:text-[10px]">
                    <span class="min-w-0 rounded-full border-2 border-white bg-[#f4f9ff] px-3 py-2 text-center shadow-sm sm:px-4">CS: {{ filled($adminCsWhatsapp ?? null) ? '+' . $adminCsWhatsapp : '-' }}</span>
                    <span class="min-w-0 rounded-full border-2 border-white bg-[#f4f9ff] px-3 py-2 text-center shadow-sm sm:px-4">OTP: {{ filled($adminOtpWhatsapp ?? null) ? '+' . $adminOtpWhatsapp : '-' }}</span>
                </div>
            </div>

            <form action="{{ route('admin.dashboard.contact-numbers.update') }}" method="POST" class="p-4 sm:p-6 md:p-8">
                @csrf
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div class="rounded-[1.6rem] border-4 border-white bg-[#f8fbff] p-4 shadow-inner">
                        <label for="admin_cs_whatsapp" class="mb-2 ml-2 block text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3]">Nomor Admin CS</label>
                        <input id="admin_cs_whatsapp" type="text" name="admin_cs_whatsapp" value="{{ old('admin_cs_whatsapp', $adminCsWhatsapp ?? '') }}" placeholder="0895611244000" class="w-full rounded-[1.25rem] border-4 border-white bg-white px-5 py-4 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]">
                        <p class="mt-2 ml-2 text-[10px] font-bold text-[#8faaf3]">Dipakai untuk icon WhatsApp kanan bawah dan laporan bantuan.</p>
                        @error('admin_cs_whatsapp')
                            <p class="mt-2 ml-2 text-[10px] font-black text-[#ff6b6b]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-[1.6rem] border-4 border-white bg-[#f8fbff] p-4 shadow-inner">
                        <label for="admin_otp_whatsapp" class="mb-2 ml-2 block text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3]">Nomor Admin OTP</label>
                        <input id="admin_otp_whatsapp" type="text" name="admin_otp_whatsapp" value="{{ old('admin_otp_whatsapp', $adminOtpWhatsapp ?? '') }}" placeholder="0895611244000" class="w-full rounded-[1.25rem] border-4 border-white bg-white px-5 py-4 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]">
                        <p class="mt-2 ml-2 text-[10px] font-bold text-[#8faaf3]">Dipakai khusus tombol minta OTP di detail pesanan user.</p>
                        @error('admin_otp_whatsapp')
                            <p class="mt-2 ml-2 text-[10px] font-black text-[#ff6b6b]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] px-7 py-3 text-sm font-black text-white shadow-lg shadow-[#5a76c8]/25 transition-transform active:scale-95 sm:w-auto">
                        Simpan Nomor Admin
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
