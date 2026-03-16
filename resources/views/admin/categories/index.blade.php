@extends('layouts.admin')

@section('title', 'Manajemen Kategori')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

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

    <div class="flex flex-col gap-4">
        @forelse($categories as $category)
            @if(is_null($category->parent_id))
                <div class="bg-gradient-to-r from-[#f0f5ff] to-[#e0ebff] rounded-[2rem] p-5 md:p-6 border-[4px] border-[#8faaf3] shadow-md flex flex-col md:flex-row items-center justify-between gap-6 group hover:shadow-lg transition-all">
                    
                    <div class="flex items-center gap-6 flex-1 w-full">
                        <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center text-5xl shadow-inner border-2 border-[#bde0fe] group-hover:scale-110 transition-transform shrink-0">
                            {{ $category->emote ?? '✨' }}
                        </div>
                        
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="font-black text-[#2b3a67] text-2xl leading-tight">{{ $category->name }}</h3>
                                <span class="bg-[#5a76c8] text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest shadow-sm">⭐ Utama</span>
                            </div>
                            <p class="text-sm font-bold text-[#5a76c8]">{{ $category->children()->count() }} Sub-Kategori terdaftar</p>
                        </div>
                    </div>
            @else
                <div class="bg-white rounded-[2rem] p-5 md:p-6 border-4 border-white shadow-sm hover:border-[#bde0fe] hover:shadow-md transition-all flex flex-col md:flex-row items-center justify-between gap-6 group">
                    
                    <div class="flex items-center gap-6 flex-1 w-full">
                        <div class="w-16 h-16 bg-[#f4f9ff] rounded-2xl flex items-center justify-center shadow-inner border-2 border-white overflow-hidden group-hover:rotate-3 transition-transform shrink-0">
                            @if($category->image)
                                <img src="{{ Storage::url($category->image) }}" class="w-full h-full object-contain p-1">
                            @else
                                <span class="text-3xl text-[#8faaf3]">📱</span>
                            @endif
                        </div>
                        
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="font-black text-[#2b3a67] text-xl leading-tight">{{ $category->name }}</h3>
                                <span class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest bg-[#f4f9ff] px-2 py-0.5 rounded-md border border-white">Sub dari: {{ $category->parent->name ?? 'Induk' }}</span>
                            </div>
                            <p class="text-xs font-bold text-[#8faaf3]">{{ $category->products()->count() }} Produk Layanan</p>
                        </div>
                    </div>
            @endif

                    <div class="flex items-center gap-3 w-full md:w-auto shrink-0 border-t-2 md:border-t-0 md:border-l-2 border-dashed border-[#8faaf3]/30 pt-4 md:pt-0 md:pl-6">
                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="flex-1 md:flex-none bg-[#f4f9ff] hover:bg-[#5a76c8] hover:text-white text-[#5a76c8] px-6 py-2.5 rounded-2xl font-black text-sm transition-colors border-2 border-white shadow-sm text-center">Edit</a>
                        
                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="flex-1 md:flex-none m-0" onsubmit="return confirm('Yakin ingin menghapus kategori ini? Pastikan kategori sudah kosong ya!')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-[#ffe5e5] hover:bg-[#ff6b6b] hover:text-white text-[#ff6b6b] px-6 py-2.5 rounded-2xl font-black text-sm transition-colors border-2 border-white shadow-sm">Hapus</button>
                        </form>
                    </div>
                </div>
        @empty
            <div class="text-center py-20 bg-white rounded-[3rem] border-4 border-dashed border-[#bde0fe]">
                <div class="text-7xl mb-6 opacity-40">📁</div>
                <p class="text-[#5a76c8] font-black text-xl">Belum ada kategori yang tersedia.</p>
                <p class="text-[#8faaf3] font-bold mt-2">Mulai dengan menambahkan kategori utama pertama kamu!</p>
            </div>
        @endforelse
    </div>

    @if(method_exists($categories, 'links') && $categories->hasPages())
        <div class="mt-10">
            {{ $categories->links() }}
        </div>
    @endif
</div>

<style>
    @keyframes bounce-short {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    .animate-bounce-short { animation: bounce-short 2s infinite; }
</style>
@endsection