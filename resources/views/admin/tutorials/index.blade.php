@extends('layouts.admin')
@section('title', 'Manajemen Tutorial')
@section('admin_header_subtitle', 'Kelola panduan artikel dan video YouTube untuk pelanggan.')

{{-- Section admin_header_actions dihapus karena tombol Tambah dipindah ke area toolbar --}}

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

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    <!-- Floating Emojis Background -->
    <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">📖</div>
    <div class="absolute top-1/3 left-5 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 right-[15%] text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">💡</div>

    <!-- Toolbar: Pencarian & Tombol Tambah -->
    <div class="mb-6 sm:mb-8 flex flex-col md:flex-row gap-3 sm:gap-4 relative z-10 w-full min-w-0">
        
        <!-- Form Pencarian (Kiri di Desktop / Atas di Mobile) -->
        <div class="flex-1 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-3 sm:p-4 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:border-[#bde0fe] min-w-0">
            <form action="{{ route('admin.tutorials.index') }}" method="GET" class="flex gap-2 sm:gap-3 items-center w-full">
                <!-- Input Field Tanpa SVG Kiri -->
                <input type="text" name="search" value="{{ request('search') }}"
                    class="min-w-0 flex-1 rounded-[1.2rem] sm:rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-3 sm:py-4 px-5 sm:px-6 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                    placeholder="Cari judul, kategori, atau deskripsi...">
                
                <!-- Tombol Cari Emoji Saja -->
                <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95 hover:bg-[#4760a9] text-base" title="Cari Tutorial">
                    🔍
                </button>
                
                <!-- Tombol Reset Emoji -->
                @if(request('search'))
                    <a href="{{ route('admin.tutorials.index') }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc] text-base" title="Reset Pencarian">
                        ✖️
                    </a>
                @endif
            </form>
        </div>

        <!-- Tombol Tambah Tutorial (Kanan di Desktop / Bawah di Mobile) -->
        <a href="{{ route('admin.tutorials.create') }}" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95 hover:bg-[#4760a9] w-full md:w-auto md:h-12 min-w-0">
            <span class="text-base">➕</span> Tambah Tutorial
        </a>
    </div>

    <!-- Alert Messages (Aman dari 500 Error) -->
    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-5 sm:px-6 py-4 rounded-[2rem] mb-6 sm:mb-8 font-black flex items-center gap-3 shadow-lg shadow-emerald-100/50 relative z-10 w-full text-sm sm:text-base">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">✅</span>
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-5 sm:px-6 py-4 rounded-[2rem] mb-6 sm:mb-8 font-black flex items-center gap-3 shadow-lg shadow-[#ff6b6b]/20 relative z-10 w-full text-sm sm:text-base">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">⚠️</span>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Grid Data Tutorial -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 relative z-10 w-full min-w-0">
        @forelse($tutorials ?? [] as $tut)
            <div class="bg-white/95 backdrop-blur-sm rounded-[2rem] sm:rounded-[2.5rem] p-5 sm:p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/30 hover:border-[#bde0fe] hover:-translate-y-2 transition-all duration-300 flex flex-col h-full group relative overflow-hidden min-w-0">
                
                <!-- Card Header -->
                <div class="flex justify-between items-start mb-4 sm:mb-5 relative z-10 w-full min-w-0">
                    <!-- Ikon/Gambar -->
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-[#f4f9ff] rounded-[1rem] sm:rounded-[1.5rem] flex items-center justify-center text-2xl sm:text-3xl border-4 border-white shadow-inner overflow-hidden shrink-0 group-hover:scale-110 transition-transform duration-300">
                        @if(!empty($tut->image))
                            <img src="{{ Storage::url($tut->image) }}" class="w-full h-full object-cover">
                        @else
                            {{ $tut->icon ?? '📖' }}
                        @endif
                    </div>
                    
                    <!-- Label Kategori & Status -->
                    <div class="flex flex-col items-end gap-1.5 sm:gap-2 min-w-0">
                        <span class="bg-[#f0f5ff] text-[#5a76c8] text-[8px] sm:text-[9px] font-black px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg uppercase tracking-widest border border-white shadow-sm truncate max-w-[120px] text-right">
                            {{ $tut->category ?? 'Umum' }}
                        </span>
                        @if(isset($tut->is_active) && !$tut->is_active)
                            <span class="bg-rose-50 text-rose-500 text-[8px] sm:text-[9px] font-black px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg uppercase tracking-widest border border-rose-100 shadow-sm truncate">Sembunyi</span>
                        @endif
                    </div>
                </div>
                
                <!-- Card Body (Teks) -->
                <div class="relative z-10 flex-1 flex flex-col min-w-0">
                    <h4 class="font-black text-lg sm:text-xl text-[#2b3a67] mb-1 sm:mb-2 leading-tight drop-shadow-sm group-hover:text-[#5a76c8] transition-colors break-words line-clamp-2">
                        {{ $tut->title ?? '-' }}
                    </h4>
                    <p class="text-[11px] sm:text-xs font-bold text-[#8faaf3] mb-4 sm:mb-6 flex-1 line-clamp-3 break-words">
                        {{ $tut->description ?? '-' }}
                    </p>
                </div>
                
                <!-- Card Footer (Aksi) -->
                <div class="flex items-center gap-2 sm:gap-3 mt-auto pt-4 sm:pt-5 border-t-4 border-dashed border-[#f4f9ff] relative z-10">
                    <a href="{{ route('admin.tutorials.edit', $tut->id) }}" class="flex-1 bg-[#f4f9ff] hover:bg-[#5a76c8] hover:text-white text-[#5a76c8] py-2.5 sm:py-3.5 rounded-xl font-black text-xs sm:text-sm transition-colors border-2 sm:border-4 border-white shadow-sm text-center flex items-center justify-center gap-1.5 sm:gap-2 active:scale-95">
                        <span class="text-sm sm:text-base">✏️</span> Edit
                    </a>
                    <form action="{{ route('admin.tutorials.destroy', $tut->id) }}" method="POST" class="flex-1 m-0" onsubmit="return confirm('Yakin ingin menghapus tutorial ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full bg-[#ffe5e5] hover:bg-[#ff6b6b] hover:text-white text-[#ff6b6b] py-2.5 sm:py-3.5 rounded-xl font-black text-xs sm:text-sm transition-colors border-2 sm:border-4 border-white shadow-sm flex items-center justify-center gap-1.5 sm:gap-2 active:scale-95">
                            <span class="text-sm sm:text-base">🗑️</span> Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 sm:py-24 bg-white/90 backdrop-blur-sm rounded-[2rem] sm:rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20">
                <div class="text-6xl sm:text-7xl mb-4 sm:mb-6 opacity-40 animate-float block">📖</div>
                <p class="text-[#5a76c8] font-black text-xl sm:text-2xl drop-shadow-sm">Belum ada Tutorial.</p>
                <p class="text-[#8faaf3] font-bold mt-2 text-sm sm:text-base">Bantu pelangganmu dengan membuat panduan pertama!</p>
            </div>
        @endforelse
    </div>

    <!-- Paginasi (Aman dari Error) -->
    @if(isset($tutorials) && method_exists($tutorials, 'links') && $tutorials->hasPages())
        <div class="mt-6 sm:mt-8 p-5 sm:p-8 border-4 border-white rounded-[2rem] sm:rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 bg-white/90 backdrop-blur-sm min-w-0 w-full">
            {{ $tutorials->links() }}
        </div>
    @endif
</div>
@endsection
