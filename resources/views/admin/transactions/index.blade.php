@extends('layouts.admin')

@section('title', 'Riwayat Transaksi')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4 pl-2">
        <div>
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Manajemen Transaksi 🧾</h3>
            <p class="text-sm text-[#8faaf3] font-bold mt-1">Pantau seluruh pesanan pelanggan Wiboost Store di sini.</p>
        </div>
        <a href="{{ route('admin.transactions.index') }}" class="bg-white text-[#5a76c8] px-6 py-3 rounded-full font-black hover:bg-[#f0f5ff] hover:-translate-y-1 transition-all flex items-center gap-2 shadow-lg shadow-[#bde0fe]/30 border-4 border-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            Segarkan Data
        </a>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">🎉</span> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
        
        <div class="bg-white p-5 rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white flex items-center h-full">
            <form action="{{ route('admin.transactions.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-[#8faaf3]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="w-full pl-12 pr-4 py-3 bg-[#f4f9ff] rounded-[1rem] border-2 border-[#e0fbfc] focus:border-[#5a76c8] outline-none transition text-[#2b3a67] font-black text-sm placeholder-[#a3bbfb]" 
                           placeholder="Cari Invoice...">
                </div>
                <button type="submit" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white px-6 py-3 rounded-[1rem] font-black transition-transform active:scale-95 shadow-md shadow-[#5a76c8]/30 border-2 border-white whitespace-nowrap text-sm">Cari</button>
                @if(request('search'))
                    <a href="{{ route('admin.transactions.index') }}" class="bg-[#ffe5e5] hover:bg-[#ffcccc] text-[#ff6b6b] px-4 py-3 rounded-[1rem] font-black transition text-sm flex items-center border-2 border-white">Reset</a>
                @endif
            </form>
        </div>

        <div class="bg-gradient-to-r from-[#e0fbfc] to-[#f4f9ff] p-5 rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white flex items-center justify-between gap-4 h-full">
            <div class="hidden sm:block">
                <h4 class="font-black text-[#2b3a67] text-md">Cetak Laporan 🖨️</h4>
                <p class="text-[10px] font-bold text-[#8faaf3]">Unduh PDF bulanan.</p>
            </div>
            <form action="{{ route('admin.transactions.export_pdf') }}" method="GET" class="flex flex-1 sm:flex-none items-center gap-2">
                <select name="month" class="bg-white border-2 border-white focus:border-[#5a76c8] rounded-[1rem] px-3 py-3 text-[#2b3a67] font-black outline-none transition appearance-none cursor-pointer text-sm shadow-sm flex-1">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ sprintf('%02d', $m) }}" {{ date('m') == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
                <select name="year" class="bg-white border-2 border-white focus:border-[#5a76c8] rounded-[1rem] px-3 py-3 text-[#2b3a67] font-black outline-none transition appearance-none cursor-pointer text-sm shadow-sm w-24">
                    @for($y=date('Y'); $y>=date('Y')-3; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="bg-[#4bc6b9] hover:bg-[#3ba398] text-white px-5 py-3 rounded-[1rem] font-black transition-transform active:scale-95 shadow-md shadow-[#4bc6b9]/30 border-2 border-white flex items-center justify-center" title="Download PDF">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white overflow-hidden">
        <div class="overflow-x-auto p-4">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Waktu & Invoice</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Pelanggan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Pesanan & Target</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Nominal</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Bayar</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Update Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-[#f4f9ff] transition-colors rounded-xl">
                        <td class="px-6 py-4">
                            <p class="font-black text-[#5a76c8] text-sm mb-0.5">{{ $trx->invoice_number }}</p>
                            <p class="text-[10px] font-bold text-[#8faaf3]">{{ $trx->created_at->format('d M Y, H:i') }}</p>
                        </td>
                        <td class="px-6 py-4 min-w-[200px]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#f0f5ff] text-[#5a76c8] flex items-center justify-center font-black text-xs uppercase border-2 border-white shadow-inner shrink-0">
                                    {{ substr($trx->user->name ?? '?', 0, 2) }}
                                </div>
                                <div>
                                    <p class="font-black text-[#2b3a67] text-sm truncate max-w-[150px]" title="{{ $trx->user->name ?? 'Guest' }}">{{ $trx->user->name ?? 'Guest' }}</p>
                                    <p class="text-[10px] font-bold text-[#8faaf3] truncate max-w-[150px]">{{ $trx->user->email ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 min-w-[200px] whitespace-normal">
                            <p class="font-black text-[#2b3a67] text-sm line-clamp-2 leading-tight">{{ $trx->product->name ?? 'Produk Dihapus' }}</p>
                            <p class="text-[10px] font-black text-[#5a76c8] mt-1 bg-[#f0f5ff] inline-block px-3 py-1 rounded-full border border-white shadow-inner truncate max-w-full">{{ $trx->target_data }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-black text-[#2b3a67] text-sm">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($trx->payment_status == 'paid')
                                <span class="bg-[#e6fff7] text-emerald-500 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white shadow-sm">Lunas</span>
                            @elseif($trx->payment_status == 'failed')
                                <span class="bg-[#ffe5e5] text-[#ff6b6b] px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white shadow-sm">Gagal</span>
                            @else
                                <span class="bg-[#fff5eb] text-amber-500 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white shadow-sm">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 min-w-[180px]">
                            <form action="{{ route('admin.transactions.update', $trx->id) }}" method="POST" class="flex items-center gap-2 justify-center">
                                @csrf
                                @method('PATCH')
                                <select name="order_status" class="bg-[#f0f5ff] border-2 border-white text-[#2b3a67] text-xs rounded-full focus:border-[#5a76c8] block px-4 py-2.5 font-black outline-none shadow-inner cursor-pointer hover:bg-[#e0fbfc] transition-colors appearance-none text-center">
                                    <option value="pending" {{ $trx->order_status == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                    <option value="processing" {{ $trx->order_status == 'processing' ? 'selected' : '' }}>⚙️ Diproses</option>
                                    <option value="success" {{ $trx->order_status == 'success' ? 'selected' : '' }}>✅ Sukses</option>
                                    <option value="failed" {{ $trx->order_status == 'failed' ? 'selected' : '' }}>❌ Gagal</option>
                                </select>
                                <button type="submit" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white p-3 rounded-full transition shadow-md active:scale-95 border-2 border-white shrink-0" title="Simpan Status">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-[#f0f5ff] border-4 border-white mb-4 shadow-inner">
                                <span class="text-4xl">📭</span>
                            </div>
                            <p class="text-[#8faaf3] font-black text-lg">Belum ada data transaksi.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($transactions->hasPages())
            <div class="p-6 border-t-2 border-dashed border-[#f0f5ff] bg-[#f4f9ff]">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection