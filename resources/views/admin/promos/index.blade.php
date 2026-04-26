@extends('layouts.admin')

@section('title', 'Manajemen Banner')
@section('admin_header_subtitle', 'Kelola slider pengumuman yang muncul di Dashboard User.')
@section('admin_header_actions')
    <a href="{{ route('admin.promos.create') }}" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#5a76c8] px-6 py-3 font-black text-white shadow-lg shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95 sm:w-auto">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
        Tambah Banner
    </a>
@endsection

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12">
    <div class="hidden">
        <div>
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Banner 📢</h3>
            <p class="text-sm text-[#8faaf3] font-bold mt-1">Kelola slider pengumuman yang muncul di Dashboard User.</p>
        </div>
        <a href="{{ route('admin.promos.create') }}" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white px-6 py-3 rounded-full font-black transition-transform active:scale-95 flex items-center gap-2 shadow-lg shadow-[#5a76c8]/30 border-4 border-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
            Tambah Banner
        </a>
    </div>

    <div class="mb-8 rounded-[2rem] border-4 border-white bg-white p-5 shadow-lg shadow-[#bde0fe]/20">
        <form action="{{ route('admin.promos.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-[#8faaf3]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full rounded-[1.5rem] border-2 border-[#e0fbfc] bg-[#f4f9ff] py-4 pl-14 pr-5 font-black text-[#2b3a67] outline-none transition placeholder-[#a3bbfb] focus:border-[#5a76c8]"
                    placeholder="Cari judul, badge, deskripsi, atau tema banner...">
            </div>
            <button type="submit" class="rounded-[1.5rem] border-2 border-white bg-[#5a76c8] px-10 py-4 font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                Cari Banner
            </button>
            @if(request('search'))
                <a href="{{ route('admin.promos.index') }}" class="flex items-center justify-center rounded-[1.5rem] border-2 border-white bg-[#ffe5e5] px-8 py-4 font-black text-[#ff6b6b] transition hover:bg-[#ffcccc]">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">🎉</span> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($promos as $promo)
            @php
                $bgClass = 'from-[#8faaf3] to-[#5a76c8]';
                if($promo->theme == 'teal') $bgClass = 'from-[#4bc6b9] to-[#3ba398]';
                elseif($promo->theme == 'orange') $bgClass = 'from-[#fbbf24] to-[#d97706]';
                elseif($promo->theme == 'rose') $bgClass = 'from-[#fb7185] to-[#e11d48]';
            @endphp

            <div class="rounded-[2.5rem] p-6 text-white relative overflow-hidden flex flex-col justify-between shadow-lg border-4 border-white group transition-transform hover:-translate-y-2
                @if(!$promo->image) bg-gradient-to-br {{ $bgClass }} 
                @else bg-[#2b3a67] @endif
                {{ !$promo->is_active ? 'opacity-60 grayscale' : '' }}">
                
                @if($promo->image)
                    <img src="{{ Storage::url($promo->image) }}" class="absolute inset-0 w-full h-full object-cover z-0 opacity-60">
                    <div class="absolute inset-0 bg-gradient-to-br from-black/80 to-transparent z-10"></div>
                @endif
                
                <div class="relative z-20">
                    <div class="flex justify-between items-start mb-3">
                        <span class="bg-white/20 backdrop-blur-md text-white text-[10px] font-black px-3 py-1 rounded-full shadow-inner tracking-widest uppercase">{{ $promo->badge_text }}</span>
                        
                        <div class="flex gap-2">
                            <a href="{{ route('admin.promos.edit', $promo->id) }}" class="w-8 h-8 bg-white/20 hover:bg-white/40 backdrop-blur-md rounded-full flex items-center justify-center transition-colors">✏️</a>
                            <form action="{{ route('admin.promos.destroy', $promo->id) }}" method="POST" onsubmit="return confirm('Hapus promo ini?');" class="m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 bg-white/20 hover:bg-white/40 backdrop-blur-md rounded-full flex items-center justify-center transition-colors">🗑️</button>
                            </form>
                        </div>
                    </div>
                    
                    <h4 class="text-xl font-black mb-1 leading-tight drop-shadow-sm">{{ $promo->title }}</h4>
                    <p class="font-bold text-white/90 text-xs line-clamp-2">{{ $promo->description }}</p>
                </div>
                
                @if(!$promo->image)
                    <div class="text-6xl opacity-40 absolute -right-2 -bottom-2 transform rotate-12 pointer-events-none group-hover:scale-110 transition-transform">
                        {{ $promo->emoji }}
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full text-center py-16 bg-white rounded-[2.5rem] border-4 border-dashed border-[#bde0fe]">
                <div class="text-6xl mb-4">📢</div>
                <p class="text-[#5a76c8] font-black text-xl">Belum ada banner yang dibuat.</p>
                <p class="text-[#8faaf3] font-bold mt-2">Buat pengumuman pertamamu sekarang!</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
