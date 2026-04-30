@extends('layouts.admin')
@section('title', 'Manual Order')
@section('admin_header_subtitle', 'Proses pesanan pelanggan yang membutuhkan tindakan admin secara manual.')

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

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    <!-- Floating Emojis Background -->
    <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden lg:block z-0">✍️</div>
    <div class="absolute top-1/3 left-5 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden lg:block z-0">✨</div>
    <div class="absolute bottom-20 right-[15%] text-6xl animate-float opacity-20 pointer-events-none hidden lg:block z-0">📦</div>

    <!-- Alert Messages (Aman dari 500 Error) -->
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 sm:px-6 py-4 font-black text-emerald-500 shadow-lg shadow-emerald-100/50 relative z-10 text-sm sm:text-base w-full">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-5 sm:px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10 text-sm sm:text-base w-full">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Toolbar: Pencarian -->
    <div class="mb-6 sm:mb-8 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-4 sm:p-5 shadow-xl shadow-[#bde0fe]/30 relative z-10 w-full min-w-0 transition-transform duration-300 hover:border-[#bde0fe]">
        <form action="{{ route('admin.manual-orders.index') }}" method="GET" class="flex flex-col gap-4 w-full">
            <div class="flex flex-col md:flex-row gap-3 w-full min-w-0">
                <!-- Wrapper Input Cari -->
                <div class="flex-1 flex gap-2 w-full min-w-0">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="min-w-0 flex-1 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] py-3 px-4 sm:px-5 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                        placeholder="Cari Invoice atau Pelanggan...">
                    
                    <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-white shadow-md active:scale-95 transition-transform" title="Cari">
                        🔍
                    </button>
                    
                    @if(request('search'))
                        <a href="{{ route('admin.manual-orders.index') }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[#ff6b6b] shadow-md active:scale-95 transition-transform" title="Reset">
                            ✖️
                        </a>
                    @endif
                </div>

                <!-- Tombol Segarkan Data -->
                <a href="{{ route('admin.manual-orders.index') }}" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#8faaf3] px-6 py-3 h-12 text-sm font-black text-white shadow-md transition-transform active:scale-95 hover:bg-[#6f8ddc] w-full md:w-auto">
                    <span class="text-base">🔄</span> <span class="whitespace-nowrap">Segarkan Antrean</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Data Transaksi Manual -->
    <div class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm shadow-2xl shadow-[#bde0fe]/30 relative z-10 min-w-0 w-full">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full min-w-[900px] text-left whitespace-nowrap">
                    <thead>
                        <tr>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Waktu & Invoice</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Pelanggan</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Layanan</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Data Target</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Status</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($orders ?? [] as $order)
                        <tr class="transition-colors hover:bg-[#f8faff] group">
                            
                            <!-- Invoice & Waktu -->
                            <td class="px-5 py-4 sm:py-5 align-top min-w-[150px]">
                                <p class="font-black text-[#5a76c8] text-xs sm:text-sm mb-1 bg-[#f4f9ff] inline-block px-2 py-1 rounded-md border border-white truncate max-w-full">{{ $order->invoice_number ?? '-' }}</p>
                                <p class="text-[9px] sm:text-[10px] font-bold text-[#8faaf3] ml-1">{{ isset($order->created_at) ? $order->created_at->format('d M Y, H:i') : '-' }}</p>
                            </td>
                            
                            <!-- User -->
                            <td class="px-5 py-4 sm:py-5 align-top min-w-[120px] max-w-[160px]">
                                <p class="font-black text-[#2b3a67] text-xs sm:text-sm truncate">{{ $order->user->name ?? 'Guest' }}</p>
                                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-500 truncate mt-1">WA: {{ $order->user->whatsapp ?? '-' }}</p>
                            </td>
                            
                            <!-- Layanan -->
                            <td class="px-5 py-4 sm:py-5 align-top whitespace-normal min-w-[180px] max-w-[250px]">
                                <p class="font-black text-[#2b3a67] text-xs sm:text-sm line-clamp-2 leading-snug">{{ $order->product->name ?? 'Produk Dihapus' }}</p>
                                <p class="font-black text-[#4bc6b9] text-[10px] sm:text-[11px] drop-shadow-sm mt-1">Rp {{ number_format($order->amount ?? 0, 0, ',', '.') }}</p>
                            </td>
                            
                            <!-- Target Input -->
                            <td class="px-5 py-4 sm:py-5 align-top text-center max-w-[200px]">
                                @if(($order->has_order_input ?? false) && !empty($order->order_input_summary))
                                    <span style="font-size: 9px;" class="font-black text-[#2b3a67] bg-[#f0f5ff] inline-block px-3 py-1.5 rounded-xl border-2 border-white shadow-sm truncate max-w-full" title="{{ $order->order_input_text ?? '' }}">
                                        {{ $order->order_input_summary }}
                                    </span>
                                @else
                                    <span style="font-size: 9px;" class="font-black text-[#2b3a67] bg-[#f0f5ff] inline-block px-3 py-1.5 rounded-xl border-2 border-white shadow-sm truncate max-w-full" title="{{ $order->target_data ?? '' }}">
                                        {{ $order->target_data ?? '-' }}
                                    </span>
                                @endif
                            </td>
                            
                            <!-- Status -->
                            <td class="px-5 py-4 sm:py-5 align-top text-center min-w-[120px]">
                                @php
                                    $statusLabels = [
                                        'success' => ['bg-[#e6fff7]', 'text-emerald-500', 'Sukses'],
                                        'processing' => ['bg-[#e0fbfc]', 'text-[#5a76c8]', 'Proses'],
                                        'pending' => ['bg-[#fff5eb]', 'text-amber-500', 'Pending'],
                                        'failed' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', 'Gagal'],
                                    ];
                                    $st = $statusLabels[$order->order_status ?? 'pending'] ?? ['bg-gray-100', 'text-gray-500', $order->order_status ?? '-'];
                                @endphp
                                <span style="font-size: 9px;" class="{{ $st[0] }} {{ $st[1] }} px-4 py-1.5 rounded-full font-black uppercase tracking-widest border-2 border-white shadow-sm inline-block w-full text-center">
                                    {{ $st[2] }}
                                </span>
                            </td>

                            <!-- Aksi (Edit / Proses) -->
                            <td class="px-5 py-4 sm:py-5 align-top text-center min-w-[120px]">
                                <a href="{{ route('admin.manual-orders.edit', $order->id) }}" class="inline-flex items-center justify-center gap-2 bg-[#f4f9ff] hover:bg-[#e0fbfc] text-[#5a76c8] px-4 py-2 rounded-xl font-black text-[10px] sm:text-xs transition-colors border-2 border-white shadow-sm active:scale-95 w-full">
                                    <span>⚙️</span> Proses
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 sm:py-20 text-center">
                                <div class="mb-4 sm:mb-5 inline-flex h-20 w-20 sm:h-24 sm:w-24 items-center justify-center rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#f0f5ff] text-4xl sm:text-5xl shadow-inner animate-float">✍️</div>
                                <p class="text-lg sm:text-xl font-black text-[#5a76c8]">Antrean kosong.</p>
                                <p class="text-[#8faaf3] font-bold mt-1 text-xs sm:text-sm">Tidak ada pesanan manual yang perlu diproses saat ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Paginasi -->
        @if(isset($orders) && method_exists($orders, 'links') && $orders->hasPages())
            <div class="border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff] p-5 sm:p-8 w-full min-w-0">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection