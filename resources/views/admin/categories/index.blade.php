@extends('layouts.admin')

@section('title', 'Manajemen Kategori')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    @keyframes bounce-short {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    .animate-bounce-short { animation: bounce-short 2s infinite; }
</style>

@php
    // LOGIKA PEMISAH: Membagi Data Kategori Utama & Sub-Kategori
    $mainCategories = $categories->whereNull('parent_id');
    $subCategories = $categories->whereNotNull('parent_id');
@endphp

<div class="wiboost-font pb-12 max-w-6xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10 pl-2">
        <div>
            <h2 class="text-3xl font-black text-[#2b3a67] tracking-tight">Kategori Layanan 🗂️</h2>
            <p class="text-[#8faaf3] font-bold text-sm mt-1">Kelola struktur Kategori Utama dan Sub-Kategori aplikasi.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white px-8 py-3.5 rounded-full font-black transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-4 border-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
            Tambah Kategori
        </a>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm animate-bounce-short">
            <span class="text-2xl">🎉</span> {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">⚠️</span> {{ session('error') }}
        </div>
    @endif

    <div class="mb-12">
        <div class="flex items-center gap-3 mb-6 pl-2">
            <span class="text-2xl">⭐</span>
            <h3 class="text-xl font-black text-[#2b3a67] border-b-4 border-dashed border-[#bde0fe] pb-1">Kategori Utama</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($mainCategories as $category)
                <div class="bg-gradient-to-r from-[#f0f5ff] to-[#e0ebff] rounded-[2rem] p-5 md:p-6 border-[4px] border-[#8faaf3] shadow-md flex flex-col xl:flex-row items-center justify-between gap-6 group hover:shadow-lg transition-all h-full">
                    
                    <div class="flex items-center gap-5 flex-1 w-full">
                        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-4xl shadow-inner border-2 border-[#bde0fe] group-hover:scale-110 transition-transform shrink-0">
                            {{ $category->emote ?? '✨' }}
                        </div>
                        
                        <div class="flex-1">
                            <h3 class="font-black text-[#2b3a67] text-xl leading-tight mb-1">{{ $category->name }}</h3>
                            <p class="text-xs font-bold text-[#5a76c8]">{{ $category->children()->count() }} Sub-Kategori</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full xl:w-auto shrink-0 border-t-2 xl:border-t-0 xl:border-l-2 border-dashed border-[#8faaf3]/50 pt-4 xl:pt-0 xl:pl-4">
                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="flex-1 xl:flex-none bg-white hover:bg-[#5a76c8] hover:text-white text-[#5a76c8] px-4 py-2 rounded-xl font-black text-xs transition-colors border-2 border-[#bde0fe] shadow-sm text-center" title="Edit Kategori">✍️ Edit</a>
                        
                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="flex-1 xl:flex-none m-0" onsubmit="return confirm('Yakin ingin menghapus kategori UTAMA ini? Pastikan kosong ya!')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full bg-[#ffe5e5] hover:bg-[#ff6b6b] hover:text-white text-[#ff6b6b] px-4 py-2 rounded-xl font-black text-xs transition-colors border-2 border-white shadow-sm" title="Hapus Kategori">🗑️ Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-10 bg-white rounded-[2rem] border-4 border-dashed border-[#bde0fe]">
                    <p class="text-[#5a76c8] font-black text-lg">Belum ada Kategori Utama.</p>
                    <p class="text-[#8faaf3] font-bold text-sm mt-1">Tambahkan agar bisa membuat Sub-Kategori.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div>
        <div class="flex items-center gap-3 mb-6 pl-2">
            <span class="text-2xl">📱</span>
            <h3 class="text-xl font-black text-[#2b3a67] border-b-4 border-dashed border-[#e0fbfc] pb-1">Sub-Kategori Layanan</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($subCategories as $category)
                <div class="bg-white rounded-[2rem] p-5 border-4 border-[#f0f5ff] shadow-sm hover:border-[#bde0fe] hover:shadow-md transition-all flex flex-col xl:flex-row items-center justify-between gap-4 group h-full">
                    
                    <div class="flex items-center gap-4 flex-1 w-full">
                        <div class="w-14 h-14 bg-[#f4f9ff] rounded-[1rem] flex items-center justify-center shadow-inner border-2 border-white overflow-hidden group-hover:rotate-3 transition-transform shrink-0 p-1">
                            @if($category->image)
                                <img src="{{ Storage::url($category->image) }}" class="w-full h-full object-contain">
                            @else
                                <span class="text-2xl text-[#8faaf3]">📦</span>
                            @endif
                        </div>
                        
                        <div class="flex-1">
                            <h3 class="font-black text-[#2b3a67] text-lg leading-tight">{{ $category->name }}</h3>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <span class="text-[9px] font-black text-[#8faaf3] uppercase tracking-widest bg-[#f4f9ff] px-2 py-0.5 rounded border border-[#e0fbfc]">Induk: {{ $category->parent->name ?? 'Unknown' }}</span>
                                <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest bg-[#e6fff7] px-2 py-0.5 rounded border border-white">{{ $category->products()->count() }} Produk</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full xl:w-auto shrink-0 border-t-2 xl:border-t-0 xl:border-l-2 border-dashed border-[#f0f5ff] pt-4 xl:pt-0 xl:pl-4">
                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="flex-1 xl:flex-none bg-[#f4f9ff] hover:bg-[#5a76c8] hover:text-white text-[#5a76c8] px-4 py-2 rounded-xl font-black text-xs transition-colors border-2 border-white shadow-sm text-center">Edit</a>
                        
                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="flex-1 xl:flex-none m-0" onsubmit="return confirm('Yakin ingin menghapus sub-kategori ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full bg-[#ffe5e5] hover:bg-[#ff6b6b] hover:text-white text-[#ff6b6b] px-4 py-2 rounded-xl font-black text-xs transition-colors border-2 border-white shadow-sm">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-10 bg-white rounded-[2rem] border-4 border-dashed border-[#f0f5ff]">
                    <p class="text-[#8faaf3] font-black text-lg">Belum ada Sub-Kategori.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if(method_exists($categories, 'links') && $categories->hasPages())
        <div class="mt-10">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection