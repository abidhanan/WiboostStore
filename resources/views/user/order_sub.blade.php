@extends('layouts.user')

@section('title', 'Pilih Aplikasi - ' . $category->name)

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-20 max-w-6xl mx-auto mt-4 px-4">
    <div class="flex items-center gap-4 mb-10">
        <a href="{{ $category->parent ? route('user.order.category', $category->parent->slug) : route('user.dashboard') }}" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] hover:bg-[#f0f5ff] transition-colors border-2 border-white shadow-sm shrink-0 hover:-translate-y-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div class="flex-1">
            <h2 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight">{{ $category->name }}</h2>
            @if($category->description)
                <p class="mt-2 text-sm font-bold text-[#8faaf3] max-w-2xl">{{ $category->description }}</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        @forelse($category->children as $child)
            @php
                $hasActiveProducts = (int) ($child->active_products_count ?? 0) > 0;
                $hasNestedChildren = (int) ($child->children_count ?? 0) > 0;
                $isBrowsable = $hasActiveProducts || $hasNestedChildren;
            @endphp

            @if($isBrowsable)
                <a href="{{ route('user.order.category', $child->slug) }}" class="group bg-white p-6 md:p-8 rounded-[2rem] border-4 border-white hover:border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20 hover:-translate-y-2 transition-all text-center flex flex-col items-center justify-center">
            @else
                <div class="group bg-white/80 p-6 md:p-8 rounded-[2rem] border-4 border-white shadow-lg shadow-[#bde0fe]/10 text-center flex flex-col items-center justify-center opacity-85">
            @endif

                <div class="w-20 h-20 bg-gradient-to-br from-[#f0f5ff] to-[#e0ebff] rounded-2xl mb-5 flex items-center justify-center {{ $hasActiveProducts ? 'group-hover:scale-110 group-hover:rotate-3' : '' }} transition-transform border-2 border-white shadow-inner shrink-0 overflow-hidden p-2">
                    @if($child->image)
                        <img src="{{ Storage::url($child->image) }}" alt="{{ $child->name }}" class="w-full h-full object-contain">
                    @else
                        <span class="text-4xl drop-shadow-sm">{{ $child->emote ?? '📦' }}</span>
                    @endif
                </div>

                <span class="text-lg font-black text-[#2b3a67] leading-tight">{{ $child->name }}</span>
                @if($child->description)
                    <p class="mt-2 text-xs font-bold text-[#8faaf3] leading-relaxed line-clamp-3">{{ $child->description }}</p>
                @endif
                <span class="mt-4 inline-flex items-center gap-2 rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-widest {{ $isBrowsable ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#fff5eb] text-amber-500' }}">
                    {{ $hasNestedChildren ? 'Lihat subkategori' : ($hasActiveProducts ? ($child->active_products_count . ' layanan aktif') : 'Segera hadir') }}
                </span>

            @if($isBrowsable)
                </a>
            @else
                </div>
            @endif
        @empty
            <div class="col-span-full text-center py-16 bg-white rounded-[2.5rem] border-4 border-dashed border-[#bde0fe]">
                <div class="text-6xl mb-4 opacity-50">🛠️</div>
                <p class="text-[#5a76c8] font-black text-xl">Layanan sedang dipersiapkan...</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
