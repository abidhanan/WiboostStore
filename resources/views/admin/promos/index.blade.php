@extends('layouts.admin')
@section('title', 'Manajemen Banner')
@section('admin_header_subtitle', 'Kelola slider pengumuman yang muncul di Dashboard User.')

{{-- Section admin_header_actions dihapus karena tombol Tambah dipindah ke area toolbar --}}

@section('content')
<style>
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    <!-- Toolbar: Pencarian & Tombol Tambah -->
    <div class="mb-6 sm:mb-8 flex flex-col md:flex-row gap-3 sm:gap-4 relative z-10 w-full min-w-0">
        
        <!-- Form Pencarian (Kiri di Desktop / Atas di Mobile) -->
        <div class="flex-1 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-3 sm:p-4 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:border-[#bde0fe] min-w-0">
            <form action="{{ route('admin.promos.index') }}" method="GET" class="flex gap-2 sm:gap-3 items-center w-full">
                <!-- Input Field Tanpa SVG Kiri -->
                <input type="text" name="search" value="{{ request('search') }}"
                    class="min-w-0 flex-1 rounded-[1.2rem] sm:rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-3 sm:py-4 px-5 sm:px-6 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                    placeholder="Cari judul, badge, deskripsi, atau tema...">
                
                <!-- Tombol Cari Emoji -->
                <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95 hover:bg-[#4760a9] text-base" title="Cari Banner">
                    🔍
                </button>
                
                <!-- Tombol Reset Emoji -->
                @if(request('search'))
                    <a href="{{ route('admin.promos.index') }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc] text-base" title="Reset Pencarian">
                        ✖️
                    </a>
                @endif
            </form>
        </div>

        <!-- Tombol Tambah Banner (Kanan di Desktop / Bawah di Mobile) -->
        <a href="{{ route('admin.promos.create') }}" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95 hover:bg-[#4760a9] w-full md:w-auto md:min-w-[190px] md:px-8 md:py-4 md:text-base min-w-0">
            <span class="text-base">➕</span> Tambah Banner
        </a>
    </div>

    <!-- Alert Messages (Aman dari 500 Error) -->
    @if(session('success'))
        <div class="mb-6 sm:mb-8 flex items-center gap-3 sm:gap-4 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 sm:px-6 py-4 font-black text-emerald-500 shadow-lg shadow-emerald-100/50 relative z-10 text-sm sm:text-base w-full">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">✅</span>
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 sm:mb-8 flex items-center gap-3 sm:gap-4 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-5 sm:px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10 text-sm sm:text-base w-full">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">⚠️</span>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Grid Data Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 relative z-10 w-full min-w-0">
        @forelse($promos ?? [] as $promo)
            @php
                $theme = $promo->theme ?? 'blue';
                $bgClass = 'from-[#8faaf3] to-[#5a76c8]';
                if($theme == 'teal') $bgClass = 'from-[#4bc6b9] to-[#3ba398]';
                elseif($theme == 'orange') $bgClass = 'from-[#fbbf24] to-[#d97706]';
                elseif($theme == 'rose') $bgClass = 'from-[#fb7185] to-[#e11d48]';
                
                $isActive = $promo->is_active ?? true;
            @endphp
            
            <div class="rounded-[2rem] sm:rounded-[2.5rem] p-6 sm:p-8 text-white relative overflow-hidden flex flex-col justify-between shadow-xl shadow-[#bde0fe]/20 border-4 border-white group transition-transform duration-300 hover:-translate-y-2 hover:scale-[1.02] min-w-0
                @if(empty($promo->image)) bg-gradient-to-br {{ $bgClass }} 
                @else bg-[#2b3a67] @endif
                {{ !$isActive ? 'opacity-70 grayscale-[50%]' : '' }}">
                
                <!-- Background Image Layer -->
                @if(!empty($promo->image))
                    <img src="{{ Storage::url($promo->image) }}" class="absolute inset-0 w-full h-full object-cover z-0 opacity-70 group-hover:scale-105 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-br from-black/80 to-transparent z-10"></div>
                @endif
                
                <!-- Card Header (Badge & Actions) -->
                <div class="relative z-20 flex justify-between items-start mb-6">
                    @if(filled($promo->badge_text))
                        <span class="bg-white/20 backdrop-blur-md text-white text-[8px] sm:text-[10px] font-black px-3 sm:px-4 py-1.5 rounded-full shadow-inner border border-white/30 tracking-widest uppercase truncate max-w-[120px]">
                            {{ $promo->badge_text }}
                        </span>
                    @endif
                    
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('admin.promos.edit', $promo->id) }}" class="w-8 h-8 sm:w-10 sm:h-10 bg-white/20 hover:bg-white/40 backdrop-blur-md rounded-[1rem] flex items-center justify-center transition-colors border border-white/30 text-sm sm:text-lg shadow-sm" title="Edit">
                            ✏️
                        </a>
                        <form action="{{ route('admin.promos.destroy', $promo->id) }}" method="POST" onsubmit="return confirm('Hapus banner ini secara permanen?');" class="m-0">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-8 h-8 sm:w-10 sm:h-10 bg-rose-500/80 hover:bg-rose-500 backdrop-blur-md rounded-[1rem] flex items-center justify-center transition-colors border border-rose-400 text-sm sm:text-lg shadow-sm" title="Hapus">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Card Content -->
                <div class="relative z-20 mt-auto pt-6 flex flex-col min-w-0">
                    <h4 class="text-xl sm:text-2xl font-black mb-1 sm:mb-2 leading-tight drop-shadow-md break-words line-clamp-2">
                        {{ $promo->title ?? '-' }}
                    </h4>
                    <p class="font-bold text-white/90 text-xs sm:text-sm line-clamp-2 sm:line-clamp-3 drop-shadow-sm break-words">
                        {{ $promo->description ?? '-' }}
                    </p>
                </div>
                
                <!-- Floating Emoji Decor (Only if no image) -->
                @if(empty($promo->image))
                    <div class="text-6xl sm:text-8xl opacity-30 absolute -right-4 -bottom-4 transform rotate-12 pointer-events-none group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500 drop-shadow-lg">
                        {{ $promo->emoji ?? '📢' }}
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full text-center py-16 sm:py-20 bg-white/90 backdrop-blur-sm rounded-[2rem] sm:rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20">
                <div class="text-6xl sm:text-7xl mb-4 sm:mb-5 animate-float opacity-50 block">🪧</div>
                <p class="text-[#5a76c8] font-black text-xl sm:text-2xl drop-shadow-sm">Belum ada banner yang dibuat.</p>
                <p class="text-[#8faaf3] font-bold mt-2 text-sm sm:text-base">Buat pengumuman pertamamu sekarang!</p>
            </div>
        @endforelse
    </div>

    <!-- Paginasi (Aman dari Error: Pengecekan method links()) -->
    @if(isset($promos) && method_exists($promos, 'links') && $promos->hasPages())
        <div class="mt-6 sm:mt-8 p-5 sm:p-8 border-4 border-white rounded-[2rem] sm:rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 bg-white/90 backdrop-blur-sm min-w-0 w-full">
            {{ $promos->links() }}
        </div>
    @endif
</div>
@endsection
