@extends('layouts.admin')

@section('title', 'Manajemen Deposit')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4 pl-2">
        <div>
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Riwayat Top Up Saldo 💰</h3>
            <p class="text-sm text-[#8faaf3] font-bold mt-1">Pantau riwayat pengisian dompet digital pelanggan (Otomatis via API).</p>
        </div>
        <a href="{{ route('admin.deposits.index') }}" class="bg-white text-[#5a76c8] px-6 py-3 rounded-full font-black hover:bg-[#f0f5ff] hover:-translate-y-1 transition-all flex items-center gap-2 shadow-lg shadow-[#bde0fe]/30 border-4 border-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            Segarkan Data
        </a>
    </div>

    <div class="bg-white p-5 rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white mb-8">
        <form action="{{ route('admin.deposits.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-[#8faaf3]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="w-full pl-14 pr-5 py-4 bg-[#f4f9ff] rounded-[1.5rem] border-2 border-[#e0fbfc] focus:border-[#5a76c8] outline-none transition text-[#2b3a67] font-black placeholder-[#a3bbfb]" 
                       placeholder="Cari Nomor Invoice atau Nama Pelanggan...">
            </div>
            <button type="submit" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white px-10 py-4 rounded-[1.5rem] font-black transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white whitespace-nowrap">
                Cari Deposit
            </button>
            @if(request('search'))
                <a href="{{ route('admin.deposits.index') }}" class="bg-[#ffe5e5] hover:bg-[#ffcccc] text-[#ff6b6b] px-8 py-4 rounded-[1.5rem] font-black transition flex items-center justify-center border-2 border-white whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white overflow-hidden">
        <div class="overflow-x-auto p-4">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Waktu & Invoice</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Pelanggan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Nominal</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Metode</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                    @forelse($deposits as $deposit)
                    <tr class="hover:bg-[#f4f9ff] transition-colors rounded-xl group">
                        <td class="px-6 py-4">
                            <p class="font-black text-[#5a76c8] text-sm mb-0.5">{{ $deposit->invoice_number }}</p>
                            <p class="text-[10px] font-bold text-[#8faaf3]">{{ $deposit->created_at->format('d M Y, H:i') }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-black text-[#2b3a67] text-sm">{{ $deposit->user->name ?? 'Guest' }}</p>
                            <p class="text-[10px] font-bold text-[#8faaf3]">{{ $deposit->user->email ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-black text-[#5a76c8] text-sm">+ Rp {{ number_format($deposit->amount, 0, ',', '.') }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-[10px] font-black text-[#8faaf3] bg-[#f0f5ff] inline-block px-3 py-1.5 rounded-md border border-white shadow-inner">{{ strtoupper($deposit->payment_method ?? 'Menunggu') }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($deposit->payment_status == 'unpaid')
                                <span class="text-amber-500 text-[10px] font-black uppercase bg-[#fff5eb] px-3 py-1.5 rounded-full border border-white shadow-sm">⏳ Menunggu API</span>
                            @elseif($deposit->payment_status == 'paid')
                                <span class="text-emerald-500 text-[10px] font-black uppercase bg-[#e6fff7] px-3 py-1.5 rounded-full border border-white shadow-sm">✅ Selesai</span>
                            @else
                                <span class="text-[#ff6b6b] text-[10px] font-black uppercase bg-[#ffe5e5] px-3 py-1.5 rounded-full border border-white shadow-sm">❌ Gagal</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-[#f0f5ff] border-4 border-white mb-4 shadow-inner">
                                <span class="text-4xl">📭</span>
                            </div>
                            <p class="text-[#8faaf3] font-black text-lg">Belum ada riwayat deposit.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(method_exists($deposits, 'links') && $deposits->hasPages())
            <div class="p-6 border-t-2 border-dashed border-[#f0f5ff] bg-[#f4f9ff]">
                {{ $deposits->links() }}
            </div>
        @endif
    </div>
</div>
@endsection