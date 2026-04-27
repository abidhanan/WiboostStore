@extends('layouts.admin')
@section('title', 'Manajemen Tutorial')
@section('admin_header_subtitle', 'Kelola panduan artikel dan video YouTube untuk pelanggan.')
@section('admin_header_actions')
    <a href="{{ route('admin.tutorials.create') }}" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#5a76c8] px-8 py-3.5 font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95 sm:w-auto">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
        Tambah Tutorial
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

    <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">📖</div>
    <div class="absolute top-1/3 left-5 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 right-[15%] text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="mb-8 rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-6 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:border-[#bde0fe] relative z-10">
        <form action="{{ route('admin.tutorials.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-6 text-[#8faaf3]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-4 pl-16 pr-5 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe]"
                    placeholder="Cari judul, kategori, deskripsi, atau konten tutorial...">
            </div>
            <button type="submit" class="rounded-full border-4 border-white bg-[#5a76c8] px-10 py-4 font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9]">
                Cari Tutorial 🔍
            </button>
            @if(request('search'))
                <a href="{{ route('admin.tutorials.index') }}" class="flex items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-8 py-4 font-black text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc]">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2.5rem] mb-8 font-black flex items-center gap-4 shadow-lg shadow-emerald-100/50 relative z-10">
            <span class="text-3xl drop-shadow-sm">🎉</span> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
        @forelse($tutorials as $tut)
            <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] p-6 md:p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/30 hover:border-[#bde0fe] hover:-translate-y-2 transition-all duration-300 flex flex-col h-full group relative overflow-hidden">
                <div class="flex justify-between items-start mb-5 relative z-10">
                    <div class="w-16 h-16 bg-[#f4f9ff] rounded-[1.5rem] flex items-center justify-center text-3xl border-4 border-white shadow-inner overflow-hidden shrink-0 group-hover:scale-110 transition-transform duration-300">
                        @if($tut->image)
                            <img src="{{ Storage::url($tut->image) }}" class="w-full h-full object-cover">
                        @else
                            {{ $tut->icon ?? '📖' }}
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="bg-[#f0f5ff] text-[#5a76c8] text-[9px] font-black px-3 py-1.5 rounded-lg uppercase border border-white shadow-sm">{{ $tut->category }}</span>
                        @if(!$tut->is_active)
                            <span class="bg-rose-50 text-rose-500 text-[9px] font-black px-3 py-1.5 rounded-lg uppercase tracking-widest border border-rose-100 shadow-sm">Sembunyi</span>
                        @endif
                    </div>
                </div>

                <div class="relative z-10 flex-1 flex flex-col">
                    <h4 class="font-black text-xl text-[#2b3a67] mb-2 leading-tight drop-shadow-sm group-hover:text-[#5a76c8] transition-colors">{{ $tut->title }}</h4>
                    <p class="text-xs font-bold text-[#8faaf3] mb-6 flex-1 line-clamp-3">{{ $tut->description }}</p>
                </div>

                <div class="flex items-center gap-3 mt-auto pt-5 border-t-4 border-dashed border-[#f4f9ff] relative z-10">
                    <a href="{{ route('admin.tutorials.edit', $tut->id) }}" class="flex-1 bg-[#f4f9ff] hover:bg-[#5a76c8] hover:text-white text-[#5a76c8] py-3.5 rounded-xl font-black text-sm transition-colors border-4 border-white shadow-sm text-center flex items-center justify-center gap-2 active:scale-95">
                        ✏️ Edit
                    </a>
                    <form action="{{ route('admin.tutorials.destroy', $tut->id) }}" method="POST" class="flex-1 m-0" onsubmit="return confirm('Yakin ingin menghapus tutorial ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full bg-[#ffe5e5] hover:bg-[#ff6b6b] hover:text-white text-[#ff6b6b] py-3.5 rounded-xl font-black text-sm transition-colors border-4 border-white shadow-sm flex items-center justify-center gap-2 active:scale-95">
                            🗑️ Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-24 bg-white/90 backdrop-blur-sm rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20">
                <div class="text-7xl mb-6 opacity-40 animate-float block">📖</div>
                <p class="text-[#5a76c8] font-black text-2xl drop-shadow-sm">Belum ada Tutorial.</p>
                <p class="text-[#8faaf3] font-bold mt-2">Bantu pelangganmu dengan membuat panduan pertama!</p>
            </div>
        @endforelse
    </div>
</div>
@endsection