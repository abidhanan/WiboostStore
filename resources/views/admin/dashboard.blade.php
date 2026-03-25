@extends('layouts.admin')
@section('title', 'Dashboard Wiboost')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12">
    <div class="mb-10 pl-2">
        <h2 class="text-3xl font-black text-[#2b3a67] tracking-tight">Selamat Datang, {{ Auth::user()->name }}! 👋</h2>
        <p class="text-sm font-bold text-[#8faaf3] mt-1">Ringkasan performa bisnis Wiboost Store bulan ini.</p>
    </div>

    @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
        <div class="bg-[#fff5eb] border-4 border-white text-amber-600 px-6 py-5 rounded-[2rem] mb-10 shadow-lg shadow-amber-500/20 relative overflow-hidden group">
            <div class="absolute -right-4 -top-8 text-8xl opacity-10 transform rotate-12 pointer-events-none group-hover:scale-110 transition-transform">⚠️</div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-2xl animate-pulse">🚨</span>
                    <h3 class="font-black text-xl tracking-tight">Waspada Stok Menipis!</h3>
                </div>
                <p class="text-sm font-bold mb-4">Beberapa produk aplikasi premium/nomor luar ini sudah menyentuh batas stok minimum. Segera restock agar pelanggan tidak kecewa.</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($lowStockProducts as $lowStock)
                        <a href="{{ route('admin.credentials.index', $lowStock->id) }}" class="bg-white border-2 border-amber-200 hover:border-amber-400 text-amber-600 px-4 py-2 rounded-xl text-xs font-black transition-colors shadow-sm inline-flex items-center gap-2">
                            {{ $lowStock->name }}
                            <span class="bg-[#ffe5e5] text-[#ff6b6b] px-2 py-0.5 rounded-md text-[10px]">Sisa: {{ $lowStock->available_stock }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        
        <div class="bg-white rounded-[2rem] p-6 border-4 border-white shadow-lg shadow-[#bde0fe]/20 relative overflow-hidden flex flex-col justify-between group hover:-translate-y-1 transition-all">
            <div class="relative z-10">
                <div class="w-14 h-14 bg-[#f0f5ff] text-[#5a76c8] rounded-2xl flex items-center justify-center mb-4 border-2 border-white shadow-inner group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <p class="text-[#8faaf3] text-xs font-black uppercase tracking-widest mb-1">Transaksi Bulan Ini</p>
                <h3 class="text-2xl font-black text-[#2b3a67] tracking-tight mt-2">{{ number_format($totalTransactionsMonth ?? 0, 0, ',', '.') }} <span class="text-sm font-bold text-[#8faaf3]">Pesanan</span></h3>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-5 text-7xl transform -rotate-12 pointer-events-none">📦</div>
        </div>

        <div class="bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] rounded-[2rem] p-6 text-white shadow-lg shadow-[#5a76c8]/30 relative overflow-hidden border-4 border-white transition-transform hover:-translate-y-1 group">
            <div class="relative z-10">
                <div class="w-14 h-14 bg-white/20 text-white rounded-2xl flex items-center justify-center mb-4 border-2 border-white/30 shadow-inner group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-[#e0fbfc] text-xs font-black uppercase tracking-widest mb-1">Pendapatan Bulan Ini</p>
                <h3 class="text-3xl font-black tracking-tight mt-2 drop-shadow-sm">Rp {{ number_format($revenueMonth ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-20 text-7xl transform -rotate-12 pointer-events-none">💰</div>
        </div>

        <div class="bg-white rounded-[2rem] p-6 border-4 border-white shadow-lg shadow-[#bde0fe]/20 relative overflow-hidden flex flex-col justify-between group hover:-translate-y-1 transition-all">
            <div class="relative z-10">
                <div class="w-14 h-14 bg-[#e6fff7] text-emerald-500 rounded-2xl flex items-center justify-center mb-4 border-2 border-white shadow-inner group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <p class="text-[#8faaf3] text-xs font-black uppercase tracking-widest mb-1">Total Pelanggan</p>
                <h3 class="text-2xl font-black text-[#2b3a67] tracking-tight mt-2">{{ number_format($totalUsers ?? 0, 0, ',', '.') }} <span class="text-sm font-bold text-[#8faaf3]">User</span></h3>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-5 text-7xl transform -rotate-12 pointer-events-none">👥</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
        
        <div class="bg-white rounded-[2.5rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white flex flex-col overflow-hidden h-full">
            <div class="px-5 py-5 md:px-8 md:py-6 border-b-2 border-dashed border-[#f0f5ff] flex justify-between items-center bg-[#f4f9ff]">
                <h4 class="font-black text-[#2b3a67] text-lg flex items-center gap-2">
                    <span class="text-2xl">⚡</span> Transaksi Terbaru
                </h4>
                <a href="{{ route('admin.transactions.index') }}" class="text-[10px] md:text-xs font-black text-white bg-[#5a76c8] px-4 md:px-5 py-2.5 rounded-full hover:bg-[#4760a9] transition shadow-md shadow-[#5a76c8]/30 border border-white whitespace-nowrap">Lihat Semua &rarr;</a>
            </div>
            
            <div class="overflow-hidden flex-1 p-2 md:p-4 w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="px-2 md:px-4 py-3 text-[9px] md:text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Invoice</th>
                            <th class="px-2 md:px-4 py-3 text-[9px] md:text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">User</th>
                            <th class="px-2 md:px-4 py-3 text-[9px] md:text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Nominal</th>
                            <th class="px-2 md:px-4 py-3 text-[9px] md:text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Metode</th> 
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                        @forelse(collect($recentTransactions ?? [])->take(5) as $trx)
                        <tr class="hover:bg-[#f4f9ff] transition-colors rounded-xl group">
                            <td class="px-2 md:px-4 py-4 align-middle w-[30%]">
                                <p class="font-black text-[#5a76c8] text-[11px] md:text-sm mb-1 leading-tight break-all">{{ $trx->invoice_number }}</p>
                                <p class="text-[9px] md:text-[10px] font-bold text-[#8faaf3]">{{ $trx->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-2 md:px-4 py-4 align-middle w-[25%]">
                                <p class="font-black text-[#2b3a67] text-xs md:text-sm truncate max-w-[60px] md:max-w-[120px]" title="{{ $trx->user->name ?? 'Guest' }}">{{ $trx->user->name ?? 'Guest' }}</p>
                            </td>
                            <td class="px-2 md:px-4 py-4 align-middle w-[25%] whitespace-nowrap">
                                <p class="font-black text-[#4bc6b9] text-xs md:text-sm">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                            </td>
                            <td class="px-2 md:px-4 py-4 align-middle text-center w-[20%] whitespace-nowrap">
                                @php
                                    $rawMethod = strtolower($trx->payment_method ?? '');
                                    
                                    if ($rawMethod == 'wallet') {
                                        $methodDisplay = 'SALDO';
                                        $colorClass = 'bg-[#e0fbfc] text-[#5a76c8]';
                                    } elseif (($rawMethod == 'manual' || empty($rawMethod)) && $trx->payment_status != 'paid') {
                                        $methodDisplay = 'MENUNGGU';
                                        $colorClass = 'bg-[#fff5eb] text-amber-500';
                                    } else {
                                        // Pemetaan Manual
                                        $methodMap = [
                                            'qris'           => 'QRIS',
                                            'gopay'          => 'GoPay',
                                            'shopeepay'      => 'ShopeePay',
                                            'bank_transfer'  => 'Bank Transfer',
                                            'cstore'         => 'Indomaret/ALFA',
                                            'credit_card'    => 'Kartu Kredit',
                                            'echannel'       => 'Mandiri Bill',
                                            'permata_va'     => 'Permata VA',
                                            'bca_va'         => 'BCA Transfer',
                                            'bni_va'         => 'BNI Transfer',
                                            'bri_va'         => 'BRI Transfer',
                                            'cimb_va'        => 'CIMB Transfer',
                                            'other_va'       => 'ATM Bersama',
                                            'danamon_online' => 'Danamon Online',
                                            'akulaku'        => 'Akulaku',
                                            'kredivo'        => 'Kredivo',
                                        ];

                                        $methodDisplay = $methodMap[$rawMethod] ?? ucwords(str_replace('_', ' ', $rawMethod));
                                        if (empty($methodDisplay) || $methodDisplay == 'Manual') $methodDisplay = 'E-WALLET';
                                        
                                        $colorClass = 'bg-[#f4f9ff] text-[#8faaf3]';
                                    }
                                @endphp
                                <span class="text-[8px] md:text-[9px] font-black uppercase tracking-widest px-2 md:px-3 py-1.5 rounded-full border border-white shadow-sm inline-block {{ $colorClass }}">
                                    {{ $methodDisplay }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-[2rem] bg-[#f0f5ff] border-4 border-white mb-3 shadow-inner"><span class="text-2xl">📭</span></div>
                                <p class="text-[#8faaf3] font-black text-xs">Belum ada transaksi.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white flex flex-col overflow-hidden h-full">
            <div class="px-5 py-5 md:px-8 md:py-6 border-b-2 border-dashed border-[#f0f5ff] flex justify-between items-center bg-[#fffcf0]">
                <h4 class="font-black text-[#2b3a67] text-lg flex items-center gap-2">
                    <span class="text-2xl">💸</span> Mutasi Saldo Global
                </h4>
                <a href="{{ route('admin.deposits.index') }}" class="text-[10px] md:text-xs font-black text-white bg-amber-500 px-4 md:px-5 py-2.5 rounded-full hover:bg-amber-600 transition shadow-md shadow-amber-500/30 border border-white whitespace-nowrap">Lihat Semua &rarr;</a>
            </div>
            
            <div class="overflow-hidden flex-1 p-2 md:p-4 w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="px-2 md:px-4 py-3 text-[9px] md:text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Invoice</th>
                            <th class="px-2 md:px-4 py-3 text-[9px] md:text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">User</th>
                            <th class="px-2 md:px-4 py-3 text-[9px] md:text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Nominal</th>
                            <th class="px-2 md:px-4 py-3 text-[9px] md:text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Tipe</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                        @php 
                            if(class_exists('\App\Models\WalletHistory')) {
                                $globalLogs = App\Models\WalletHistory::with('user')->latest()->take(5)->get(); 
                            } else {
                                $globalLogs = collect();
                            }
                        @endphp
                        
                        @forelse($globalLogs as $log)
                        <tr class="hover:bg-[#f4f9ff] transition-colors rounded-xl group">
                            <td class="px-2 md:px-4 py-4 align-middle w-[30%]">
                                <p class="font-black text-[#5a76c8] text-[11px] md:text-sm mb-1 leading-tight break-all">{{ $log->invoice_number ?? '-' }}</p>
                                <p class="text-[9px] md:text-[10px] font-bold text-[#8faaf3]">{{ $log->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-2 md:px-4 py-4 align-middle w-[25%]">
                                <p class="font-black text-[#2b3a67] text-xs md:text-sm truncate max-w-[60px] md:max-w-[120px]" title="{{ $log->user->name ?? 'Deleted' }}">{{ $log->user->name ?? 'Deleted' }}</p>
                            </td>
                            
                            <td class="px-2 md:px-4 py-4 align-middle w-[25%] whitespace-nowrap">
                                @if($log->type == 'purchase')
                                    <p class="font-black text-[11px] md:text-xs text-[#ff6b6b]">
                                        - Rp {{ number_format($log->amount, 0, ',', '.') }}
                                    </p>
                                @else
                                    <p class="font-black text-[11px] md:text-xs text-emerald-500">
                                        + Rp {{ number_format($log->amount, 0, ',', '.') }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-2 md:px-4 py-4 align-middle text-center w-[20%] whitespace-nowrap">
                                @php
                                    $displayType = $log->type;
                                    if (str_contains($log->invoice_number, 'POIN')) {
                                        $displayType = 'poin';
                                    }

                                    $logClasses = [
                                        'refund'   => ['bg-[#e6fff7]', 'text-emerald-500', '↺ Refund'],
                                        'topup'    => ['bg-[#f0f5ff]', 'text-[#5a76c8]', '💰 Top Up'],
                                        'purchase' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', '🛍️ Beli'],
                                        'poin'     => ['bg-[#fff5eb]', 'text-amber-500', '🎁 Poin'],
                                    ];
                                    $st = $logClasses[$displayType] ?? ['bg-gray-100', 'text-gray-500', strtoupper($displayType)];
                                @endphp
                                <span class="{{ $st[0] }} {{ $st[1] }} px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white shadow-sm inline-block min-w-[90px] text-center" title="{{ $log->description }}">
                                    {{ $st[2] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-[2rem] bg-[#fff9f0] border-4 border-white mb-3 shadow-inner"><span class="text-2xl">💤</span></div>
                                <p class="text-[#8faaf3] font-black text-xs">Belum ada mutasi saldo.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection