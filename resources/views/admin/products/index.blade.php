@extends('layouts.admin')

@section('title', 'Manajemen Produk')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4 pl-2">
        <div>
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Katalog Produk 🛒</h3>
            <p class="text-sm text-[#8faaf3] font-bold mt-1">Kelola harga, status aktif, dan kode SKU Provider di sini.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white px-6 py-3 rounded-full font-black transition-transform active:scale-95 flex items-center gap-2 shadow-lg shadow-[#5a76c8]/30 border-4 border-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
            Tambah Produk
        </a>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">🎉</span> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-5 rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white mb-8">
        <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-[#8faaf3]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="w-full pl-14 pr-5 py-4 bg-[#f4f9ff] rounded-[1.5rem] border-2 border-[#e0fbfc] focus:border-[#5a76c8] outline-none transition text-[#2b3a67] font-black placeholder-[#a3bbfb]" 
                       placeholder="Cari Nama Produk atau SKU Provider...">
            </div>
            <button type="submit" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white px-10 py-4 rounded-[1.5rem] font-black transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white whitespace-nowrap">
                Cari Produk
            </button>
            @if(request('search'))
                <a href="{{ route('admin.products.index') }}" class="bg-[#ffe5e5] hover:bg-[#ffcccc] text-[#ff6b6b] px-8 py-4 rounded-[1.5rem] font-black transition flex items-center justify-center border-2 border-white whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white overflow-hidden">
        <div class="overflow-x-auto p-4">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Layanan / Produk</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Kategori</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Harga Jual</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">SKU Provider</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                    @forelse($products as $product)
                    <tr class="hover:bg-[#f4f9ff] transition-colors rounded-xl group">
                        <td class="px-6 py-4 min-w-[200px] whitespace-normal">
                            <p class="font-black text-[#2b3a67] text-md line-clamp-2 leading-tight">{{ $product->name }}</p>
                            <p class="text-[10px] font-bold text-[#8faaf3] truncate max-w-[200px] mt-1">{{ $product->description ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-[#e0fbfc] text-[#4bc6b9] px-3 py-1.5 rounded-full text-xs font-black border border-white shadow-sm">
                                {{ $product->category->name ?? 'Tanpa Kategori' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-black text-[#5a76c8] text-sm">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-[#f0f5ff] text-[#8faaf3] px-3 py-1.5 rounded-md text-xs font-mono font-black border border-white shadow-inner">
                                {{ $product->provider_product_id ?? 'Manual' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($product->is_active)
                                <span class="bg-[#e6fff7] text-emerald-500 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white shadow-sm">Aktif</span>
                            @else
                                <span class="bg-[#ffe5e5] text-[#ff6b6b] px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white shadow-sm">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="bg-[#fff5eb] text-amber-500 hover:bg-amber-500 hover:text-white p-2.5 rounded-xl transition-colors shadow-sm border-2 border-white" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="bg-[#ffe5e5] text-[#ff6b6b] hover:bg-[#ff6b6b] hover:text-white p-2.5 rounded-xl transition-colors shadow-sm border-2 border-white" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-[1.5rem] bg-[#f0f5ff] border-4 border-white mb-3 shadow-inner">
                                <span class="text-3xl">🛍️</span>
                            </div>
                            <p class="text-[#8faaf3] font-black text-sm">Data produk tidak ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(method_exists($products, 'links') && $products->hasPages())
            <div class="p-6 border-t-2 border-dashed border-[#f0f5ff] bg-[#f4f9ff]">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection