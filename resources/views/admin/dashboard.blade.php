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
        <p class="text-sm font-bold text-[#8faaf3] mt-1">Berikut adalah ringkasan performa Wiboost Store hari ini.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        
        <div class="bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] rounded-[2rem] p-6 text-white shadow-lg shadow-[#5a76c8]/30 relative overflow-hidden border-4 border-white transition-transform hover:-translate-y-1">
            <div class="relative z-10">
                <p class="text-[#e0fbfc] text-xs font-black uppercase tracking-widest mb-1">Cuan Hari Ini</p>
                <h3 class="text-3xl font-black tracking-tight mt-2 drop-shadow-sm">Rp {{ number_format($revenueToday ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-20 text-7xl transform -rotate-12 pointer-events-none">💸</div>
        </div>

        <div class="bg-white rounded-[2rem] p-6 border-4 border-white shadow-lg shadow-[#bde0fe]/20 relative overflow-hidden flex flex-col justify-between group hover:-translate-y-1 transition-all">
            <div>
                <div class="w-14 h-14 bg-[#e6fff7] text-emerald-500 rounded-2xl flex items-center justify-center mb-4 border-2 border-white shadow-inner group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <p class="text-[#8faaf3] text-xs font-black uppercase tracking-widest mb-1">Total Pendapatan</p>
            </div>
            <h3 class="text-2xl font-black text-[#2b3a67] tracking-tight mt-2">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h3>
        </div>

        <div class="bg-white rounded-[2rem] p-6 border-4 border-white shadow-lg shadow-[#bde0fe]/20 relative overflow-hidden flex flex-col justify-between group hover:-translate-y-1 transition-all">
            <div>
                <div class="w-14 h-14 bg-[#fff5eb] text-amber-500 rounded-2xl flex items-center justify-center mb-4 border-2 border-white shadow-inner group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-[#8faaf3] text-xs font-black uppercase tracking-widest mb-1">Pesanan Pending</p>
            </div>
            <h3 class="text-2xl font-black text-[#2b3a67] tracking-tight mt-2">{{ $pendingOrders ?? 0 }} <span class="text-sm font-bold text-[#8faaf3]">Antrean</span></h3>
        </div>

        <div class="bg-white rounded-[2rem] p-6 border-4 border-white shadow-lg shadow-[#bde0fe]/20 relative overflow-hidden flex flex-col justify-between group hover:-translate-y-1 transition-all">
            <div>
                <div class="w-14 h-14 bg-[#f0f5ff] text-[#5a76c8] rounded-2xl flex items-center justify-center mb-4 border-2 border-white shadow-inner group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <p class="text-[#8faaf3] text-xs font-black uppercase tracking-widest mb-1">Total Pelanggan</p>
            </div>
            <h3 class="text-2xl font-black text-[#2b3a67] tracking-tight mt-2">{{ $totalUsers ?? 0 }} <span class="text-sm font-bold text-[#8faaf3]">User</span></h3>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white overflow-hidden mb-8">
        <div class="px-6 py-5 md:px-8 md:py-6 border-b-2 border-dashed border-[#f0f5ff] flex justify-between items-center bg-[#f4f9ff]">
            <h4 class="font-black text-[#2b3a67] text-lg flex items-center gap-2">
                <span class="text-2xl">⚡</span> 5 Transaksi Terbaru
            </h4>
            <a href="{{ route('admin.transactions.index') }}" class="text-[10px] md:text-xs font-black text-white bg-[#5a76c8] px-4 md:px-5 py-2.5 rounded-full hover:bg-[#4760a9] transition shadow-md shadow-[#5a76c8]/30 border border-white whitespace-nowrap">Lihat Semua &rarr;</a>
        </div>
        
        <div class="overflow-x-auto p-2 md:p-4">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr>
                        <th class="px-4 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Waktu & Invoice</th>
                        <th class="px-4 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Pelanggan</th>
                        <th class="px-4 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Layanan</th>
                        <th class="px-4 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Harga</th>
                        <th class="px-4 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                    @forelse($recentTransactions ?? [] as $trx)
                    <tr class="hover:bg-[#f4f9ff] transition-colors rounded-xl group">
                        
                        <td class="px-4 py-5 align-top">
                            <p class="font-black text-[#5a76c8] text-sm mb-1">{{ $trx->invoice_number }}</p>
                            <p class="text-[10px] font-bold text-[#8faaf3]">{{ $trx->created_at->diffForHumans() }}</p>
                        </td>
                        
                        <td class="px-4 py-5 align-top">
                            <p class="font-black text-[#2b3a67] text-sm truncate max-w-[120px] md:max-w-[150px]" title="{{ $trx->user->name ?? 'Guest' }}">{{ $trx->user->name ?? 'Guest' }}</p>
                            <p class="text-[10px] font-bold text-[#8faaf3] truncate max-w-[120px] md:max-w-[150px] mt-1">{{ $trx->user->email ?? '-' }}</p>
                        </td>
                        
                        <td class="px-4 py-5 align-top min-w-[180px] whitespace-normal">
                            <p class="font-black text-[#2b3a67] text-sm line-clamp-2 leading-snug">{{ $trx->product->name ?? 'Produk Dihapus' }}</p>
                        </td>

                        <td class="px-4 py-5 align-top">
                            <p class="font-black text-[#4bc6b9] text-sm">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                        </td>
                        
                        <td class="px-4 py-5 align-top text-center min-w-[120px]">
                            @if($trx->order_status == 'success')
                                <span class="bg-[#e6fff7] text-emerald-500 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white shadow-sm inline-block w-full text-center">✅ Sukses</span>
                            @elseif($trx->order_status == 'processing')
                                <span class="bg-[#e0fbfc] text-[#5a76c8] px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white shadow-sm inline-block w-full text-center">⚙️ Proses</span>
                            @elseif($trx->order_status == 'pending')
                                <span class="bg-[#fff5eb] text-amber-500 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white shadow-sm inline-block w-full text-center">⏳ Pending</span>
                            @else
                                <span class="bg-[#ffe5e5] text-[#ff6b6b] px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white shadow-sm inline-block w-full text-center">❌ Gagal</span>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-[2rem] bg-[#f0f5ff] border-4 border-white mb-4 shadow-inner">
                                <span class="text-3xl">📭</span>
                            </div>
                            <p class="text-[#8faaf3] font-black text-sm">Belum ada transaksi masuk hari ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection