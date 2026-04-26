@extends('layouts.admin')

@section('title', 'Laporan')
@section('admin_header_subtitle', 'Pantau performa order, keberhasilan fulfillment, dan antrean kerja yang masih perlu diselesaikan.')

@section('content')
<div class="pb-12" style="font-family: 'Nunito', sans-serif;">
    <div class="mb-8 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-lg shadow-[#bde0fe]/20">
            <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Total Order</p>
            <p class="mt-3 text-3xl font-black text-[#2b3a67]">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="rounded-[2rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] p-6 text-white shadow-lg shadow-[#5a76c8]/25">
            <p class="text-xs font-black uppercase tracking-[0.3em] text-white/80">Pendapatan Bersih</p>
            <p class="mt-3 text-3xl font-black">Rp {{ number_format((float) $totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-lg shadow-[#bde0fe]/20">
            <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Success Rate</p>
            <p class="mt-3 text-3xl font-black text-[#4bc6b9]">{{ number_format($successRate, 1) }}%</p>
            <p class="mt-2 text-sm font-bold text-[#8faaf3]">{{ number_format($successfulOrders) }} order sukses dari {{ number_format($paidOrders) }} order berbayar</p>
        </div>
        <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-lg shadow-[#bde0fe]/20">
            <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">AOV</p>
            <p class="mt-3 text-3xl font-black text-[#2b3a67]">Rp {{ number_format((float) $averageOrderValue, 0, ',', '.') }}</p>
            <p class="mt-2 text-sm font-bold text-[#8faaf3]">Rata-rata nilai order sukses</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-lg shadow-[#bde0fe]/20">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Fokus Operasional</p>
                    <h4 class="text-2xl font-black text-[#2b3a67]">Antrean Manual</h4>
                </div>
                <a href="{{ route('admin.manual-orders.index') }}" class="rounded-full border-2 border-white bg-[#f0f5ff] px-4 py-2 text-xs font-black text-[#5a76c8] shadow-sm">Buka Queue</a>
            </div>

            <div class="rounded-[1.5rem] border-2 border-white bg-[#fff9f2] p-5">
                <p class="text-xs font-black uppercase tracking-[0.3em] text-amber-500">Perlu Follow Up</p>
                <p class="mt-3 text-4xl font-black text-[#2b3a67]">{{ number_format($pendingManualOrders) }}</p>
                <p class="mt-2 text-sm font-bold text-[#8faaf3]">Pesanan manual masih berstatus pending atau processing.</p>
            </div>
        </div>

        <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-lg shadow-[#bde0fe]/20">
            <div class="mb-5">
                <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Risiko Stok</p>
                <h4 class="text-2xl font-black text-[#2b3a67]">Produk Perlu Restock</h4>
            </div>

            @if($lowStockProducts->count() > 0)
                <div class="space-y-3">
                    @foreach($lowStockProducts as $product)
                        <a href="{{ route('admin.credentials.index', $product->id) }}" class="flex items-center justify-between rounded-[1.5rem] border-2 border-white bg-[#f8fbff] px-5 py-4 shadow-sm transition hover:bg-[#f0f5ff]">
                            <div>
                                <p class="font-black text-[#2b3a67]">{{ $product->name }}</p>
                                <p class="mt-1 text-xs font-bold text-[#8faaf3]">Reminder: {{ $product->stock_reminder }}</p>
                            </div>
                            <span class="rounded-full bg-[#ffe5e5] px-4 py-2 text-xs font-black text-[#ff6b6b]">Sisa {{ $product->available_stock }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-[1.5rem] border-2 border-white bg-[#f6fffb] px-5 py-8 text-center">
                    <p class="text-lg font-black text-emerald-500">Aman</p>
                    <p class="mt-2 text-sm font-bold text-[#8faaf3]">Belum ada produk inventory yang menyentuh batas stok minimum.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
