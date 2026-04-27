@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('admin_header_subtitle', 'Pantau ringkasan transaksi, saldo, stok, dan aktivitas terbaru Wiboost Store.')

@section('content')
    @php
        $paymentMethodMeta = static function ($trx): array {
            $rawMethod = strtolower((string) ($trx->payment_method ?? ''));

            if ($rawMethod === 'wallet') {
                return ['label' => 'Saldo', 'class' => 'bg-[#e0fbfc] text-[#5a76c8] border-white'];
            }

            if (($rawMethod === 'manual' || $rawMethod === '') && $trx->payment_status !== 'paid') {
                return ['label' => 'Menunggu', 'class' => 'bg-[#fff5eb] text-amber-500 border-white'];
            }

            $methodMap = [
                'qris' => 'QRIS',
                'gopay' => 'GoPay',
                'shopeepay' => 'ShopeePay',
                'bank_transfer' => 'Bank Transfer',
                'cstore' => 'Indomaret/Alfamart',
                'credit_card' => 'Kartu Kredit',
                'echannel' => 'Mandiri Bill',
                'permata_va' => 'Permata VA',
                'bca_va' => 'BCA Transfer',
                'bni_va' => 'BNI Transfer',
                'bri_va' => 'BRI Transfer',
                'cimb_va' => 'CIMB Transfer',
                'other_va' => 'ATM Bersama',
                'danamon_online' => 'Danamon Online',
                'akulaku' => 'Akulaku',
                'kredivo' => 'Kredivo',
            ];

            $label = $methodMap[$rawMethod] ?? ucwords(str_replace('_', ' ', $rawMethod));
            if ($label === '' || $label === 'Manual') {
                $label = 'E-Wallet';
            }

            return ['label' => $label, 'class' => 'bg-white text-[#5a76c8] border-[#f0f5ff]'];
        };

        $walletLogMeta = static function ($log): array {
            $displayType = (string) ($log->type ?? '');

            if (str_contains((string) ($log->invoice_number ?? ''), 'POIN')) {
                $displayType = 'poin';
            }

            $map = [
                'refund' => ['label' => 'Refund', 'class' => 'bg-[#e6fff7] text-emerald-500 border-white'],
                'topup' => ['label' => 'Top Up', 'class' => 'bg-[#f4f9ff] text-[#5a76c8] border-white'],
                'purchase' => ['label' => 'Belanja', 'class' => 'bg-[#ffe5e5] text-[#ff6b6b] border-white'],
                'poin' => ['label' => 'Poin', 'class' => 'bg-[#fff5eb] text-amber-500 border-white'],
            ];

            return $map[$displayType] ?? ['label' => strtoupper($displayType ?: '-'), 'class' => 'bg-slate-100 text-slate-500 border-white'];
        };
    @endphp

    <div class="space-y-8 pb-12 relative z-10">
        
        <section class="relative overflow-hidden rounded-[2.5rem] border-4 border-white bg-gradient-to-r from-[#e0fbfc] via-[#f4f9ff] to-white p-8 shadow-xl shadow-[#bde0fe]/30 md:p-10 transform transition hover:scale-[1.01] duration-300">
            <div class="absolute -right-6 -bottom-6 text-9xl opacity-20 pointer-events-none animate-float">👋</div>
            <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="inline-block px-4 py-1 bg-white/80 backdrop-blur-sm text-[#5a76c8] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                        Overview
                    </div>
                    <h2 class="text-3xl font-black tracking-tight text-[#2b3a67] md:text-5xl drop-shadow-sm">Selamat datang, <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]">{{ Auth::user()->name }}</span>!</h2>
                </div>
            </div>
        </section>

        @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
            <section class="rounded-[2.5rem] border-4 border-white bg-[#fffdf7] p-8 shadow-xl shadow-amber-500/20 relative overflow-hidden">
                <div class="absolute -left-6 -top-6 text-8xl opacity-10 pointer-events-none animate-bounce">⚠️</div>
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="inline-flex px-3 py-1 bg-[#fff5eb] text-[10px] font-black uppercase tracking-[0.3em] text-amber-500 rounded-full border border-amber-200 mb-2 shadow-sm">Perlu Perhatian</p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-amber-600 md:text-3xl drop-shadow-sm">Stok produk mulai menipis!</h3>
                        <p class="mt-3 text-sm font-bold text-amber-700 bg-amber-50 p-4 rounded-2xl border-2 border-white shadow-inner">Beberapa produk akun atau nomor sudah menyentuh batas pengingat stok. Segera restock supaya order dari pengguna tetap lancar.</p>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border-4 border-white bg-amber-500 px-6 py-4 text-sm font-black text-white shadow-lg shadow-amber-500/30 transition-transform active:scale-95 hover:bg-amber-600 mt-2">
                        Kelola Produk 📦
                    </a>
                </div>

                <div class="mt-6 flex flex-wrap gap-4 relative z-10">
                    @foreach($lowStockProducts as $lowStock)
                        <a href="{{ route('admin.credentials.index', $lowStock->id) }}" class="inline-flex items-center gap-3 rounded-full border-2 border-white bg-[#fff5eb] px-5 py-3 text-sm font-black text-amber-600 shadow-sm transition-transform hover:scale-105 hover:border-amber-200 group">
                            <span class="line-clamp-1 group-hover:text-amber-700">{{ $lowStock->name }}</span>
                            <span class="rounded-full bg-[#ffe5e5] px-3 py-1.5 text-[11px] text-[#ff6b6b] border border-white shadow-inner">Sisa {{ $lowStock->available_stock }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <article class="overflow-hidden rounded-[2.5rem] border-4 border-white bg-white p-8 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:-translate-y-2 relative group">
                <div class="absolute -right-4 -bottom-4 text-7xl opacity-10 group-hover:scale-110 transition-transform duration-300">🛒</div>
                <div class="relative z-10">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-inner text-3xl group-hover:scale-110 transition-transform">
                        🧾
                    </div>
                    <p class="mt-6 text-[10px] font-black uppercase tracking-[0.2em] text-[#8faaf3]">Transaksi Bulan Ini</p>
                    <p class="mt-1 text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm">{{ number_format($totalTransactionsMonth ?? 0, 0, ',', '.') }}</p>
                </div>
            </article>

            <article class="overflow-hidden rounded-[2.5rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] p-8 text-white shadow-xl shadow-[#5a76c8]/40 transition-transform duration-300 hover:-translate-y-2 relative group">
                <div class="absolute -right-4 -bottom-4 text-7xl opacity-20 group-hover:scale-110 transition-transform duration-300">💎</div>
                <div class="relative z-10">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl border-2 border-white/30 bg-white/20 shadow-inner text-3xl group-hover:scale-110 transition-transform backdrop-blur-sm">
                        💰
                    </div>
                    <p class="mt-6 text-[10px] font-black uppercase tracking-[0.2em] text-[#e0fbfc]">Pendapatan Bulan Ini</p>
                    <p class="mt-1 text-4xl font-black tracking-tight drop-shadow-md">Rp {{ number_format($revenueMonth ?? 0, 0, ',', '.') }}</p>
                </div>
            </article>

            <article class="overflow-hidden rounded-[2.5rem] border-4 border-white bg-white p-8 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:-translate-y-2 relative group">
                <div class="absolute -right-4 -bottom-4 text-7xl opacity-10 group-hover:scale-110 transition-transform duration-300">👥</div>
                <div class="relative z-10">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl border-2 border-white bg-[#e6fff7] text-emerald-500 shadow-inner text-3xl group-hover:scale-110 transition-transform">
                        😊
                    </div>
                    <p class="mt-6 text-[10px] font-black uppercase tracking-[0.2em] text-[#8faaf3]">Total Pelanggan</p>
                    <p class="mt-1 text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm">{{ number_format($totalUsers ?? 0, 0, ',', '.') }}</p>
                </div>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-8 xl:grid-cols-2">
            
            <article class="overflow-hidden rounded-[2.5rem] border-4 border-white bg-white shadow-xl shadow-[#bde0fe]/20">
                <div class="flex flex-col gap-4 border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff] px-6 py-6 md:flex-row md:items-center md:justify-between md:px-8">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3] mb-1">Realtime Order</p>
                        <h3 class="text-2xl font-black tracking-tight text-[#2b3a67]">Transaksi Terbaru 🛒</h3>
                    </div>
                    <a href="{{ route('admin.transactions.index') }}" class="inline-flex items-center justify-center rounded-full border-2 border-white bg-[#5a76c8] px-5 py-2.5 text-xs font-black text-white shadow-md shadow-[#5a76c8]/30 transition-transform hover:scale-105 active:scale-95">
                        Lihat Semua
                    </a>
                </div>

                <div class="p-6 md:p-8">
                    <div class="space-y-5 md:hidden">
                        @forelse(collect($recentTransactions ?? [])->take(5) as $trx)
                            @php($method = $paymentMethodMeta($trx))
                            <div class="rounded-[2rem] border-4 border-white bg-[#f8fbff] p-5 shadow-sm">
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <div class="min-w-0">
                                        <p class="break-all text-sm font-black text-[#2b3a67]">{{ $trx->invoice_number }}</p>
                                        <p class="mt-1 text-[10px] font-bold text-[#8faaf3] uppercase tracking-wider">{{ optional($trx->created_at)->diffForHumans() ?? '-' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-3 py-1.5 text-[9px] uppercase tracking-widest border border-white shadow-sm font-black {{ $method['class'] }}">{{ $method['label'] }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-xl border-2 border-white bg-white px-4 py-3 shadow-inner">
                                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-1">User</p>
                                        <p class="line-clamp-1 text-sm font-black text-[#2b3a67]">{{ $trx->user->name ?? 'Guest' }}</p>
                                    </div>
                                    <div class="rounded-xl border-2 border-white bg-white px-4 py-3 shadow-inner">
                                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-1">Nominal</p>
                                        <p class="text-sm font-black text-emerald-500">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-[2rem] border-4 border-dashed border-[#d6e5ff] bg-[#f8fbff] px-5 py-12 text-center">
                                <span class="text-4xl opacity-50">🧾</span>
                                <p class="mt-3 text-sm font-black text-[#2b3a67]">Belum ada transaksi.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">
                                    <th class="px-5 py-4">Invoice</th>
                                    <th class="px-5 py-4">User</th>
                                    <th class="px-5 py-4">Nominal</th>
                                    <th class="px-5 py-4 text-center">Metode</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                                @forelse(collect($recentTransactions ?? [])->take(5) as $trx)
                                    @php($method = $paymentMethodMeta($trx))
                                    <tr class="transition hover:bg-[#f8faff] group">
                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-[#2b3a67]">{{ $trx->invoice_number }}</p>
                                            <p class="mt-1 text-[10px] font-bold text-[#8faaf3] uppercase tracking-wider">{{ optional($trx->created_at)->diffForHumans() ?? '-' }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-sm font-black text-[#2b3a67]">{{ $trx->user->name ?? 'Guest' }}</td>
                                        <td class="px-5 py-4 text-sm font-black text-emerald-500">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 text-center">
                                            <span class="inline-flex rounded-full border-2 border-white shadow-sm px-4 py-1.5 text-[9px] uppercase tracking-widest font-black {{ $method['class'] }}">{{ $method['label'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-16 text-center">
                                            <span class="text-4xl opacity-50 block mb-3">🧾</span>
                                            <p class="text-sm font-black text-[#2b3a67]">Belum ada transaksi.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>

            <article class="overflow-hidden rounded-[2.5rem] border-4 border-white bg-white shadow-xl shadow-[#bde0fe]/20">
                <div class="flex flex-col gap-4 border-b-4 border-dashed border-[#f4f9ff] bg-[#fffcf0] px-6 py-6 md:flex-row md:items-center md:justify-between md:px-8">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-amber-500 mb-1">Arus Saldo</p>
                        <h3 class="text-2xl font-black tracking-tight text-[#2b3a67]">Mutasi Global 💸</h3>
                    </div>
                    <a href="{{ route('admin.deposits.index') }}" class="inline-flex items-center justify-center rounded-full border-2 border-white bg-amber-500 px-5 py-2.5 text-xs font-black text-white shadow-md shadow-amber-500/30 transition-transform hover:scale-105 active:scale-95">
                        Lihat Semua
                    </a>
                </div>

                <div class="p-6 md:p-8">
                    <div class="space-y-5 md:hidden">
                        @forelse(collect($globalWalletLogs ?? [])->take(5) as $log)
                            @php($logMeta = $walletLogMeta($log))
                            <div class="rounded-[2rem] border-4 border-white bg-[#fffdf7] p-5 shadow-sm">
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <div class="min-w-0">
                                        <p class="break-all text-sm font-black text-[#2b3a67]">{{ $log->invoice_number ?? '-' }}</p>
                                        <p class="mt-1 text-[10px] font-bold text-[#8faaf3] uppercase tracking-wider">{{ optional($log->created_at)->diffForHumans() ?? '-' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-3 py-1.5 text-[9px] uppercase tracking-widest border border-white shadow-sm font-black {{ $logMeta['class'] }}">{{ $logMeta['label'] }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-xl border-2 border-white bg-white px-4 py-3 shadow-inner">
                                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-1">User</p>
                                        <p class="line-clamp-1 text-sm font-black text-[#2b3a67]">{{ $log->user->name ?? 'Deleted' }}</p>
                                    </div>
                                    <div class="rounded-xl border-2 border-white bg-white px-4 py-3 shadow-inner">
                                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-1">Nominal</p>
                                        <p class="text-sm font-black {{ $log->type === 'purchase' ? 'text-[#ff6b6b]' : 'text-emerald-500' }}">
                                            {{ $log->type === 'purchase' ? '-' : '+' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                @if($log->description)
                                    <p class="mt-3 text-xs font-bold text-[#8faaf3] bg-white px-3 py-2 rounded-lg border border-[#f0f5ff]">{{ $log->description }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-[2rem] border-4 border-dashed border-[#f5dfb7] bg-[#fffdf7] px-5 py-12 text-center">
                                <span class="text-4xl opacity-50 block mb-3">🪫</span>
                                <p class="text-sm font-black text-[#2b3a67]">Belum ada mutasi saldo.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">
                                    <th class="px-5 py-4">Invoice</th>
                                    <th class="px-5 py-4">User</th>
                                    <th class="px-5 py-4">Nominal</th>
                                    <th class="px-5 py-4 text-center">Tipe</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                                @forelse(collect($globalWalletLogs ?? [])->take(5) as $log)
                                    @php($logMeta = $walletLogMeta($log))
                                    <tr class="transition hover:bg-[#fffdf7] group">
                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-[#2b3a67]">{{ $log->invoice_number ?? '-' }}</p>
                                            <p class="mt-1 text-[10px] font-bold text-[#8faaf3] uppercase tracking-wider">{{ optional($log->created_at)->diffForHumans() ?? '-' }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-sm font-black text-[#2b3a67]">{{ $log->user->name ?? 'Deleted' }}</td>
                                        <td class="px-5 py-4 text-sm font-black {{ $log->type === 'purchase' ? 'text-[#ff6b6b]' : 'text-emerald-500' }}">
                                            {{ $log->type === 'purchase' ? '-' : '+' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-5 py-4 text-center">
                                            <span class="inline-flex rounded-full px-4 py-1.5 border-2 border-white shadow-sm text-[9px] uppercase tracking-widest font-black {{ $logMeta['class'] }}" title="{{ $log->description }}">
                                                {{ $logMeta['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-16 text-center">
                                            <span class="text-4xl opacity-50 block mb-3">🪫</span>
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