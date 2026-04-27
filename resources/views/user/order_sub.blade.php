@extends('layouts.user')

@section('title', 'Pilih Aplikasi - ' . $category->name)

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

<div class="wiboost-font pb-24 max-w-6xl mx-auto mt-4 px-4 relative z-10">
    
    <div class="absolute top-10 left-10 text-5xl animate-float opacity-50 pointer-events-none hidden md:block z-0">☁️</div>
    <div class="absolute top-1/3 right-10 text-4xl animate-float-delayed opacity-50 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 right-1/4 text-3xl animate-float opacity-40 pointer-events-none hidden md:block z-0">⭐</div>
    <div class="absolute bottom-10 left-[15%] text-6xl animate-float-delayed opacity-60 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="flex flex-col md:flex-row items-center md:items-start gap-4 mb-10 text-center md:text-left relative z-10">
        <a href="{{ $category->parent ? route('user.order.category', $category->parent->slug) : route('user.dashboard') }}" class="w-14 h-14 bg-white/95 backdrop-blur-sm rounded-[1.2rem] flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] transition-transform border-4 border-white shadow-lg shadow-[#bde0fe]/30 shrink-0 hover:scale-110 active:scale-95">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div class="flex-1 mt-2 md:mt-0">
            <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Pilih Kategori
            </div>
            <h2 class="text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">{{ $category->name }}</h2>
            @if($category->description)
                <p class="mt-3 text-sm font-bold text-[#8faaf3] max-w-2xl bg-white/50 px-5 py-3 rounded-[1.5rem] border-2 border-white shadow-sm mx-auto md:mx-0">{{ $category->description }}</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 relative z-10">
        @forelse($category->children as $child)
            @php
                $hasActiveProducts = (int) ($child->active_products_count ?? 0) > 0;
                $hasNestedChildren = (int) ($child->children_count ?? 0) > 0;
                $isBrowsable = $hasActiveProducts || $hasNestedChildren;
            @endphp

            @if($isBrowsable)
                <a href="{{ route('user.order.category', $child->slug) }}" class="group bg-white/95 backdrop-blur-sm p-6 md:p-8 rounded-[2.5rem] border-4 border-white hover:border-[#bde0fe] shadow-xl shadow-[#bde0fe]/30 hover:-translate-y-2 hover:scale-[1.02] transition-all duration-300 text-center flex flex-col items-center justify-center">
            @else
                <div class="group bg-white/60 backdrop-blur-sm p-6 md:p-8 rounded-[2.5rem] border-4 border-white shadow-md shadow-[#bde0fe]/10 text-center flex flex-col items-center justify-center opacity-80 grayscale-[20%]">
            @endif

                <div class="w-24 h-24 bg-[#f4f9ff] rounded-[1.5rem] mb-5 flex items-center justify-center {{ $hasActiveProducts ? 'group-hover:scale-110 group-hover:-rotate-3' : '' }} transition-transform duration-300 border-4 border-white shadow-inner shrink-0 overflow-hidden p-3">
                    @if($child->image)
                        <img src="{{ Storage::url($child->image) }}" alt="{{ $child->name }}" class="w-full h-full object-contain drop-shadow-sm">
                    @else
                        <span class="text-5xl drop-shadow-md">{{ $child->emote ?? '📦' }}</span>
                    @endif
                </div>

                <span class="text-lg md:text-xl font-black text-[#2b3a67] leading-tight">{{ $child->name }}</span>
                @if($child->description)
                    <p class="mt-2 text-xs font-bold text-[#8faaf3] leading-relaxed line-clamp-2">{{ $child->description }}</p>
                @endif
                <span class="mt-4 inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-[9px] font-black uppercase tracking-widest border border-white shadow-sm {{ $isBrowsable ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#fff5eb] text-amber-500' }}">
                    {{ $hasNestedChildren ? 'Pilih Subkategori' : ($hasActiveProducts ? ($child->active_products_count . ' Layanan') : 'Segera Hadir') }}
                </span>

            @if($isBrowsable)
                </a>
            @else
                </div>
            @endif
        @empty
            <div class="col-span-full text-center py-20 bg-white/90 backdrop-blur-sm rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20">
                <div class="text-6xl mb-4 opacity-50 animate-float">🛠️</div>
                <p class="text-[#5a76c8] font-black text-2xl">Layanan sedang dipersiapkan...</p>
            </div>
        @endforelse
    </div>
</div>
@endsection