@extends('layouts.admin')
@section('title', 'Manajemen Produk')
@section('admin_header_subtitle', 'Kelola harga, stok otomatis, dan sinkronisasi layanan provider ke website.')

@section('content')
<style>
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; border: 2px solid #f0f5ff; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
</style>

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    <div class="mb-6 sm:mb-8 flex flex-col gap-3 sm:gap-4 relative z-10 w-full min-w-0">
        <div class="flex flex-col md:flex-row gap-3 sm:gap-4 w-full min-w-0">
            <div class="flex-1 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-3 sm:p-4 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:border-[#bde0fe] min-w-0">
                <form action="{{ route('admin.products.index') }}" method="GET" class="flex gap-2 sm:gap-3 items-center w-full">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="min-w-0 flex-1 rounded-[1.2rem] sm:rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-3 sm:py-4 px-5 sm:px-6 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                        placeholder="Cari nama produk atau SKU provider...">

                    <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95 hover:bg-[#4760a9] text-base" title="Cari Produk">
                        &#x1F50D;
                    </button>

                    @if(request('search'))
                        <a href="{{ route('admin.products.index') }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc] text-base" title="Reset Pencarian">
                            &#x2716;&#xFE0F;
                        </a>
                    @endif
                </form>
            </div>

            <a href="{{ route('admin.products.create') }}" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95 hover:bg-[#4760a9] w-full md:w-auto md:min-w-[190px] md:px-8 md:py-4 md:text-base min-w-0">
                <span class="text-base">&#x2795;</span> Tambah Produk
            </a>
        </div>

        <div class="hidden">
            <form action="{{ route('admin.products.sync.digiflazz') }}" method="POST" class="w-full sm:flex-1 m-0">
                @csrf
                <button type="submit">Sync Digiflazz</button>
            </form>

            <form action="{{ route('admin.products.sync.ordersosmed') }}" method="POST" class="w-full sm:flex-1 m-0">
                @csrf
                <button type="submit">Sync Sosmed</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 sm:mb-8 flex items-center gap-3 sm:gap-4 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 sm:px-6 py-4 font-black text-emerald-500 shadow-lg shadow-emerald-100/50 relative z-10 text-sm sm:text-base w-full">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 sm:mb-8 flex items-center gap-3 sm:gap-4 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-5 sm:px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10 text-sm sm:text-base w-full">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @php
        $sections = collect($productSections ?? []);
        $hasProducts = $sections->contains(fn ($section) => collect($section['categories'] ?? [])->isNotEmpty());
    @endphp

    @if($hasProducts)
        <div class="space-y-8">
            @foreach($sections as $section)
                @php
                    $categorySections = collect($section['categories'] ?? []);
                @endphp

                @continue($categorySections->isEmpty())

                <section class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm shadow-2xl shadow-[#bde0fe]/30 relative z-10 min-w-0 w-full">
                    <div class="border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff] px-5 py-5 sm:px-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <span class="mb-2 inline-flex rounded-full border-2 border-white bg-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#5a76c8] shadow-sm">
                                    Kategori Utama
                                </span>
                                <h3 class="text-xl font-black text-[#2b3a67] sm:text-2xl">{{ $section['name'] }}</h3>
                            </div>
                            <span class="w-fit rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                                {{ $section['count'] }} produk di halaman ini
                            </span>
                        </div>
                    </div>

                    <div class="w-full overflow-x-auto table-scroll pb-2">
                        <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                            <table class="w-full text-left" style="min-width: 1170px; table-layout: fixed;">
                                <thead>
                                    <tr>
                                        <th style="font-size: 10px; width: 360px;" class="whitespace-nowrap px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Produk</th>
                                        <th style="font-size: 10px; width: 170px;" class="whitespace-nowrap px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Tipe Proses</th>
                                        <th style="font-size: 10px; width: 170px;" class="whitespace-nowrap px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Harga Jual</th>
                                        <th style="font-size: 10px; width: 210px;" class="whitespace-nowrap px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Provider</th>
                                        <th style="font-size: 10px; width: 115px;" class="whitespace-nowrap px-5 py-4 pr-8 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Stok</th>
                                        <th style="font-size: 10px; width: 145px;" class="whitespace-nowrap px-5 py-4 pl-8 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                                    @foreach($categorySections as $categorySection)
                                        <tr class="bg-[#f8faff]">
                                            <td colspan="6" class="px-5 py-3">
                                                <div class="flex flex-wrap items-center justify-start gap-4 sm:gap-5">
                                                    <span class="inline-flex rounded-full border-2 border-white bg-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#5a76c8] shadow-sm">
                                                        {{ $categorySection['name'] }}
                                                    </span>
                                                    <span class="rounded-full border-2 border-white bg-[#eef8ff] px-4 py-1.5 text-[9px] font-black uppercase tracking-widest text-[#8faaf3] shadow-sm">
                                                        {{ $categorySection['count'] }} produk
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>

                                        @foreach($categorySection['products'] as $product)
                                            <tr class="transition-colors hover:bg-[#f8faff] group">
                                                <td class="px-5 py-4 align-middle sm:py-5" style="width: 360px; max-width: 360px;">
                                                    <div class="flex items-center gap-3 sm:gap-4">
                                                        @if(!empty($product->display_logo_image))
                                                            <div class="h-10 w-10 shrink-0 overflow-hidden rounded-[1rem] border-2 border-white bg-[#f4f9ff] shadow-sm">
                                                                <img src="{{ Storage::url($product->display_logo_image) }}" class="h-full w-full object-cover" alt="{{ $product->name }}">
                                                            </div>
                                                        @else
                                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#f0f5ff] text-xl shadow-inner group-hover:scale-110 transition-transform">
                                                                {{ $product->display_logo_emote ?? 'P' }}
                                                            </div>
                                                        @endif

                                                        <div class="flex-1" style="min-width: 0;">
                                                            <p class="text-[11px] font-black leading-tight text-[#2b3a67] sm:text-sm sm:leading-snug" title="{{ $product->name ?? '-' }}" style="display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; text-overflow: clip; white-space: normal; word-break: normal; overflow-wrap: anywhere;">
                                                                {{ $product->name ?? '-' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="px-5 py-4 pr-8 sm:py-5 align-middle text-center">
                                                    @php $pType = $product->process_type ?? 'api'; @endphp
                                                    @if($pType === 'api')
                                                        <span style="font-size: 10px;" class="inline-block rounded-lg border-2 border-white bg-[#f4f9ff] shadow-sm px-3 py-1.5 font-black uppercase tracking-widest text-[#8faaf3]">API</span>
                                                    @elseif($pType === 'account')
                                                        <a href="{{ route('admin.credentials.index', $product->id) }}" style="font-size: 10px;" class="inline-flex items-center gap-1.5 rounded-lg border-2 border-white bg-[#ffeef2] shadow-sm px-3 py-1.5 font-black uppercase tracking-widest text-[#e1306c] transition-transform hover:scale-105 active:scale-95">
                                                            Gudang Akun
                                                        </a>
                                                    @else
                                                        <span style="font-size: 10px;" class="inline-block rounded-lg border-2 border-white bg-[#f4f9ff] shadow-sm px-3 py-1.5 font-black uppercase tracking-widest text-[#8faaf3]">Arsip</span>
                                                    @endif
                                                </td>

                                                <td class="px-5 py-4 sm:py-5 align-middle">
                                                    <p class="text-xs sm:text-sm font-black text-[#4bc6b9] drop-shadow-sm">Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}</p>
                                                    @if($product->uses_api_selling_markup)
                                                        <p style="font-size: 9px;" class="mt-1 whitespace-nowrap font-black uppercase tracking-widest text-[#8faaf3]">
                                                            Modal: Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}
                                                        </p>
                                                    @endif
                                                </td>

                                                <td class="px-5 py-4 pl-8 sm:py-5 align-middle text-center">
                                                    <div class="space-y-1">
                                                        <span style="font-size: 10px;" class="inline-block rounded-lg border-2 border-white bg-[#f0f5ff] px-2 py-1 font-mono font-black text-[#8faaf3] shadow-inner max-w-[120px] truncate">
                                                            {{ $product->provider_product_id ?? '-' }}
                                                        </span>
                                                        <div style="font-size: 9px;" class="whitespace-nowrap font-black uppercase tracking-widest text-[#5a76c8]">
                                                            {{ $product->provider_source ?? '-' }}
                                                            @if(($product->provider_source ?? '') === 'ordersosmed')
                                                                <span class="ml-1 text-[#8faaf3]">x{{ $product->provider_quantity ?? 1 }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                                    @if($pType !== 'account')
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
                                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="flex h-9 w-9 items-center justify-center rounded-[1rem] border-2 border-white bg-white text-lg text-[#5a76c8] shadow-md shadow-[#bde0fe]/20 transition-transform active:scale-95 hover:scale-110 hover:bg-[#f0f5ff]" title="Edit">
                                                            &#x270F;&#xFE0F;
                                                        </a>
                                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini? Semua data gudang terkait juga akan hilang!');" class="m-0">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-[1rem] border-2 border-white bg-white text-lg text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:scale-110 hover:bg-[#ffe5e5]" title="Hapus">
                                                                &#x1F5D1;&#xFE0F;
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm shadow-2xl shadow-[#bde0fe]/30 relative z-10 min-w-0 w-full">
            <div class="px-5 py-16 sm:py-20 text-center">
                <div class="mb-4 sm:mb-5 inline-flex h-20 w-20 sm:h-24 sm:w-24 items-center justify-center rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#f4f9ff] text-4xl sm:text-5xl shadow-inner">P</div>
                <p class="text-lg sm:text-xl font-black text-[#5a76c8]">Belum ada produk di katalog.</p>
            </div>
        </div>
    @endif

    @if(isset($products) && method_exists($products, 'links') && $products->hasPages())
        <div class="mt-8 rounded-[2rem] border-4 border-white bg-[#f8faff] p-5 sm:p-8 shadow-xl shadow-[#bde0fe]/20 min-w-0 w-full">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
