@extends('layouts.admin')

@section('title', 'Mutasi Saldo Global')
@section('admin_header_subtitle', 'Pantau seluruh arus kas pelanggan: Top Up, Beli, Refund, dan Tukar Poin.')
@section('admin_header_actions')
    <a href="{{ route('admin.deposits.index') }}" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-white px-6 py-3 font-black text-[#5a76c8] shadow-lg shadow-[#bde0fe]/30 transition-transform active:scale-95 hover:bg-[#f4f9ff] sm:w-auto">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        Segarkan Data
    </a>
@endsection

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

@php
    $search = request('search');
    $logs = \App\Models\WalletHistory::with('user')
        ->when($search, function($query, $search) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        })
        ->latest()
        ->paginate(20);
@endphp

<div class="wiboost-font pb-12 relative z-10">

    <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">💸</div>
    <div class="absolute top-1/3 left-5 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 right-[15%] text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="bg-white/90 backdrop-blur-sm p-6 rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white mb-8 relative z-10 transition-transform duration-300 hover:border-[#bde0fe]">
        <form action="{{ route('admin.deposits.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-6 text-[#8faaf3]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="w-full pl-16 pr-5 py-4 bg-[#f4f9ff] rounded-[1.5rem] border-4 border-white focus:border-[#bde0fe] outline-none transition text-[#2b3a67] font-black placeholder-[#a3bbfb] shadow-inner" 
                       placeholder="Cari Nomor Invoice, Keterangan, atau Nama Pelanggan...">
            </div>
            <button type="submit" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white px-10 py-4 rounded-full font-black transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white whitespace-nowrap">
                Cari Transaksi 🚀
            </button>
            @if(request('search'))
                <a href="{{ route('admin.deposits.index') }}" class="bg-[#ffe5e5] hover:bg-[#ffcccc] text-[#ff6b6b] px-8 py-4 rounded-full font-black transition-transform active:scale-95 flex items-center justify-center border-4 border-white whitespace-nowrap shadow-md shadow-[#ff6b6b]/20">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/30 border-4 border-white overflow-hidden relative z-10">
        <div class="overflow-x-auto p-4 md:p-6">
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Invoice & Waktu</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Pelanggan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Nominal</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Aktivitas / Detail</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff] text-center">Tipe</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff] text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                    @forelse($logs as $log)
                    
                    @php
                        $activityText = $log->description;
                        if ($log->type === 'topup') {
                            if (class_exists('\App\Models\Deposit')) {
                                $deposit = \App\Models\Deposit::where('invoice_number', $log->invoice_number)->first();
                                if ($deposit && $deposit->payment_method) {
                                    $method = str_replace('_', ' ', $deposit->payment_method);
                                    $method = ucwords($method);
                                    if (strtolower($method) == 'qris') $method = 'QRIS';
                                    if (strtolower($method) == 'gopay') $method = 'GoPay';
                                    if (strtolower($method) == 'shopeepay') $method = 'ShopeePay';
                                    
                                    $activityText = "Top Up via " . $method;
                                }
                            }
                        } elseif ($log->type === 'refund') {
                            $desc = $log->description;
                            if (str_starts_with($desc, 'Refund Pembelian Gagal:')) {
                                $productName = trim(str_replace('Refund Pembelian Gagal:', '', $desc));
                                $activityText = "Refund: Sistem Gagal ( " . $productName . " )";
                            } elseif (str_starts_with($desc, 'Refund Otomatis (Pending > 24 Jam):')) {
                                $productName = trim(str_replace('Refund Otomatis (Pending > 24 Jam):', '', $desc));
                                $activityText = "Refund: Kehabisan Stok ( " . $productName . " )";
                            }
                        } elseif ($log->type === 'poin') {
                            $activityText = "Bonus Penukaran Poin Loyalty";
                        }
                    @endphp

                    <tr class="hover:bg-[#f8faff] transition-colors rounded-xl group">
                        
                        <td class="px-6 py-5">
                            <p class="font-black text-[#5a76c8] text-sm mb-1 bg-[#f4f9ff] inline-block px-3 py-1 rounded-md border border-white whitespace-nowrap">{{ $log->invoice_number ?? 'Tanpa Invoice' }}</p>
                            <p class="text-[10px] font-bold text-[#8faaf3] whitespace-nowrap ml-1">{{ $log->created_at->format('d M Y, H:i') }}</p>
                        </td>

                        <td class="px-6 py-5">
                            <p class="font-black text-[#2b3a67] text-sm leading-tight line-clamp-1 min-w-[120px]">{{ $log->user->name ?? 'User Dihapus' }}</p>
                        </td>

                        <td class="px-6 py-5">
                            @if($log->type == 'purchase')
                                <p class="font-black text-sm text-[#ff6b6b] whitespace-nowrap drop-shadow-sm">
                                    - Rp {{ number_format($log->amount, 0, ',', '.') }}
                                </p>
                            @else
                                <p class="font-black text-sm text-emerald-500 whitespace-nowrap drop-shadow-sm">
                                    + Rp {{ number_format($log->amount, 0, ',', '.') }}
                                </p>
                            @endif
                        </td>

                        <td class="px-6 py-5 whitespace-normal min-w-[200px]">
                            <p class="font-bold text-[#4a5f96] text-xs leading-tight bg-[#f8faff] px-4 py-2 rounded-xl border border-[#e0fbfc]" title="{{ $activityText }}">{{ $activityText }}</p>
                        </td>

                        <td class="px-6 py-5 text-center">
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
                            <span class="{{ $st[0] }} {{ $st[1] }} text-[9px] font-black uppercase px-4 py-2 rounded-full border-2 border-white shadow-sm inline-flex items-center justify-center gap-1.5 min-w-[90px] whitespace-nowrap">
                                {{ $st[2] }}
                            </span>
                        </td>

                        <td class="px-6 py-5 text-center">
                            @php
                                $statusLabels = [
                                    'success' => ['bg-[#e6fff7]', 'text-emerald-500', '✅ Sukses'],
                                ];
                                $stStatus = $statusLabels['success'];
                            @endphp
                            <span class="{{ $stStatus[0] }} {{ $stStatus[1] }} px-4 py-2 rounded-full text-[9px] font-black uppercase tracking-widest border-2 border-white shadow-sm inline-flex items-center justify-center gap-1.5 min-w-[96px] whitespace-nowrap">
                                {{ $stStatus[2] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="inline-flex items-center justify-center w-24 h-24 rounded-[2.5rem] bg-[#fff9f0] border-4 border-white mb-5 shadow-inner">
                                <span class="text-5xl animate-float">💤</span>
                            </div>
                            <p class="text-[#5a76c8] font-black text-xl">Belum ada riwayat mutasi saldo.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="p-6 md:p-8 border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff]">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection