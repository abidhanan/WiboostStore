@extends('layouts.admin')
@section('title', 'Manajemen Produk')
@section('admin_header_subtitle', 'Kelola harga, stok, dan provider.')

@section('content')
<style>
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; border: 2px solid #f0f5ff; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
</style>

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    @php
        $searchValue = request('search');
        $activeProductFilter = $productFilter ?? request('product_filter', 'all');
        $productFilters = [
            'all' => ['label' => 'Semua'],
            'api' => ['label' => 'API'],
            'account' => ['label' => 'Gudang Akun'],
        ];
    @endphp

    <div class="relative z-10 mb-10 flex w-full min-w-0 flex-col gap-3 rounded-[2rem] border-4 border-white bg-white/90 p-3 shadow-xl shadow-[#bde0fe]/25 backdrop-blur-sm sm:mb-12 sm:rounded-[2.5rem] sm:p-4 lg:mb-14">
        <div class="flex flex-col md:flex-row gap-3 sm:gap-4 w-full min-w-0">
            <div class="min-w-0 flex-1">
                <form action="{{ route('admin.products.index') }}" method="GET" class="flex gap-2 sm:gap-3 items-center w-full">
                    @if($activeProductFilter !== 'all')
                        <input type="hidden" name="product_filter" value="{{ $activeProductFilter }}">
                    @endif
                    <input type="text" name="search" value="{{ $searchValue }}"
                        class="min-w-0 flex-1 rounded-[1.2rem] sm:rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-3 sm:py-4 px-5 sm:px-6 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                        placeholder="Cari nama produk atau SKU provider...">

                    <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95 hover:bg-[#4760a9] text-base" title="Cari Produk">
                        @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                    </button>

                    @if($searchValue)
                        <a href="{{ route('admin.products.index', array_filter(['product_filter' => $activeProductFilter === 'all' ? null : $activeProductFilter])) }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc] text-base" title="Reset Pencarian">
                            @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                        </a>
                    @endif
                </form>
            </div>

            <a href="{{ route('admin.products.create') }}" class="flex w-full min-w-0 shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem] lg:w-auto lg:min-w-[190px] lg:px-7">
                @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-5 w-5'])
                Tambah Produk
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

    @include('admin.partials.filter-tabs', [
        'filters' => $productFilters,
        'activeFilter' => $activeProductFilter,
        'filterParam' => 'product_filter',
        'filterRoute' => 'admin.products.index',
        'filterQuery' => ['search' => $searchValue],
    ])

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
                                <h3 class="text-xl font-black text-[#2b3a67] sm:text-2xl">{{ $section['name'] }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="w-full overflow-x-auto table-scroll pb-2">
                        <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                            <table class="w-full text-left" style="min-width: 1370px; table-layout: fixed;">
                                <thead>
                                    <tr>
                                        <th style="font-size: 10px; width: 90px;" class="whitespace-nowrap px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Visual</th>
                                        <th style="font-size: 10px; width: 320px;" class="whitespace-nowrap px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Produk</th>
                                        <th style="font-size: 10px; width: 170px;" class="whitespace-nowrap px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Tipe Proses</th>
                                        <th style="font-size: 10px; width: 150px;" class="whitespace-nowrap px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Harga Buyer</th>
                                        <th style="font-size: 10px; width: 160px;" class="whitespace-nowrap px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Harga Member</th>
                                        <th style="font-size: 10px; width: 135px;" class="whitespace-nowrap px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Modal</th>
                                        <th style="font-size: 10px; width: 210px;" class="whitespace-nowrap px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Provider</th>
                                        <th style="font-size: 10px; width: 115px;" class="whitespace-nowrap px-5 py-4 pr-8 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Stok</th>
                                        <th style="font-size: 10px; width: 145px;" class="whitespace-nowrap px-5 py-4 pl-8 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                                    @foreach($categorySections as $categorySection)
                                        @foreach($categorySection['products'] as $product)
                                            @php
                                                $productRootCategory = $product->category;
                                                while($productRootCategory?->parent) {
                                                    $productRootCategory = $productRootCategory->parent;
                                                }

                                                $productRootSlug = $productRootCategory?->slug;
                                                $memberMarkupRate = \App\Models\Product::memberMarkupRateFor($productRootSlug, collect([
                                                    $productRootCategory?->name,
                                                    $product->category?->name,
                                                    $product->name,
                                                    $product->slug,
                                                ])->filter()->implode(' '));
                                                $memberOriginalPrice = null;

                                                if ($productRootSlug === 'aplikasi-premium' && filled($product->membership_price)) {
                                                    $memberOriginalPrice = (float) $product->membership_price;
                                                } elseif (($product->uses_api_selling_markup ?? false) && $memberMarkupRate > 0) {
                                                    $memberOriginalPrice = (float) ceil(round(((float) $product->price) * (1 + $memberMarkupRate), 6));
                                                }

                                                $memberDisplayPrice = $memberOriginalPrice !== null
                                                    ? (float) max(0, ceil(round($memberOriginalPrice * (1 - \App\Models\Product::currentDiscountRate()), 6)))
                                                    : null;
                                            @endphp
                                            <tr class="transition-colors hover:bg-[#f8faff] group">
                                                <td class="px-5 py-4 align-middle sm:py-5">
                                                    @if(!empty($product->display_logo_image))
                                                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-[1rem] border-2 border-white bg-[#f4f9ff] shadow-sm">
                                                            <img src="{{ Storage::url($product->display_logo_image) }}" class="h-full w-full object-cover" alt="{{ $product->name }}">
                                                        </div>
                                                    @else
                                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#f0f5ff] text-xl shadow-inner group-hover:scale-110 transition-transform">
                                                            {{ $product->display_logo_emote ?? 'P' }}
                                                        </div>
                                                    @endif
                                                </td>

                                                <td class="px-5 py-4 align-middle sm:py-5" style="width: 320px; max-width: 320px;">
                                                    <p class="text-[11px] font-black leading-tight text-[#2b3a67] sm:text-sm sm:leading-snug" title="{{ $product->name ?? '-' }}" style="display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; text-overflow: clip; white-space: normal; word-break: normal; overflow-wrap: anywhere;">
                                                        {{ $product->name ?? '-' }}
                                                    </p>
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
                                                    <p class="text-xs sm:text-sm font-black text-[#5a76c8] drop-shadow-sm">Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}</p>
                                                </td>

                                                <td class="px-5 py-4 sm:py-5 align-middle">
                                                    <p class="whitespace-nowrap text-xs font-black drop-shadow-sm sm:text-sm" style="color:#8b5cf6;">
                                                        {{ $memberDisplayPrice !== null ? 'Rp ' . number_format($memberDisplayPrice, 0, ',', '.') : '-' }}
                                                    </p>
                                                </td>

                                                <td class="px-5 py-4 sm:py-5 align-middle">
                                                    <p style="font-size: 9px;" class="whitespace-nowrap font-black uppercase tracking-widest text-[#8faaf3]">
                                                        {{ $product->uses_api_selling_markup ? 'Rp ' . number_format($product->price ?? 0, 0, ',', '.') : '-' }}
                                                    </p>
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
                                                            @include('partials.wiboost-icon', ['name' => 'edit', 'class' => 'h-4 w-4'])
                                                        </a>
                                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini? Semua data gudang terkait juga akan hilang!');" class="m-0">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-[1rem] border-2 border-white bg-white text-lg text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:scale-110 hover:bg-[#ffe5e5]" title="Hapus">
                                                                @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-4 w-4'])
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
