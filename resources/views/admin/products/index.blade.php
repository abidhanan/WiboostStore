@extends('layouts.admin')

@section('title', 'Manajemen Produk')
@section('admin_header_subtitle', 'Kelola harga, stok otomatis, dan sinkronisasi layanan provider ke website.')
@section('admin_header_actions')
    <form action="{{ route('admin.products.sync.digiflazz') }}" method="POST" class="w-full sm:w-auto">
        @csrf
        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#4bc6b9] px-6 py-3.5 text-sm font-black text-white shadow-xl shadow-[#4bc6b9]/30 transition-transform active:scale-95 hover:bg-[#3ba398] sm:w-auto">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m14.836 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-14.837-2m14.837 2H15"></path></svg>
            Sync Digiflazz
        </button>
    </form>

    <form action="{{ route('admin.products.sync.ordersosmed') }}" method="POST" class="w-full sm:w-auto">
        @csrf
        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#8faaf3] px-6 py-3.5 text-sm font-black text-white shadow-xl shadow-[#8faaf3]/30 transition-transform active:scale-95 hover:bg-[#6f8ddc] sm:w-auto">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m14.836 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-14.837-2m14.837 2H15"></path></svg>
            Sync Sosmed
        </button>
    </form>

    <a href="{{ route('admin.products.create') }}" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#5a76c8] px-6 py-3.5 text-sm font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9] sm:w-auto">
        ✨ Tambah Produk
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

        <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">📦</div>
        <div class="absolute top-1/3 left-5 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
        <div class="absolute bottom-20 right-[15%] text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

        @if(session('success'))
            <div class="mb-8 flex items-center gap-4 rounded-[2.5rem] border-4 border-white bg-[#e6fff7] px-6 py-4 font-black text-emerald-500 shadow-lg shadow-emerald-100/50 relative z-10">
                <span class="text-3xl drop-shadow-sm">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 flex items-center gap-4 rounded-[2.5rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10">
                <span class="text-3xl drop-shadow-sm">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="mb-8 rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-6 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:border-[#bde0fe] relative z-10">
            <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-6 text-[#8faaf3]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-4 pl-16 pr-5 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe]"
                        placeholder="Cari nama produk atau SKU provider...">
                </div>

                <button type="submit" class="rounded-full border-4 border-white bg-[#5a76c8] px-10 py-4 font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9]">
                    Cari Produk 🔍
                </button>

                @if(request('search'))
                    <a href="{{ route('admin.products.index') }}" class="flex items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-8 py-4 font-black text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc]">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-hidden rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm shadow-2xl shadow-[#bde0fe]/30 relative z-10">
            <div class="overflow-x-auto table-scroll p-4 md:p-6">
                <table class="w-full min-w-[920px] text-left">
                    <thead>
                        <tr>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Produk</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Tipe Proses</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Harga Jual</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Provider</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Stok</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($products as $product)
                            <tr class="transition-colors hover:bg-[#f8faff] group">
                                <td class="min-w-[220px] whitespace-normal px-5 py-5">
                                    <div class="flex items-center gap-4">
                                        @if($product->image)
                                            <div class="h-12 w-12 shrink-0 overflow-hidden rounded-[1rem] border-2 border-white bg-[#f4f9ff] shadow-sm">
                                                <img src="{{ Storage::url($product->image) }}" class="h-full w-full object-cover">
                                            </div>
                                        @elseif($product->emote)
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#f0f5ff] text-2xl shadow-inner group-hover:scale-110 transition-transform">{{ $product->emote }}</div>
                                        @endif

                                        <div>
                                            <p class="line-clamp-1 text-base font-black leading-tight text-[#2b3a67]">{{ $product->name }}</p>
                                            <span class="mt-1 inline-block rounded-md bg-[#e0fbfc] px-2 py-1 text-[9px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm border border-white">
                                                {{ $product->category?->breadcrumb_name ?? 'N/A' }}
                                            </span>
                                            @if(! $product->is_active)
                                                <span class="ml-1 text-[9px] font-black uppercase tracking-widest text-rose-500 bg-rose-50 border border-rose-200 px-2 py-1 rounded-md">[Nonaktif]</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-5 text-center">
                                    @if($product->process_type === 'api')
                                        <span class="inline-block rounded-lg border-2 border-white bg-[#f4f9ff] shadow-sm px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">API</span>
                                    @elseif(in_array($product->process_type, ['account', 'number'], true))
                                        <a href="{{ route('admin.credentials.index', $product->id) }}" class="inline-flex items-center gap-2 rounded-lg border-2 border-white bg-[#ffeef2] shadow-sm px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#e1306c] transition-transform hover:scale-105 active:scale-95">
                                            Gudang {{ $product->process_type === 'account' ? 'Akun' : 'Nomor' }}
                                        </a>
                                    @else
                                        <span class="inline-block rounded-lg border-2 border-white bg-[#f4f9ff] shadow-sm px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Manual</span>
                                    @endif
                                </td>

                                <td class="px-5 py-5">
                                    <p class="text-base font-black text-[#4bc6b9] drop-shadow-sm">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                </td>

                                <td class="px-5 py-5 text-center">
                                    <div class="space-y-2">
                                        <span class="inline-block rounded-lg border-2 border-white bg-[#f0f5ff] px-3 py-1.5 font-mono text-[10px] font-black text-[#8faaf3] shadow-inner">
                                            {{ $product->provider_product_id ?? '-' }}
                                        </span>
                                        <div class="text-[9px] font-black uppercase tracking-widest text-[#5a76c8]">
                                            {{ $product->provider_source ?? '-' }}
                                            @if(($product->provider_source ?? '') === 'ordersosmed')
                                                <span class="ml-1 text-[#8faaf3]">x{{ $product->provider_quantity ?? 1 }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-5 text-center">
                                    @if(in_array($product->process_type, ['api', 'manual'], true))
                                        <span class="text-xl font-black text-[#8faaf3]">-</span>
                                    @else
                                        @php($stock = (int) $product->available_stock)
                                        @if($stock <= $product->stock_reminder)
                                            <span class="inline-block min-w-[70px] rounded-full border-2 border-white bg-[#ffe5e5] px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#ff6b6b] shadow-sm animate-pulse">
                                                {{ $stock }} (Limit)
                                            </span>
                                        @else
                                            <span class="inline-block min-w-[70px] rounded-full border-2 border-white bg-[#e6fff7] px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-500 shadow-sm">
                                                {{ $stock }}
                                            </span>
                                        @endif
                                    @endif
                                </td>

                                <td class="px-5 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="rounded-[1rem] border-2 border-white bg-white p-3 text-xl shadow-md shadow-[#bde0fe]/20 transition-transform active:scale-95 hover:bg-[#f0f5ff]" title="Edit">
                                            ✏️
                                        </a>

                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini? Semua data gudang terkait juga akan hilang!');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-[1rem] border-2 border-white bg-white p-3 text-xl shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffe5e5]" title="Hapus">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-20 text-center">
                                    <div class="mb-5 inline-flex h-24 w-24 items-center justify-center rounded-[2.5rem] border-4 border-white bg-[#f4f9ff] text-5xl shadow-inner animate-float">📦</div>
                                    <p class="text-xl font-black text-[#5a76c8]">Belum ada produk di katalog.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($products, 'links') && $products->hasPages())
                <div class="border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff] p-6 md:p-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection