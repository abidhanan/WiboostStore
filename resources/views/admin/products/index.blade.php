@extends('layouts.admin')
@section('title', 'Manajemen Produk')
@section('admin_header_subtitle', 'Kelola harga, stok otomatis, dan sinkronisasi layanan provider ke website.')

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
    <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">📦</div>
    <div class="absolute top-1/3 left-5 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 right-[15%] text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">🛍️</div>

    <!-- Toolbar Utama -->
    <div class="mb-6 sm:mb-8 flex flex-col gap-3 sm:gap-4 relative z-10 w-full min-w-0">
        
        <!-- Baris 1: Pencarian & Tombol Tambah Produk -->
        <div class="flex flex-col md:flex-row gap-3 sm:gap-4 w-full min-w-0">
            <!-- Form Pencarian -->
            <div class="flex-1 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-3 sm:p-4 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:border-[#bde0fe] min-w-0">
                <form action="{{ route('admin.products.index') }}" method="GET" class="flex gap-2 sm:gap-3 items-center w-full">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="min-w-0 flex-1 rounded-[1.2rem] sm:rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-3 sm:py-4 px-5 sm:px-6 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                        placeholder="Cari nama produk atau SKU provider...">
                    
                    <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1.2rem] sm:rounded-[1.5rem] border-4 border-white bg-[#5a76c8] w-12 h-12 sm:w-[3.5rem] sm:h-[3.5rem] text-white shadow-lg shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9] text-lg sm:text-xl" title="Cari Produk">
                        🔍
                    </button>
                    
                    @if(request('search'))
                        <a href="{{ route('admin.products.index') }}" class="shrink-0 flex items-center justify-center rounded-[1.2rem] sm:rounded-[1.5rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 sm:w-[3.5rem] sm:h-[3.5rem] text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc] text-lg sm:text-xl" title="Reset Pencarian">
                            ✖️
                        </a>
                    @endif
                </form>
            </div>

            <!-- Tombol Tambah Produk -->
            <a href="{{ route('admin.products.create') }}" class="shrink-0 flex items-center justify-center gap-2 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#5a76c8] px-6 sm:px-8 py-4 sm:py-0 text-sm font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9] w-full md:w-auto min-w-0">
                <span class="text-lg">➕</span> Tambah Produk
            </a>
        </div>

        <!-- Baris 2: Tombol Sync Digiflazz & Sosmed -->
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 w-full min-w-0">
            <form action="{{ route('admin.products.sync.digiflazz') }}" method="POST" class="w-full sm:flex-1 m-0">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#4bc6b9] px-4 sm:px-6 py-3.5 sm:py-4 text-xs sm:text-sm font-black text-white shadow-xl shadow-[#4bc6b9]/30 transition-transform active:scale-95 hover:bg-[#3ba398]">
                    <span class="text-base sm:text-lg">🔄</span> Sync Digiflazz
                </button>
            </form>
            
            <form action="{{ route('admin.products.sync.ordersosmed') }}" method="POST" class="w-full sm:flex-1 m-0">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#8faaf3] px-4 sm:px-6 py-3.5 sm:py-4 text-xs sm:text-sm font-black text-white shadow-xl shadow-[#8faaf3]/30 transition-transform active:scale-95 hover:bg-[#6f8ddc]">
                    <span class="text-base sm:text-lg">🔄</span> Sync Sosmed
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-6 sm:mb-8 flex items-center gap-3 sm:gap-4 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 sm:px-6 py-4 font-black text-emerald-500 shadow-lg shadow-emerald-100/50 relative z-10 text-sm sm:text-base w-full">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 sm:mb-8 flex items-center gap-3 sm:gap-4 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-5 sm:px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10 text-sm sm:text-base w-full">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Tabel Data Produk -->
    <div class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm shadow-2xl shadow-[#bde0fe]/30 relative z-10 min-w-0 w-full">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full min-w-[800px] text-left whitespace-nowrap">
                    <thead>
                        <tr>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Produk</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Tipe Proses</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Harga Jual</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Provider</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Stok</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($products ?? [] as $product)
                            <tr class="transition-colors hover:bg-[#f8faff] group">
                                <td class="min-w-[200px] max-w-[280px] whitespace-normal px-5 py-4 sm:py-5 align-middle">
                                    <div class="flex items-center gap-3 sm:gap-4">
                                        @if(!empty($product->image))
                                            <div class="h-10 w-10 shrink-0 overflow-hidden rounded-[1rem] border-2 border-white bg-[#f4f9ff] shadow-sm">
                                                <img src="{{ Storage::url($product->image) }}" class="h-full w-full object-cover">
                                            </div>
                                        @else
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#f0f5ff] text-xl shadow-inner group-hover:scale-110 transition-transform">
                                                {{ $product->emote ?? '📦' }}
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="line-clamp-2 text-xs sm:text-sm font-black leading-snug text-[#2b3a67]">{{ $product->name ?? '-' }}</p>
                                            
                                            <!-- Label Kategori (Ukuran Dikecilkan Secara Paksa dengan Inline Style) -->
                                            <span style="font-size: 9px;" class="mt-1.5 inline-block rounded-md bg-[#e0fbfc] px-2 py-1 font-black uppercase tracking-wider text-[#4bc6b9] shadow-sm border border-white line-clamp-2 leading-tight">
                                                {{ $product->category?->breadcrumb_name ?? 'KATEGORI KOSONG' }}
                                            </span>
                                            
                                            @if(isset($product->is_active) && !$product->is_active)
                                                <span style="font-size: 9px;" class="ml-1 font-black uppercase tracking-wider text-rose-500 bg-rose-50 border border-rose-200 px-2 py-1 rounded-md shadow-sm">[Nonaktif]</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                    @php $pType = $product->process_type ?? 'manual'; @endphp
                                    @if($pType === 'api')
                                        <span style="font-size: 10px;" class="inline-block rounded-lg border-2 border-white bg-[#f4f9ff] shadow-sm px-3 py-1.5 font-black uppercase tracking-widest text-[#8faaf3]">API</span>
                                    @elseif(in_array($pType, ['account', 'number'], true))
                                        <a href="{{ route('admin.credentials.index', $product->id) }}" style="font-size: 10px;" class="inline-flex items-center gap-1.5 rounded-lg border-2 border-white bg-[#ffeef2] shadow-sm px-3 py-1.5 font-black uppercase tracking-widest text-[#e1306c] transition-transform hover:scale-105 active:scale-95">
                                            Gudang {{ $pType === 'account' ? 'Akun' : 'Nomor' }}
                                        </a>
                                    @else
                                        <span style="font-size: 10px;" class="inline-block rounded-lg border-2 border-white bg-[#f4f9ff] shadow-sm px-3 py-1.5 font-black uppercase tracking-widest text-[#8faaf3]">Manual</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle">
                                    <p class="text-xs sm:text-sm font-black text-[#4bc6b9] drop-shadow-sm">Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                    <div class="space-y-1">
                                        <span style="font-size: 10px;" class="inline-block rounded-lg border-2 border-white bg-[#f0f5ff] px-2 py-1 font-mono font-black text-[#8faaf3] shadow-inner max-w-[120px] truncate">
                                            {{ $product->provider_product_id ?? '-' }}
                                        </span>
                                        <div style="font-size: 9px;" class="font-black uppercase tracking-widest text-[#5a76c8]">
                                            {{ $product->provider_source ?? '-' }}
                                            @if(($product->provider_source ?? '') === 'ordersosmed')
                                                <span class="ml-1 text-[#8faaf3]">x{{ $product->provider_quantity ?? 1 }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                    @if(in_array($pType, ['api', 'manual'], true))
                                        <span class="text-lg font-black text-[#8faaf3]">-</span>
                                    @else
                                        @php
                                            $stock = (int) ($product->available_stock ?? 0);
                                            $reminder = (int) ($product->stock_reminder ?? 0);
                                        @endphp
                                        @if($stock <= $reminder)
                                            <span style="font-size: 10px;" class="inline-block min-w-[60px] rounded-full border-2 border-white bg-[#ffe5e5] px-3 py-1.5 font-black uppercase tracking-widest text-[#ff6b6b] shadow-sm animate-pulse">
                                                {{ $stock }} (Limit)
                                            </span>
                                        @else
                                            <span style="font-size: 10px;" class="inline-block min-w-[60px] rounded-full border-2 border-white bg-[#e6fff7] px-3 py-1.5 font-black uppercase tracking-widest text-emerald-500 shadow-sm">
                                                {{ $stock }}
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="flex h-9 w-9 items-center justify-center rounded-[1rem] border-2 border-white bg-white text-lg shadow-md shadow-[#bde0fe]/20 transition-transform active:scale-95 hover:bg-[#f0f5ff]" title="Edit">
                                            ✏️
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini? Semua data gudang terkait juga akan hilang!');" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-[1rem] border-2 border-white bg-white text-lg shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffe5e5]" title="Hapus">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 sm:py-20 text-center">
                                    <div class="mb-4 sm:mb-5 inline-flex h-20 w-20 sm:h-24 sm:w-24 items-center justify-center rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#f4f9ff] text-4xl sm:text-5xl shadow-inner animate-float">📦</div>
                                    <p class="text-lg sm:text-xl font-black text-[#5a76c8]">Belum ada produk di katalog.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Paginasi -->
        @if(isset($products) && method_exists($products, 'links') && $products->hasPages())
            <div class="border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff] p-5 sm:p-8 w-full min-w-0">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection