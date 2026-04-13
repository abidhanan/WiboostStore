@extends('layouts.admin')

@section('title', 'Manual Order')

@section('content')
<div class="pb-12" style="font-family: 'Nunito', sans-serif;">
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-3xl font-black tracking-tight text-[#2b3a67]">Manual Order Queue</h3>
            <p class="mt-1 text-sm font-bold text-[#8faaf3]">Pantau pesanan yang butuh campur tangan admin dan selesaikan langsung dari panel ini.</p>
        </div>

        <form action="{{ route('admin.manual-orders.index') }}" method="GET" class="flex w-full max-w-xl flex-col gap-3 sm:flex-row">
            <input type="text" name="search" value="{{ request('search') }}"
                class="flex-1 rounded-[1.5rem] border-2 border-white bg-white px-5 py-3 font-black text-[#2b3a67] shadow-sm outline-none transition focus:border-[#5a76c8]"
                placeholder="Cari invoice, nama pelanggan, atau target...">
            <button type="submit" class="rounded-[1.5rem] border-2 border-white bg-[#5a76c8] px-6 py-3 font-black text-white shadow-lg shadow-[#5a76c8]/20 transition hover:bg-[#4760a9]">
                Cari
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-6 py-4 font-black text-emerald-500 shadow-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-sm">{{ session('error') }}</div>
    @endif

    <div class="overflow-hidden rounded-[2rem] border-4 border-white bg-white shadow-lg shadow-[#bde0fe]/20">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Invoice</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pelanggan</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Produk</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Target</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                    @forelse($manualOrders as $order)
                        <tr class="align-top hover:bg-[#f8fbff]">
                            <td class="px-6 py-5">
                                <p class="font-black text-[#5a76c8]">{{ $order->invoice_number }}</p>
                                <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $order->created_at->format('d M Y, H:i') }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <p class="font-black text-[#2b3a67]">{{ $order->user->name ?? 'Guest' }}</p>
                                <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $order->user->email ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <p class="font-black text-[#2b3a67]">{{ $order->product->name ?? 'Produk dihapus' }}</p>
                                <p class="mt-1 text-xs font-bold text-[#8faaf3]">Rp {{ number_format((float) $order->amount, 0, ',', '.') }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <div class="max-w-xs rounded-2xl border border-white bg-[#f4f9ff] px-4 py-3 text-sm font-black text-[#2b3a67] shadow-inner">
                                    {{ $order->target_data }}
                                </div>
                                @if($order->target_notes)
                                    <p class="mt-2 max-w-xs text-xs font-bold text-[#8faaf3]">{{ $order->target_notes }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-[#fff5eb] text-amber-500',
                                        'processing' => 'bg-[#f0f5ff] text-[#5a76c8]',
                                        'success' => 'bg-[#e6fff7] text-emerald-500',
                                        'failed' => 'bg-[#ffe5e5] text-[#ff6b6b]',
                                    ];
                                @endphp
                                <span class="inline-flex rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-widest {{ $statusClasses[$order->order_status] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ $order->order_status }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                @if($order->order_status !== 'success')
                                    <form action="{{ route('admin.manual-orders.complete', $order->id) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <textarea name="target_notes" rows="3"
                                            class="w-64 rounded-2xl border-2 border-[#e0fbfc] bg-[#f8fbff] px-4 py-3 text-sm font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]"
                                            placeholder="Catatan penyelesaian untuk user (opsional)"></textarea>
                                        <button type="submit" class="w-full rounded-[1.25rem] border-2 border-white bg-[#4bc6b9] px-4 py-3 text-sm font-black text-white shadow-lg shadow-[#4bc6b9]/20 transition hover:bg-[#3ba398]">
                                            Tandai Selesai
                                        </button>
                                    </form>
                                @else
                                    <span class="text-sm font-black text-emerald-500">Sudah selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <p class="text-lg font-black text-[#5a76c8]">Belum ada pesanan manual yang perlu diproses.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($manualOrders->hasPages())
            <div class="border-t-2 border-dashed border-[#f0f5ff] bg-[#f8fbff] p-6">
                {{ $manualOrders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
