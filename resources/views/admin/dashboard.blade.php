@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('admin_header_subtitle', 'Pantau ringkasan transaksi, saldo, stok, dan aktivitas terbaru Wiboost Store.')
@section('content')
    @php
        $paymentMethodMeta = static function ($trx): array {
            $rawMethod = strtolower((string) ($trx->payment_method ?? ''));
            if ($rawMethod === 'wallet') { return ['label' => 'Saldo', 'class' => 'bg-[#e0fbfc] text-[#5a76c8] border-white']; }
            if (($rawMethod === 'manual' || $rawMethod === '') && $trx->payment_status !== 'paid') { return ['label' => 'Menunggu', 'class' => 'bg-[#fff5eb] text-amber-500 border-white']; }
            $methodMap = ['qris'=>'QRIS','gopay'=>'GoPay','shopeepay'=>'ShopeePay','bank_transfer'=>'Bank Transfer','cstore'=>'Indomaret/Alfamart','credit_card'=>'Kartu Kredit','echannel'=>'Mandiri Bill','permata_va'=>'Permata VA','bca_va'=>'BCA Transfer','bni_va'=>'BNI Transfer','bri_va'=>'BRI Transfer','cimb_va'=>'CIMB Transfer','other_va'=>'ATM Bersama','danamon_online'=>'Danamon Online','akulaku'=>'Akulaku','kredivo'=>'Kredivo'];
            $label = $methodMap[$rawMethod] ?? ucwords(str_replace('_', ' ', $rawMethod));
            if ($label === '' || $label === 'Manual') { $label = 'E-Wallet'; }
            return ['label' => $label, 'class' => 'bg-white text-[#5a76c8] border-[#f0f5ff]'];
        };
        $walletLogMeta = static function ($log): array {
            $displayType = (string) ($log->type ?? '');
            if (str_contains((string) ($log->invoice_number ?? ''), 'POIN')) { $displayType = 'poin'; }
            $map = ['refund'=>['label'=>'Refund','class'=>'bg-[#e6fff7] text-emerald-500 border-white'],'topup'=>['label'=>'Top Up','class'=>'bg-[#f4f9ff] text-[#5a76c8] border-white'],'purchase'=>['label'=>'Belanja','class'=>'bg-[#ffe5e5] text-[#ff6b6b] border-white'],'poin'=>['label'=>'Poin','class'=>'bg-[#fff5eb] text-amber-500 border-white']];
            return $map[$displayType] ?? ['label' => strtoupper($displayType ?: '-'), 'class' => 'bg-slate-100 text-slate-500 border-white'];
        };
    @endphp

    <div class="space-y-6 sm:space-y-8 pb-12 relative z-10 w-full min-w-0">
        
        <!-- Welcome Banner -->
        <section class="relative overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-gradient-to-r from-[#e0fbfc] via-[#f4f9ff] to-white p-6 sm:p-8 shadow-xl shadow-[#bde0fe]/30 transform transition hover:scale-[1.01] duration-300">
            <div class="absolute -right-6 -bottom-6 text-8xl sm:text-9xl opacity-20 pointer-events-none animate-float">👋</div>
            <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between w-full">
                <div class="min-w-0">
                    <div class="inline-block px-4 py-1 bg-white/80 backdrop-blur-sm text-[#5a76c8] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                        Overview
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-[#2b3a67] md:text-5xl drop-shadow-sm truncate">Selamat datang {{ Auth::user()->name }}!</h2>
                </div>
            </div>
        </section>

        <!-- Low Stock Alert -->
        @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
            <section class="rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#fffdf7] p-6 sm:p-8 shadow-xl shadow-amber-500/20 relative overflow-hidden">
                <div class="absolute -left-6 -top-6 text-7xl sm:text-8xl opacity-10 pointer-events-none animate-bounce">⚠️</div>
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
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <article class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white p-6 sm:p-8 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:-translate-y-2 relative group min-w-0">
                <div class="absolute -right-4 -bottom-4 text-6xl sm:text-7xl opacity-10 group-hover:scale-110 transition-transform duration-300">🛒</div>
                <div class="relative z-10">
                    <div class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-inner text-2xl sm:text-3xl group-hover:scale-110 transition-transform">
                        📊
                    </div>
                    <p class="mt-4 sm:mt-6 text-[10px] font-black uppercase tracking-[0.2em] text-[#8faaf3] truncate">Transaksi Bulan Ini</p>
                    <p class="mt-1 text-3xl sm:text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm truncate">{{ number_format($totalTransactionsMonth ?? 0, 0, ',', '.') }}</p>
                </div>
            </article>

            <article class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] p-6 sm:p-8 text-white shadow-xl shadow-[#5a76c8]/40 transition-transform duration-300 hover:-translate-y-2 relative group min-w-0">
                <div class="absolute -right-4 -bottom-4 text-6xl sm:text-7xl opacity-20 group-hover:scale-110 transition-transform duration-300">💰</div>
                <div class="relative z-10">
                    <div class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-2xl border-2 border-white/30 bg-white/20 shadow-inner text-2xl sm:text-3xl group-hover:scale-110 transition-transform backdrop-blur-sm">
                        💸
                    </div>
                    <p class="mt-4 sm:mt-6 text-[10px] font-black uppercase tracking-[0.2em] text-[#e0fbfc] truncate">Pendapatan Bulan Ini</p>
                    <p class="mt-1 text-2xl sm:text-4xl font-black tracking-tight drop-shadow-md truncate">Rp {{ number_format($revenueMonth ?? 0, 0, ',', '.') }}</p>
                </div>
            </article>

            <article class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white p-6 sm:p-8 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:-translate-y-2 relative group min-w-0 sm:col-span-2 lg:col-span-1">
                <div class="absolute -right-4 -bottom-4 text-6xl sm:text-7xl opacity-10 group-hover:scale-110 transition-transform duration-300">👥</div>
                <div class="relative z-10">
                    <div class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-2xl border-2 border-white bg-[#e6fff7] text-emerald-500 shadow-inner text-2xl sm:text-3xl group-hover:scale-110 transition-transform">
                        👤
                    </div>
                    <p class="mt-4 sm:mt-6 text-[10px] font-black uppercase tracking-[0.2em] text-[#8faaf3] truncate">Total Pelanggan</p>
                    <p class="mt-1 text-3xl sm:text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm truncate">{{ number_format($totalUsers ?? 0, 0, ',', '.') }}</p>
                </div>
            </article>
        </section>

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
                            <div class="rounded-2xl border-4 border-white bg-[#f8fbff] p-4 shadow-sm w-full min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs sm:text-sm font-black text-[#2b3a67]">{{ $trx->invoice_number }}</p>
                                        <p class="mt-0.5 text-[9px] font-bold text-[#8faaf3] uppercase tracking-wider">{{ optional($trx->created_at)->diffForHumans() ?? '-' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2 py-1 text-[8px] uppercase tracking-widest border border-white shadow-sm font-black {{ $method['class'] }}">{{ $method['label'] }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="rounded-xl border-2 border-white bg-white px-3 py-2 shadow-inner min-w-0">
                                        <p class="text-[8px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-0.5 truncate">User</p>
                                        <p class="truncate text-xs font-black text-[#2b3a67]">{{ $trx->user->name ?? 'Guest' }}</p>
                                    </div>
                                    <div class="rounded-xl border-2 border-white bg-white px-3 py-2 shadow-inner min-w-0">
                                        <p class="text-[8px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-0.5 truncate">Nominal</p>
                                        <p class="truncate text-xs font-black text-emerald-500">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
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
                        <table class="w-full min-w-[500px] text-left whitespace-nowrap">
                            <thead>
                                <tr class="text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">
                                    <th class="px-4 py-3">Invoice</th>
                                    <th class="px-4 py-3">User</th>
                                    <th class="px-4 py-3">Nominal</th>
                                    <th class="px-4 py-3 text-center">Metode</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                                @forelse(collect($recentTransactions ?? [])->take(5) as $trx)
                                    @php($method = $paymentMethodMeta($trx))
                                    <tr class="transition hover:bg-[#f8faff] group">
                                        <td class="px-4 py-3 min-w-0">
                                            <p class="text-sm font-black text-[#2b3a67] truncate max-w-[150px]">{{ $trx->invoice_number }}</p>
                                            <p class="mt-0.5 text-[9px] font-bold text-[#8faaf3] uppercase tracking-wider">{{ optional($trx->created_at)->diffForHumans() ?? '-' }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-black text-[#2b3a67] max-w-[120px] truncate">{{ $trx->user->name ?? 'Guest' }}</td>
                                        <td class="px-4 py-3 text-sm font-black text-emerald-500">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex rounded-full border-2 border-white shadow-sm px-3 py-1 text-[9px] uppercase tracking-widest font-black {{ $method['class'] }}">{{ $method['label'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-10 text-center">
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
                        <h3 class="text-xl sm:text-2xl font-black tracking-tight text-[#2b3a67] truncate">Mutasi Global 🔄</h3>
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
                        <table class="w-full min-w-[500px] text-left whitespace-nowrap">
                            <thead>
                                <tr class="text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">
                                    <th class="px-4 py-3">Invoice</th>
                                    <th class="px-4 py-3">User</th>
                                    <th class="px-4 py-3">Nominal</th>
                                    <th class="px-4 py-3 text-center">Tipe</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                                @forelse(collect($globalWalletLogs ?? [])->take(5) as $log)
                                    @php($logMeta = $walletLogMeta($log))
                                    <tr class="transition hover:bg-[#fffdf7] group">
                                        <td class="px-4 py-3 min-w-0">
                                            <p class="text-sm font-black text-[#2b3a67] truncate max-w-[150px]">{{ $log->invoice_number ?? '-' }}</p>
                                            <p class="mt-0.5 text-[9px] font-bold text-[#8faaf3] uppercase tracking-wider">{{ optional($log->created_at)->diffForHumans() ?? '-' }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-black text-[#2b3a67] max-w-[120px] truncate">{{ $log->user->name ?? 'Deleted' }}</td>
                                        <td class="px-4 py-3 text-sm font-black {{ $log->type === 'purchase' ? 'text-[#ff6b6b]' : 'text-emerald-500' }}">
                                            {{ $log->type === 'purchase' ? '-' : '+' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex rounded-full px-3 py-1 border-2 border-white shadow-sm text-[9px] uppercase tracking-widest font-black {{ $logMeta['class'] }}" title="{{ $log->description }}">
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
    </div>
@endsection
