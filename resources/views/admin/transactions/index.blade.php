@extends('layouts.admin')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">Manajemen Transaksi</h3>
        <p class="text-sm text-slate-500 mt-1">Pantau seluruh pesanan pelanggan Wiboost Store di sini.</p>
    </div>
    <a href="{{ route('admin.transactions.index') }}" class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl font-bold hover:bg-indigo-100 transition flex items-center gap-2 shadow-sm border border-indigo-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        Segarkan Data
    </a>
</div>

@if(session('success'))
    <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-4 py-3 rounded-xl mb-6 font-bold flex items-center gap-3">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
        {{ session('success') }}
    </div>
@endif

<div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
    <form action="{{ route('admin.transactions.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" 
                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm font-medium" 
                   placeholder="Cari Nomor Invoice atau Nama Pelanggan...">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold transition shadow-sm whitespace-nowrap">
            Cari Pesanan
        </button>
        @if(request('search'))
            <a href="{{ route('admin.transactions.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-3 rounded-xl font-bold transition flex items-center justify-center whitespace-nowrap">
                Reset
            </a>
        @endif
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left whitespace-nowrap">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Waktu & Invoice</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Pelanggan</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Pesanan & Target</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nominal</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-center">Status Pembayaran</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-center">Update Status Pesanan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $trx)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4">
                        <p class="font-bold text-indigo-600 text-sm mb-0.5">{{ $trx->invoice_number }}</p>
                        <p class="text-xs text-slate-500">{{ $trx->created_at->format('d M Y, H:i') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs uppercase border border-slate-200 shrink-0">
                                {{ substr($trx->user->name ?? '?', 0, 2) }}
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">{{ $trx->user->name ?? 'Guest' }}</p>
                                <p class="text-xs text-slate-500">{{ $trx->user->email ?? '-' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-800 text-sm truncate max-w-[200px]">{{ $trx->product->name ?? 'Produk Dihapus' }}</p>
                        <p class="text-xs font-mono text-indigo-600 mt-0.5 bg-indigo-50 border border-indigo-100 inline-block px-2 py-0.5 rounded">{{ $trx->target_data }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-extrabold text-slate-900 text-sm">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($trx->payment_status == 'paid')
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border border-emerald-200">Lunas</span>
                        @elseif($trx->payment_status == 'failed')
                            <span class="bg-rose-100 text-rose-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border border-rose-200">Gagal</span>
                        @else
                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border border-amber-200">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <form action="{{ route('admin.transactions.update', $trx->id) }}" method="POST" class="flex items-center gap-2 justify-center">
                            @csrf
                            @method('PATCH')
                            <select name="order_status" class="bg-white border border-slate-300 text-slate-700 text-xs rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block p-2 font-semibold outline-none shadow-sm cursor-pointer hover:border-indigo-400 transition">
                                <option value="pending" {{ $trx->order_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $trx->order_status == 'processing' ? 'selected' : '' }}>Diproses</option>
                                <option value="success" {{ $trx->order_status == 'success' ? 'selected' : '' }}>Sukses</option>
                                <option value="failed" {{ $trx->order_status == 'failed' ? 'selected' : '' }}>Gagal</option>
                            </select>
                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white p-2 rounded-lg transition shadow-sm" title="Simpan Status">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 border border-slate-100 mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                        <p class="text-slate-500 font-medium">Belum ada data transaksi yang ditemukan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($transactions->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection