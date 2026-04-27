@extends('layouts.admin')

@section('title', 'Manual Order')
@section('admin_header_subtitle', 'Pantau pesanan yang butuh campur tangan admin dan selesaikan langsung dari sini.')
@section('admin_header_actions')
    <a href="{{ route('admin.manual-orders.index') }}" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-white px-6 py-3.5 font-black text-[#5a76c8] shadow-lg shadow-[#bde0fe]/30 transition-transform active:scale-95 hover:bg-[#f4f9ff] sm:w-auto">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        Segarkan Data
    </a>
@endsection

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; border: 2px solid #f0f5ff; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 relative z-10">

    <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">⚙️</div>
    <div class="absolute top-1/3 left-5 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 right-[15%] text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="mb-8 rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-6 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:border-[#bde0fe] relative z-10">
        <form action="{{ route('admin.manual-orders.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-6 text-[#8faaf3]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-4 pl-16 pr-5 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe]"
                    placeholder="Cari invoice, nama pelanggan, atau target...">
            </div>
            <button type="submit" class="rounded-full border-4 border-white bg-[#5a76c8] px-10 py-4 font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9]">
                Cari Order 🔍
            </button>
            @if(request('search'))
                <a href="{{ route('admin.manual-orders.index') }}" class="flex items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-8 py-4 font-black text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc]">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2.5rem] mb-8 font-black flex items-center gap-4 shadow-lg shadow-emerald-100/50 relative z-10">
            <span class="text-3xl drop-shadow-sm">✅</span> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2.5rem] mb-8 font-black flex items-center gap-4 shadow-lg shadow-[#ff6b6b]/20 relative z-10">
            <span class="text-3xl drop-shadow-sm">⚠️</span> {{ session('error') }}
        </div>
    @endif

    <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/30 border-4 border-white flex flex-col overflow-hidden relative z-10">
        <div class="overflow-x-auto table-scroll w-full p-4 md:p-6">
            <table class="min-w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr>
                        <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Invoice & Waktu</th>
                        <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Pelanggan</th>
                        <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Layanan & Harga</th>
                        <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Target Buyer</th>
                        <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Status</th>
                        <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Aksi Manual</th>
                    </tr>
                </thead>
                <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                    @forelse($manualOrders as $order)
                        <tr class="align-top hover:bg-[#f8faff] transition-colors group">
                            <td class="px-5 py-6">
                                <p class="font-black text-[#5a76c8] text-sm mb-1 bg-[#f4f9ff] inline-block px-3 py-1 rounded-md border border-white">{{ $order->invoice_number }}</p>
                                <p class="mt-1 ml-1 text-[10px] font-bold text-[#8faaf3]">{{ $order->created_at->format('d M Y, H:i') }}</p>
                            </td>
                            <td class="px-5 py-6">
                                <p class="font-black text-[#2b3a67] text-sm truncate max-w-[150px]">{{ $order->user->name ?? 'Guest' }}</p>
                                <p class="mt-1 text-[10px] font-bold text-[#8faaf3] truncate max-w-[150px]">{{ $order->user->email ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-6 min-w-[200px] whitespace-normal">
                                <p class="font-black text-[#2b3a67] text-sm leading-snug line-clamp-2">{{ $order->product->name ?? 'Produk dihapus' }}</p>
                                <p class="mt-2 text-sm font-black text-[#4bc6b9] drop-shadow-sm">Rp {{ number_format((float) $order->amount, 0, ',', '.') }}</p>
                            </td>
                            <td class="px-5 py-6 min-w-[220px]">
                                @if($order->has_order_input)
                                    <div class="space-y-3">
                                        @foreach($order->order_input_fields as $inputField)
                                            <div class="rounded-xl border-2 border-white bg-[#f4f9ff] px-4 py-3 shadow-inner">
                                                <p class="mb-1 text-[9px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $inputField['label'] }}</p>
                                                <p class="text-sm font-black text-[#2b3a67] break-all whitespace-normal">{{ $inputField['value'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="rounded-xl border-2 border-white bg-[#f4f9ff] px-4 py-3 text-sm font-black text-[#2b3a67] shadow-inner break-all whitespace-normal">
                                        {{ $order->target_data }}
                                    </div>
                                @endif
                                
                                @if($order->target_notes)
                                    <p class="mt-3 text-[10px] font-bold text-[#8faaf3] bg-white p-2 rounded border border-[#f0f5ff] whitespace-normal line-clamp-2">{{ $order->target_notes }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-6 text-center align-middle">
                                @php
                                    $statusClasses = [
                                        'pending' => ['bg-[#fff5eb]', 'text-amber-500', '⏳ PENDING'],
                                        'processing' => ['bg-[#f0f5ff]', 'text-[#5a76c8]', '⚙️ PROSES'],
                                        'success' => ['bg-[#e6fff7]', 'text-emerald-500', '✅ SUKSES'],
                                        'failed' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', '❌ GAGAL'],
                                    ];
                                    $st = $statusClasses[$order->order_status] ?? ['bg-gray-100', 'text-gray-500', strtoupper($order->order_status)];
                                @endphp
                                <span class="inline-flex items-center justify-center rounded-full px-4 py-2 text-[9px] font-black tracking-widest border-2 border-white shadow-sm w-28 {{ $st[0] }} {{ $st[1] }}">
                                    {{ $st[2] }}
                                </span>
                            </td>
                            <td class="px-5 py-6 align-middle min-w-[250px]">
                                @if($order->order_status !== 'success')
                                    <form action="{{ route('admin.manual-orders.complete', $order->id) }}" method="POST" class="flex flex-col gap-3">
                                        @csrf
                                        <textarea name="target_notes" rows="2"
                                            class="w-full rounded-[1.2rem] border-4 border-white bg-[#f8fbff] px-4 py-3 text-xs font-bold text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] placeholder-[#a3bbfb]"
                                            placeholder="Catatan ke pelanggan (opsional)"></textarea>
                                        <div class="flex gap-2">
                                            <button type="submit" class="flex-1 rounded-[1rem] border-2 border-white bg-[#4bc6b9] py-3 text-xs font-black text-white shadow-md shadow-[#4bc6b9]/30 transition-transform active:scale-95 hover:bg-[#3ba398]">
                                                Tandai Selesai ✅
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="flex items-center justify-center h-full">
                                        <span class="text-[10px] font-black text-emerald-500 bg-[#e6fff7] px-4 py-2 rounded-full border border-white shadow-sm uppercase tracking-widest">Diselesaikan Admin</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="inline-flex items-center justify-center w-24 h-24 rounded-[2.5rem] bg-[#f0f5ff] border-4 border-white mb-5 shadow-inner">
                                    <span class="text-5xl animate-float">🎉</span>
                                </div>
                                <p class="text-xl font-black text-[#5a76c8]">Hore! Tidak ada antrean manual.</p>
                                <p class="text-[#8faaf3] font-bold mt-1">Semua pesanan manual sudah diselesaikan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($manualOrders, 'links') && $manualOrders->hasPages())
            <div class="border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff] p-6 md:p-8">
                {{ $manualOrders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection