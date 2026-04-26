@extends('layouts.admin')

@section('title', 'Manajemen Produk')
@section('admin_header_subtitle', 'Kelola harga, stok otomatis, dan sinkronisasi layanan provider ke website.')
@section('admin_header_actions')
    <form action="{{ route('admin.products.sync.digiflazz') }}" method="POST" class="w-full sm:w-auto">
        @csrf
        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#4bc6b9] px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-[#4bc6b9]/25 transition hover:bg-[#3ba398] sm:w-auto">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m14.836 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-14.837-2m14.837 2H15"></path></svg>
            Sync Digiflazz
        </button>
    </form>

    <form action="{{ route('admin.products.sync.ordersosmed') }}" method="POST" class="w-full sm:w-auto">
        @csrf
        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#8faaf3] px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-[#8faaf3]/25 transition hover:bg-[#6f8ddc] sm:w-auto">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m14.836 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-14.837-2m14.837 2H15"></path></svg>
            Sync OrderSosmed
        </button>
    </form>

    <a href="{{ route('admin.products.create') }}" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#5a76c8] px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9] sm:w-auto">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
        Tambah Produk
    </a>
@endsection

@section('content')
    <div class="pb-12" style="font-family: 'Nunito', sans-serif;">
        @if(session('success'))
            <div class="mb-8 flex items-start gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-6 py-4 font-black text-emerald-500 shadow-sm">
                <span class="mt-0.5 text-xl">OK</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 flex items-start gap-3 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-sm">
                <span class="mt-0.5 text-xl">!</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="mb-8 rounded-[2rem] border-4 border-white bg-white p-5 shadow-lg shadow-[#bde0fe]/20">
            <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-[#8faaf3]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full rounded-[1.5rem] border-2 border-[#e0fbfc] bg-[#f4f9ff] py-4 pl-14 pr-5 font-black text-[#2b3a67] outline-none transition placeholder-[#a3bbfb] focus:border-[#5a76c8]"
                        placeholder="Cari nama produk atau SKU provider...">
                </div>

                <button type="submit" class="rounded-[1.5rem] border-2 border-white bg-[#5a76c8] px-10 py-4 font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                    Cari Produk
                </button>

                @if(request('search'))
                    <a href="{{ route('admin.products.index') }}" class="flex items-center justify-center rounded-[1.5rem] border-2 border-white bg-[#ffe5e5] px-8 py-4 font-black text-[#ff6b6b] transition hover:bg-[#ffcccc]">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-hidden rounded-[2rem] border-4 border-white bg-white shadow-lg shadow-[#bde0fe]/20">
            <div class="overflow-x-auto p-4">
                <table class="w-full min-w-[920px] text-left">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Produk</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] text-center">Tipe Proses</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Harga Jual</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] text-center">Provider</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] text-center">Stok</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                        @forelse($products as $product)
                            <tr class="transition hover:bg-[#f4f9ff]">
                                <td class="min-w-[220px] whitespace-normal px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($product->image)
                                            <img src="{{ Storage::url($product->image) }}" class="h-10 w-10 rounded-xl border-2 border-white object-cover shadow-sm">
                                        @elseif($product->emote)
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl border-2 border-white bg-[#f0f5ff] text-xl shadow-inner">{{ $product->emote }}</div>
                                        @endif

                                        <div>
                                            <p class="line-clamp-1 text-md font-black leading-tight text-[#2b3a67]">{{ $product->name }}</p>
                                            <span class="mt-1 inline-block rounded-md bg-[#e0fbfc] px-2 py-0.5 text-[9px] font-black uppercase text-[#4bc6b9] shadow-sm">
                                                {{ $product->category?->breadcrumb_name ?? 'N/A' }}
                                            </span>
                                            @if(! $product->is_active)
                                                <span class="ml-1 text-[9px] font-black uppercase text-rose-400">[Nonaktif]</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if($product->process_type === 'api')
                                        <span class="inline-block rounded-lg border border-[#e0ebff] bg-[#f4f9ff] px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">API</span>
                                    @elseif(in_array($product->process_type, ['account', 'number'], true))
                                        <a href="{{ route('admin.credentials.index', $product->id) }}" class="inline-flex items-center gap-2 rounded-lg border border-white bg-[#ffeef2] px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#e1306c] shadow-sm transition hover:bg-[#e1306c] hover:text-white">
                                            Gudang {{ $product->process_type === 'account' ? 'Akun' : 'Nomor' }}
                                        </a>
                                    @else
                                        <span class="inline-block rounded-lg border border-[#e0ebff] bg-[#f4f9ff] px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Manual</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm font-black text-[#5a76c8]">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="space-y-2">
                                        <span class="inline-block rounded-md border border-white bg-[#f0f5ff] px-3 py-1.5 font-mono text-xs font-black text-[#8faaf3] shadow-inner">
                                            {{ $product->provider_product_id ?? '-' }}
                                        </span>
                                        <div class="text-[10px] font-black uppercase tracking-widest text-[#5a76c8]">
                                            {{ $product->provider_source ?? '-' }}
                                            @if(($product->provider_source ?? '') === 'ordersosmed')
                                                <span class="ml-1 text-[#8faaf3]">x{{ $product->provider_quantity ?? 1 }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if(in_array($product->process_type, ['api', 'manual'], true))
                                        <span class="text-lg font-black text-[#8faaf3]">-</span>
                                    @else
                                        @php($stock = (int) $product->available_stock)
                                        @if($stock <= $product->stock_reminder)
                                            <span class="inline-block min-w-[70px] rounded-full border border-white bg-[#ffe5e5] px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#ff6b6b] shadow-sm">
                                                {{ $stock }} (Limit)
                                            </span>
                                        @else
                                            <span class="inline-block min-w-[70px] rounded-full border border-white bg-[#e6fff7] px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-500 shadow-sm">
                                                {{ $stock }}
                                            </span>
                                        @endif
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="rounded-xl border-2 border-white bg-[#fff5eb] p-2.5 text-amber-500 shadow-sm transition hover:bg-amber-500 hover:text-white" title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>

                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini? Semua data gudang terkait juga akan hilang!');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl border-2 border-white bg-[#ffe5e5] p-2.5 text-[#ff6b6b] shadow-sm transition hover:bg-[#ff6b6b] hover:text-white" title="Hapus">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="mb-4 inline-flex h-20 w-20 items-center justify-center rounded-[2rem] border-4 border-white bg-[#f4f9ff] text-4xl shadow-inner">-</div>
                                    <p class="text-lg font-black text-[#8faaf3]">Belum ada produk di katalog.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($products, 'links') && $products->hasPages())
                <div class="border-t-2 border-dashed border-[#f0f5ff] bg-[#f4f9ff] p-6">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
