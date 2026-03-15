@extends('layouts.user')

@section('title', 'Riwayat Pesanan')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto mt-4">
    <div class="mb-10 pl-2">
        <h2 class="text-3xl font-black text-[#2b3a67] tracking-tight">Riwayat Pesanan 🛒</h2>
        <p class="text-[#8faaf3] font-bold text-sm mt-1">Pantau semua status jajan digitalmu di sini.</p>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">🎉</span> {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6">
        @forelse($transactions as $trx)
            <div class="bg-white rounded-[2.5rem] p-6 md:p-8 shadow-lg shadow-[#bde0fe]/20 border-4 border-white flex flex-col md:flex-row justify-between items-start md:items-center hover:border-[#bde0fe] hover:-translate-y-1 transition-all group">
                
                <div class="mb-5 md:mb-0 w-full md:w-2/3">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="text-[10px] md:text-xs font-black text-white bg-[#5a76c8] px-3 py-1 rounded-full border-2 border-white shadow-sm tracking-widest">{{ $trx->invoice_number }}</span>
                        <span class="text-[10px] md:text-xs font-bold text-[#8faaf3]">{{ $trx->created_at->format('d M Y, H:i') }} WIB</span>
                    </div>
                    
                    <h4 class="font-black text-xl text-[#2b3a67]">{{ $trx->product->name ?? 'Produk Dihapus' }}</h4>
                    
                    <div class="inline-flex items-center gap-2 mt-3 bg-[#f0f5ff] px-4 py-2 rounded-xl border-2 border-white shadow-inner max-w-full">
                        <span class="text-lg shrink-0">🎯</span>
                        <p class="text-sm text-[#5a76c8] font-bold truncate">Target: <span class="font-black">{{ $trx->target_data }}</span></p>
                    </div>
                </div>
                
                <div class="flex flex-col items-start md:items-end w-full md:w-auto bg-[#f4f9ff] md:bg-transparent p-5 md:p-0 rounded-[1.5rem] md:rounded-none border-2 border-white md:border-0 shadow-inner md:shadow-none">
                    <p class="font-black text-[#5a76c8] text-2xl mb-3">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                    
                    <div class="flex flex-wrap justify-start md:justify-end gap-2">
                        @if($trx->payment_status == 'paid')
                            <span class="bg-[#e6fff7] text-emerald-500 px-4 py-1.5 rounded-full text-[10px] md:text-xs font-black uppercase tracking-widest border border-white shadow-sm">Lunas</span>
                        @elseif($trx->payment_status == 'failed')
                            <span class="bg-[#ffe5e5] text-[#ff6b6b] px-4 py-1.5 rounded-full text-[10px] md:text-xs font-black uppercase tracking-widest border border-white shadow-sm">Gagal Bayar</span>
                        @else
                            <span class="bg-[#fff5eb] text-amber-500 px-4 py-1.5 rounded-full text-[10px] md:text-xs font-black uppercase tracking-widest border border-white shadow-sm">Menunggu Bayar</span>
                        @endif

                        @if($trx->payment_status == 'paid')
                            @if($trx->order_status == 'success')
                                <span class="bg-[#e6fff7] text-emerald-500 px-4 py-1.5 rounded-full text-[10px] md:text-xs font-black uppercase tracking-widest border border-white shadow-sm">Pesanan Sukses</span>
                            @elseif($trx->order_status == 'processing')
                                <span class="bg-[#f0f5ff] text-[#5a76c8] px-4 py-1.5 rounded-full text-[10px] md:text-xs font-black uppercase tracking-widest border border-white shadow-sm animate-pulse">Diproses</span>
                            @elseif($trx->order_status == 'failed')
                                <span class="bg-[#ffe5e5] text-[#ff6b6b] px-4 py-1.5 rounded-full text-[10px] md:text-xs font-black uppercase tracking-widest border border-white shadow-sm">Pesanan Gagal</span>
                            @else
                                <span class="bg-[#f4f9ff] text-[#8faaf3] px-4 py-1.5 rounded-full text-[10px] md:text-xs font-black uppercase tracking-widest border border-white shadow-sm">Menunggu</span>
                            @endif
                        @endif
                    </div>
                </div>

            </div>
        @empty
            <div class="text-center py-24 bg-white rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] shadow-sm">
                <div class="text-7xl mb-4 opacity-50 inline-block w-24 h-24 bg-[#f0f5ff] rounded-full flex items-center justify-center mx-auto border-4 border-white shadow-inner">🛒</div>
                <h3 class="text-xl font-black text-[#5a76c8] mb-2 mt-4">Belum Ada Transaksi</h3>
                <p class="text-[#8faaf3] font-bold max-w-sm mx-auto">Pesanan yang kamu buat akan otomatis muncul di sini. Yuk mulai jajan!</p>
                <a href="{{ route('user.dashboard') }}" class="inline-block mt-6 bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black px-8 py-3.5 rounded-full transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white">
                    Lihat Katalog Layanan
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection