@extends('layouts.admin')

@section('title', 'Manual Order')
@section('admin_header_subtitle', 'Pantau pesanan yang butuh campur tangan admin dan selesaikan langsung dari panel ini.')

@section('content')
<div class="pb-12" style="font-family: 'Nunito', sans-serif;">
    <div class="mb-8 rounded-[2rem] border-4 border-white bg-white p-5 shadow-lg shadow-[#bde0fe]/20">
        <form action="{{ route('admin.manual-orders.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-[#8faaf3]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full rounded-[1.5rem] border-2 border-[#e0fbfc] bg-[#f4f9ff] py-4 pl-14 pr-5 font-black text-[#2b3a67] outline-none transition placeholder-[#a3bbfb] focus:border-[#5a76c8]"
                    placeholder="Cari invoice, nama pelanggan, atau target...">
            </div>
            <button type="submit" class="rounded-[1.5rem] border-2 border-white bg-[#5a76c8] px-10 py-4 font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                Cari Order
            </button>
            @if(request('search'))
                <a href="{{ route('admin.manual-orders.index') }}" class="flex items-center justify-center rounded-[1.5rem] border-2 border-white bg-[#ffe5e5] px-8 py-4 font-black text-[#ff6b6b] transition hover:bg-[#ffcccc]">
                    Reset
                </a>
            @endif
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
                                @if($order->has_order_input)
                                    <div class="max-w-xs space-y-2">
                                        @foreach($order->order_input_fields as $inputField)
                                            <div class="rounded-2xl border border-white bg-[#f4f9ff] px-4 py-3 shadow-inner">
                                                <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $inputField['label'] }}</p>
                                                <p class="text-sm font-black text-[#2b3a67] break-all">{{ $inputField['value'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="max-w-xs rounded-2xl border border-white bg-[#f4f9ff] px-4 py-3 text-sm font-black text-[#2b3a67] shadow-inner">
                                        {{ $order->target_data }}
                                    </div>
                                @endif
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
