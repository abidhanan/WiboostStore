@extends('layouts.admin')

@section('title', 'Dashboard Wiboost')

@section('content')
<div class="mb-8">
    <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Selamat Datang, {{ Auth::user()->name }}! 👋</h2>
    <p class="text-sm text-slate-500 mt-1">Berikut adalah ringkasan performa Wiboost Store hari ini.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <div class="bg-indigo-600 rounded-3xl p-6 text-white shadow-lg shadow-indigo-200 relative overflow-hidden">
        <div class="relative z-10">
            <p class="text-indigo-100 text-xs font-bold uppercase tracking-widest mb-1">Cuan Hari Ini</p>
            <h3 class="text-3xl font-extrabold tracking-tight mt-2">Rp {{ number_format($revenueToday, 0, ',', '.') }}</h3>
        </div>
        <div class="absolute -right-4 -bottom-4 opacity-20">
            <svg width="100" height="100" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Total Pendapatan</p>
        </div>
        <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-2">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Pesanan Menggantung</p>
        </div>
        <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-2">{{ $pendingOrders }} <span class="text-sm font-medium text-slate-400">Pesanan</span></h3>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Total Pelanggan</p>
        </div>
        <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-2">{{ $totalUsers }} <span class="text-sm font-medium text-slate-400">User</span></h3>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-8">
    <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
        <h4 class="font-bold text-slate-800">5 Transaksi Terbaru</h4>
        <a href="{{ route('admin.transactions.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">Lihat Semua &rarr;</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left whitespace-nowrap">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Invoice</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Pelanggan</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Produk</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nominal</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentTransactions as $trx)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4 font-bold text-indigo-600 text-sm">{{ $trx->invoice_number }}</td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-800 text-sm">{{ $trx->user->name ?? 'Guest' }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $trx->product->name ?? 'Produk Dihapus' }}</td>
                    <td class="px-6 py-4 font-bold text-slate-900 text-sm">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($trx->payment_status == 'paid')
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border border-emerald-200">Lunas</span>
                        @elseif($trx->payment_status == 'failed')
                            <span class="bg-rose-100 text-rose-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border border-rose-200">Gagal</span>
                        @else
                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border border-amber-200">Pending</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-slate-500 text-sm font-medium">Belum ada transaksi hari ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection