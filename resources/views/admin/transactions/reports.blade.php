@extends('layouts.admin')

@section('title', 'Laporan')
@section('admin_header_subtitle', 'Pantau performa order, keberhasilan fulfillment, dan antrean kerja operasional.')

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

<div class="wiboost-font pb-12 relative z-10">

    <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">📊</div>
    <div class="absolute top-1/3 left-5 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 right-[15%] text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="mb-10 flex items-start gap-5 pl-2 relative z-10">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.2rem] border-4 border-white bg-white/90 backdrop-blur-sm text-[#5a76c8] shadow-lg shadow-[#bde0fe]/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        </div>
        <div>
            <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-2 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Data Analitik
            </div>
            <h3 class="text-3xl md:text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm">Ringkasan Performa 📈</h3>
        </div>
    </div>

    <div class="mb-10 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4 relative z-10">
        <div class="rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-8 shadow-xl shadow-[#bde0fe]/30 transition-transform hover:-translate-y-2 hover:scale-[1.02] duration-300">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3] mb-2 bg-[#f4f9ff] inline-block px-3 py-1.5 rounded-lg border border-white shadow-sm">Total Order</p>
            <p class="mt-4 text-4xl font-black text-[#2b3a67] drop-shadow-sm">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="rounded-[2.5rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] p-8 text-white shadow-xl shadow-[#5a76c8]/40 transition-transform hover:-translate-y-2 hover:scale-[1.02] duration-300 relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 text-7xl opacity-20 transform rotate-12">💰</div>
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#5a76c8] mb-2 bg-[#e0fbfc] inline-block px-3 py-1.5 rounded-lg border border-white shadow-sm">Pendapatan Bersih</p>
                <p class="mt-4 text-4xl font-black drop-shadow-md">Rp {{ number_format((float) $totalRevenue, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-8 shadow-xl shadow-[#bde0fe]/30 transition-transform hover:-translate-y-2 hover:scale-[1.02] duration-300">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3] mb-2 bg-[#f4f9ff] inline-block px-3 py-1.5 rounded-lg border border-white shadow-sm">Success Rate</p>
            <p class="mt-4 text-4xl font-black text-[#4bc6b9] drop-shadow-sm">{{ number_format($successRate, 1) }}%</p>
            <p class="mt-3 text-xs font-bold text-[#8faaf3]">{{ number_format($successfulOrders) }} sukses dari {{ number_format($paidOrders) }} dibayar</p>
        </div>
        <div class="rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-8 shadow-xl shadow-[#bde0fe]/30 transition-transform hover:-translate-y-2 hover:scale-[1.02] duration-300">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3] mb-2 bg-[#f4f9ff] inline-block px-3 py-1.5 rounded-lg border border-white shadow-sm">AOV</p>
            <p class="mt-4 text-4xl font-black text-[#2b3a67] drop-shadow-sm truncate">Rp {{ number_format((float) $averageOrderValue, 0, ',', '.') }}</p>
            <p class="mt-3 text-xs font-bold text-[#8faaf3]">Rata-rata nilai order sukses</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-2 relative z-10">
        
        <div class="rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-8 shadow-xl shadow-[#bde0fe]/30 flex flex-col">
            <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b-4 border-dashed border-[#f4f9ff] pb-6">
                <div class="flex items-center gap-3">
                    <div class="text-3xl">⚙️</div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Fokus Operasional</p>
                        <h4 class="text-2xl font-black text-[#2b3a67]">Antrean Manual</h4>
                    </div>
                </div>
                <a href="{{ route('admin.manual-orders.index') }}" class="rounded-full border-2 border-white bg-[#f0f5ff] px-5 py-2.5 text-xs font-black text-[#5a76c8] shadow-sm hover:bg-[#5a76c8] hover:text-white transition-colors">Buka Antrean</a>
            </div>

            <div class="rounded-[2rem] border-4 border-white bg-[#fffdf7] p-8 shadow-inner flex flex-col items-center justify-center text-center h-full">
                <div class="w-16 h-16 bg-[#fff5eb] border-2 border-amber-100 rounded-2xl flex items-center justify-center text-3xl mb-4 shadow-sm animate-bounce">
                    🛎️
                </div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-amber-500">Perlu Follow Up</p>
                <p class="mt-2 text-6xl font-black text-amber-600 drop-shadow-sm">{{ number_format($pendingManualOrders) }}</p>
                <p class="mt-4 text-sm font-bold text-amber-700 bg-amber-50 px-4 py-2 rounded-xl border border-amber-100">Pesanan manual masih berstatus pending / processing.</p>
            </div>
        </div>

        <div class="rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-8 shadow-xl shadow-[#bde0fe]/30 flex flex-col">
            <div class="mb-6 flex items-center gap-3 border-b-4 border-dashed border-[#f4f9ff] pb-6">
                <div class="text-3xl">⚠️</div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Risiko Stok</p>
                    <h4 class="text-2xl font-black text-[#2b3a67]">Produk Perlu Restock</h4>
                </div>
            </div>

            @if($lowStockProducts->count() > 0)
                <div class="space-y-4 flex-1 overflow-y-auto pr-2 max-h-[300px]">
                    @foreach($lowStockProducts as $product)
                        <a href="{{ route('admin.credentials.index', $product->id) }}" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 rounded-[1.5rem] border-4 border-white bg-[#f8fbff] p-5 shadow-sm transition-transform hover:scale-[1.02] hover:border-[#bde0fe] group">
                            <div>
                                <p class="font-black text-[#2b3a67] text-lg group-hover:text-[#5a76c8] transition-colors">{{ $product->name }}</p>
                                <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Batas Pengingat: {{ $product->stock_reminder }}</p>
                            </div>
                            <span class="rounded-full bg-[#ffe5e5] px-4 py-2 text-xs font-black text-[#ff6b6b] border border-white shadow-sm animate-pulse w-full sm:w-auto text-center">Sisa {{ $product->available_stock }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-[2rem] border-4 border-white bg-[#f6fffb] px-5 py-12 text-center shadow-inner h-full flex flex-col items-center justify-center">
                    <div class="w-20 h-20 bg-[#e6fff7] border-2 border-emerald-100 rounded-3xl flex items-center justify-center text-4xl mb-4 shadow-sm animate-float">
                        🛡️
                    </div>
                    <p class="text-2xl font-black text-emerald-500 drop-shadow-sm">Stok Aman</p>
                    <p class="mt-3 text-sm font-bold text-emerald-600 bg-emerald-50 px-5 py-2 rounded-xl border border-emerald-100">Belum ada produk inventory yang menyentuh batas minimum.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection