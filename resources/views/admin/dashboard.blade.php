@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('admin_header_subtitle', 'Pantau ringkasan transaksi, saldo, stok, dan aktivitas terbaru Wiboost Store.')

@section('content')
    @php
        $paymentMethodMeta = static function ($trx): array {
            $rawMethod = strtolower((string) ($trx->payment_method ?? ''));

            if ($rawMethod === 'wallet') {
                return ['label' => 'Saldo', 'class' => 'bg-[#e0fbfc] text-[#5a76c8]'];
            }

            if (($rawMethod === 'manual' || $rawMethod === '') && $trx->payment_status !== 'paid') {
                return ['label' => 'Menunggu', 'class' => 'bg-[#fff5eb] text-amber-500'];
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

            return ['label' => $label, 'class' => 'bg-[#f4f9ff] text-[#8faaf3]'];
        };

        $walletLogMeta = static function ($log): array {
            $displayType = (string) ($log->type ?? '');

            if (str_contains((string) ($log->invoice_number ?? ''), 'POIN')) {
                $displayType = 'poin';
            }

            $map = [
                'refund' => ['label' => 'Refund', 'class' => 'bg-[#e6fff7] text-emerald-500'],
                'topup' => ['label' => 'Top Up', 'class' => 'bg-[#f0f5ff] text-[#5a76c8]'],
                'purchase' => ['label' => 'Belanja', 'class' => 'bg-[#ffe5e5] text-[#ff6b6b]'],
                'poin' => ['label' => 'Poin', 'class' => 'bg-[#fff5eb] text-amber-500'],
            ];

            return $map[$displayType] ?? ['label' => strtoupper($displayType ?: '-'), 'class' => 'bg-slate-100 text-slate-500'];
        };
    @endphp

    <div class="space-y-8 pb-12">
        <section class="overflow-hidden rounded-[2.5rem] border-4 border-white bg-gradient-to-r from-[#f8fbff] via-white to-[#eef7ff] p-6 shadow-xl shadow-[#bde0fe]/25 md:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-[#2b3a67] md:text-4xl">Selamat datang, {{ Auth::user()->name }}!</h2>
                </div>
            </div>
        </section>

        @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
            <section class="rounded-[2.25rem] border-4 border-white bg-[#fff5eb] p-6 shadow-lg shadow-amber-500/15">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-black uppercase tracking-[0.3em] text-amber-500">Perlu perhatian</p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-amber-600">Stok produk mulai menipis</h3>
                        <p class="mt-2 text-sm font-bold text-amber-700">Beberapa produk akun atau nomor sudah menyentuh batas pengingat stok. Restock lebih cepat supaya order tetap lancar.</p>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="inline-flex items-center justify-center rounded-full border-2 border-white bg-white px-5 py-3 text-sm font-black text-amber-600 shadow-sm transition hover:border-amber-200">
                        Kelola produk
                    </a>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach($lowStockProducts as $lowStock)
                        <a href="{{ route('admin.credentials.index', $lowStock->id) }}" class="inline-flex items-center gap-3 rounded-[1.2rem] border-2 border-white bg-white px-4 py-3 text-sm font-black text-amber-600 shadow-sm transition hover:border-amber-200">
                            <span class="line-clamp-1">{{ $lowStock->name }}</span>
                            <span class="rounded-full bg-[#ffe5e5] px-3 py-1 text-[11px] text-[#ff6b6b]">Sisa {{ $lowStock->available_stock }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <article class="overflow-hidden rounded-[2rem] border-4 border-white bg-white p-6 shadow-lg shadow-[#bde0fe]/20 transition hover:-translate-y-1">
                <div class="flex h-14 w-14 items-center justify-center rounded-[1.4rem] border-2 border-white bg-[#f0f5ff] text-[#5a76c8] shadow-inner">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <p class="mt-5 text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Transaksi bulan ini</p>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#2b3a67]">{{ number_format($totalTransactionsMonth ?? 0, 0, ',', '.') }}</p>
            </article>

            <article class="overflow-hidden rounded-[2rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] p-6 text-white shadow-lg shadow-[#5a76c8]/30 transition hover:-translate-y-1">
                <div class="flex h-14 w-14 items-center justify-center rounded-[1.4rem] border-2 border-white/30 bg-white/15 shadow-inner">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="mt-5 text-xs font-black uppercase tracking-[0.3em] text-white/80">Pendapatan bulan ini</p>
                <p class="mt-2 text-3xl font-black tracking-tight">Rp {{ number_format($revenueMonth ?? 0, 0, ',', '.') }}</p>
            </article>

            <article class="overflow-hidden rounded-[2rem] border-4 border-white bg-white p-6 shadow-lg shadow-[#bde0fe]/20 transition hover:-translate-y-1">
                <div class="flex h-14 w-14 items-center justify-center rounded-[1.4rem] border-2 border-white bg-[#e6fff7] text-emerald-500 shadow-inner">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <p class="mt-5 text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Total pelanggan</p>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#2b3a67]">{{ number_format($totalUsers ?? 0, 0, ',', '.') }}</p>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-8 xl:grid-cols-2">
            <article class="overflow-hidden rounded-[2.5rem] border-4 border-white bg-white shadow-lg shadow-[#bde0fe]/20">
                <div class="flex flex-col gap-4 border-b-2 border-dashed border-[#f0f5ff] bg-[#f4f9ff] px-5 py-5 md:flex-row md:items-center md:justify-between md:px-8 md:py-6">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Realtime order</p>
                        <h3 class="mt-1 text-2xl font-black tracking-tight text-[#2b3a67]">Transaksi terbaru</h3>
                    </div>
                    <a href="{{ route('admin.transactions.index') }}" class="inline-flex items-center justify-center rounded-full border border-white bg-[#5a76c8] px-5 py-2.5 text-xs font-black text-white shadow-md shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                        Lihat semua
                    </a>
                </div>

                <div class="p-4 md:p-5">
                    <div class="space-y-4 md:hidden">
                        @forelse(collect($recentTransactions ?? [])->take(5) as $trx)
                            @php($method = $paymentMethodMeta($trx))
                            <div class="rounded-[1.75rem] border-2 border-white bg-[#f8fbff] p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="break-all text-sm font-black text-[#2b3a67]">{{ $trx->invoice_number }}</p>
                                        <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ optional($trx->created_at)->diffForHumans() ?? '-' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-3 py-1 text-[11px] font-black {{ $method['class'] }}">{{ $method['label'] }}</span>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <div class="rounded-[1.2rem] bg-white px-4 py-3">
                                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-[#8faaf3]">User</p>
                                        <p class="mt-1 line-clamp-1 text-sm font-black text-[#2b3a67]">{{ $trx->user->name ?? 'Guest' }}</p>
                                    </div>
                                    <div class="rounded-[1.2rem] bg-white px-4 py-3">
                                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-[#8faaf3]">Nominal</p>
                                        <p class="mt-1 text-sm font-black text-emerald-500">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-[1.75rem] border-2 border-dashed border-[#d6e5ff] bg-[#f8fbff] px-5 py-10 text-center">
                                <p class="text-sm font-black text-[#2b3a67]">Belum ada transaksi.</p>
                                <p class="mt-2 text-xs font-bold text-[#8faaf3]">Data order terbaru akan muncul di sini.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full text-left">
                            <thead>
                                <tr class="text-[11px] font-black uppercase tracking-[0.25em] text-[#8faaf3]">
                                    <th class="px-4 py-3">Invoice</th>
                                    <th class="px-4 py-3">User</th>
                                    <th class="px-4 py-3">Nominal</th>
                                    <th class="px-4 py-3 text-center">Metode</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                                @forelse(collect($recentTransactions ?? [])->take(5) as $trx)
                                    @php($method = $paymentMethodMeta($trx))
                                    <tr class="transition hover:bg-[#f8fbff]">
                                        <td class="px-4 py-4">
                                            <p class="text-sm font-black text-[#2b3a67]">{{ $trx->invoice_number }}</p>
                                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ optional($trx->created_at)->diffForHumans() ?? '-' }}</p>
                                        </td>
                                        <td class="px-4 py-4 text-sm font-black text-[#2b3a67]">{{ $trx->user->name ?? 'Guest' }}</td>
                                        <td class="px-4 py-4 text-sm font-black text-emerald-500">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-black {{ $method['class'] }}">{{ $method['label'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-14 text-center">
                                            <p class="text-sm font-black text-[#2b3a67]">Belum ada transaksi.</p>
                                            <p class="mt-2 text-xs font-bold text-[#8faaf3]">Data order terbaru akan muncul di sini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>

            <article class="overflow-hidden rounded-[2.5rem] border-4 border-white bg-white shadow-lg shadow-[#bde0fe]/20">
                <div class="flex flex-col gap-4 border-b-2 border-dashed border-[#f0f5ff] bg-[#fffcf0] px-5 py-5 md:flex-row md:items-center md:justify-between md:px-8 md:py-6">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.3em] text-amber-500">Arus saldo</p>
                        <h3 class="mt-1 text-2xl font-black tracking-tight text-[#2b3a67]">Mutasi saldo global</h3>
                    </div>
                    <a href="{{ route('admin.deposits.index') }}" class="inline-flex items-center justify-center rounded-full border border-white bg-amber-500 px-5 py-2.5 text-xs font-black text-white shadow-md shadow-amber-500/25 transition hover:bg-amber-600">
                        Lihat semua
                    </a>
                </div>

                <div class="p-4 md:p-5">
                    <div class="space-y-4 md:hidden">
                        @forelse(collect($globalWalletLogs ?? [])->take(5) as $log)
                            @php($logMeta = $walletLogMeta($log))
                            <div class="rounded-[1.75rem] border-2 border-white bg-[#fffdf7] p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="break-all text-sm font-black text-[#2b3a67]">{{ $log->invoice_number ?? '-' }}</p>
                                        <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ optional($log->created_at)->diffForHumans() ?? '-' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-3 py-1 text-[11px] font-black {{ $logMeta['class'] }}">{{ $logMeta['label'] }}</span>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <div class="rounded-[1.2rem] bg-white px-4 py-3">
                                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-[#8faaf3]">User</p>
                                        <p class="mt-1 line-clamp-1 text-sm font-black text-[#2b3a67]">{{ $log->user->name ?? 'Deleted' }}</p>
                                    </div>
                                    <div class="rounded-[1.2rem] bg-white px-4 py-3">
                                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-[#8faaf3]">Nominal</p>
                                        <p class="mt-1 text-sm font-black {{ $log->type === 'purchase' ? 'text-[#ff6b6b]' : 'text-emerald-500' }}">
                                            {{ $log->type === 'purchase' ? '-' : '+' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                @if($log->description)
                                    <p class="mt-3 text-xs font-bold text-slate-500">{{ $log->description }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-[1.75rem] border-2 border-dashed border-[#f5dfb7] bg-[#fffdf7] px-5 py-10 text-center">
                                <p class="text-sm font-black text-[#2b3a67]">Belum ada mutasi saldo.</p>
                                <p class="mt-2 text-xs font-bold text-[#8faaf3]">Riwayat saldo global akan muncul di sini.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full text-left">
                            <thead>
                                <tr class="text-[11px] font-black uppercase tracking-[0.25em] text-[#8faaf3]">
                                    <th class="px-4 py-3">Invoice</th>
                                    <th class="px-4 py-3">User</th>
                                    <th class="px-4 py-3">Nominal</th>
                                    <th class="px-4 py-3 text-center">Tipe</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                                @forelse(collect($globalWalletLogs ?? [])->take(5) as $log)
                                    @php($logMeta = $walletLogMeta($log))
                                    <tr class="transition hover:bg-[#fffdf7]">
                                        <td class="px-4 py-4">
                                            <p class="text-sm font-black text-[#2b3a67]">{{ $log->invoice_number ?? '-' }}</p>
                                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ optional($log->created_at)->diffForHumans() ?? '-' }}</p>
                                        </td>
                                        <td class="px-4 py-4 text-sm font-black text-[#2b3a67]">{{ $log->user->name ?? 'Deleted' }}</td>
                                        <td class="px-4 py-4 text-sm font-black {{ $log->type === 'purchase' ? 'text-[#ff6b6b]' : 'text-emerald-500' }}">
                                            {{ $log->type === 'purchase' ? '-' : '+' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-black {{ $logMeta['class'] }}" title="{{ $log->description }}">
                                                {{ $logMeta['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-14 text-center">
                                            <p class="text-sm font-black text-[#2b3a67]">Belum ada mutasi saldo.</p>
                                            <p class="mt-2 text-xs font-bold text-[#8faaf3]">Riwayat saldo global akan muncul di sini.</p>
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
