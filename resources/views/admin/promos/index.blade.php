@extends('layouts.admin')

@section('title', 'Manajemen Banner')
@section('admin_header_subtitle', 'Kelola slider pengumuman yang muncul di Dashboard User.')
@section('admin_header_actions')
    <a href="{{ route('admin.promos.create') }}" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#5a76c8] px-8 py-3.5 font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95 sm:w-auto">
        ✨ Tambah Banner
    </a>
@endsection

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 relative z-10">

    <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">🎉</div>
    <div class="absolute top-1/3 left-5 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 right-[15%] text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="mb-10 rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-6 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:border-[#bde0fe] relative z-10">
        <form action="{{ route('admin.promos.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-6 text-[#8faaf3]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-4 pl-16 pr-5 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe]"
                    placeholder="Cari judul, badge, deskripsi, atau tema banner...">
            </div>
            <button type="submit" class="rounded-full border-4 border-white bg-[#5a76c8] px-10 py-4 font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9]">
                Cari Banner 🔍
            </button>
            @if(request('search'))
                <a href="{{ route('admin.promos.index') }}" class="flex items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-8 py-4 font-black text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc]">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2.5rem] mb-10 font-black flex items-center gap-4 shadow-lg shadow-emerald-100/50 relative z-10">
            <span class="text-3xl drop-shadow-sm">🎉</span> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
        @forelse($promos as $promo)
            @php
                $bgClass = 'from-[#8faaf3] to-[#5a76c8]';
                if($promo->theme == 'teal') $bgClass = 'from-[#4bc6b9] to-[#3ba398]';
                elseif($promo->theme == 'orange') $bgClass = 'from-[#fbbf24] to-[#d97706]';
                elseif($promo->theme == 'rose') $bgClass = 'from-[#fb7185] to-[#e11d48]';
            @endphp

            <div class="rounded-[2.5rem] p-8 text-white relative overflow-hidden flex flex-col justify-between shadow-xl shadow-[#bde0fe]/20 border-4 border-white group transition-transform duration-300 hover:-translate-y-2 hover:scale-[1.02]
                @if(!$promo->image) bg-gradient-to-br {{ $bgClass }} 
                @else bg-[#2b3a67] @endif
                {{ !$promo->is_active ? 'opacity-70 grayscale-[50%]' : '' }}">
                
                @if($promo->image)
                    <img src="{{ Storage::url($promo->image) }}" class="absolute inset-0 w-full h-full object-cover z-0 opacity-70 group-hover:scale-105 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-br from-black/80 to-transparent z-10"></div>
                @endif
                
                <div class="relative z-20 h-full flex flex-col">
                    <div class="flex justify-between items-start mb-6">
                        <span class="bg-white/20 backdrop-blur-md text-white text-[10px] font-black px-4 py-1.5 rounded-full shadow-inner border border-white/30 tracking-widest uppercase">{{ $promo->badge_text }}</span>
                        
                        <div class="flex gap-2">
                            <a href="{{ route('admin.promos.edit', $promo->id) }}" class="w-10 h-10 bg-white/20 hover:bg-white/40 backdrop-blur-md rounded-xl flex items-center justify-center transition-colors border border-white/30 text-lg shadow-sm" title="Edit">✏️</a>
                            <form action="{{ route('admin.promos.destroy', $promo->id) }}" method="POST" onsubmit="return confirm('Hapus banner ini secara permanen?');" class="m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-10 h-10 bg-rose-500/80 hover:bg-rose-500 backdrop-blur-md rounded-xl flex items-center justify-center transition-colors border border-rose-400 text-lg shadow-sm" title="Hapus">🗑️</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="mt-auto pt-6">
                        <h4 class="text-2xl font-black mb-2 leading-tight drop-shadow-md">{{ $promo->title }}</h4>
                        <p class="font-bold text-white/90 text-sm line-clamp-3 drop-shadow-sm">{{ $promo->description }}</p>
                    </div>
                </div>
                
                @if(!$promo->image)
                    <div class="text-8xl opacity-30 absolute -right-4 -bottom-4 transform rotate-12 pointer-events-none group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500 drop-shadow-lg">
                        {{ $promo->emoji }}
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full text-center py-20 bg-white/90 backdrop-blur-sm rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20">
                <div class="text-7xl mb-5 animate-float opacity-50 block">🎉</div>
                <p class="text-[#5a76c8] font-black text-2xl">Belum ada banner yang dibuat.</p>
                <p class="text-[#8faaf3] font-bold mt-2">Buat pengumuman pertamamu sekarang!</p>
            </div>
        @endforelse
    </div>
</div>
@endsection