@extends('layouts.user')

@section('title', 'Pilih Aplikasi - ' . $category->name)

@section('content')
@php
    $categoryDiscountLabel = \App\Models\Product::currentDiscountLabel();
@endphp
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

    .discount-corner-ribbon {
        pointer-events: none;
        position: absolute;
        right: -1px;
        top: -1px;
        z-index: 20;
        width: 7.75rem;
        height: 7.75rem;
        overflow: hidden;
        border-top-right-radius: 2rem;
    }

    .discount-corner-ribbon__text {
        position: absolute;
        right: -2.75rem;
        top: 1.45rem;
        display: block;
        width: 10.25rem;
        transform: rotate(45deg);
        transform-origin: center;
        background: #5a76c8;
        padding: 0.45rem 0;
        text-align: center;
        font-size: 0.55rem;
        font-weight: 900;
        letter-spacing: 0.14em;
        color: white;
        text-transform: uppercase;
        box-shadow: 0 12px 26px rgba(90, 118, 200, 0.28);
    }
    .wiboost-square-logo-img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .wiboost-logo-frame {
        aspect-ratio: 1 / 1;
        width: 6rem;
        height: 6rem;
        min-width: 6rem;
    }

    @media (max-width: 767px) {
        .wiboost-suborder-logo-content {
            width: 88%;
            height: 88%;
            object-fit: cover;
            border-radius: 1rem;
        }

        .wiboost-suborder-emote {
            font-size: 2.35rem;
            line-height: 1;
        }

        .wiboost-suborder-emote-large {
            font-size: 2.75rem;
            line-height: 1;
        }
    }
</style>

<div class="wiboost-font pb-24 w-full mx-auto mt-4 relative z-10">
    
    <div class="relative z-10 mb-10 flex flex-col items-center gap-6 overflow-visible rounded-[2.5rem] border-4 border-white bg-white/95 p-6 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm md:flex-row md:gap-8 md:p-8">
        <div class="relative z-10 flex w-full items-center justify-between gap-4 md:w-auto md:justify-start">
            <a href="{{ $category->parent ? route('user.order.category', $category->parent->slug) : route('user.dashboard') }}" class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.2rem] border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition-transform hover:scale-110 hover:bg-[#e0fbfc] active:scale-95">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            </a>

            <div class="wiboost-logo-frame flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-inner ring-1 ring-[#bde0fe]/60">
                @if($category->image)
                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="wiboost-square-logo-img wiboost-suborder-logo-content drop-shadow-sm">
                @else
                    <span class="wiboost-suborder-emote text-4xl font-black drop-shadow-sm">{{ $category->emote ?? '📦' }}</span>
                @endif
            </div>
        </div>

        <div class="relative z-10 flex-1 text-center md:text-left">
            <div class="mb-3 inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                Katalog
            </div>
            <h2 class="mb-2 text-3xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm md:text-4xl">{{ $category->name }}</h2>
            @if($category->description)
                <p class="inline-block w-full rounded-2xl border-2 border-white bg-[#f8faff] p-4 text-left text-sm font-bold leading-relaxed text-[#4a5f96] shadow-inner md:w-auto">
                    {!! nl2br(e($category->description)) !!}
                </p>
            @else
                <p class="font-bold text-[#8faaf3]">Pilih kategori di bawah ini untuk lanjut.</p>
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
                <a href="{{ route('user.order.category', $child->slug) }}" class="group relative aspect-square overflow-hidden bg-white/95 backdrop-blur-sm p-6 md:p-8 rounded-[2.5rem] border-4 border-white hover:border-[#bde0fe] shadow-xl shadow-[#bde0fe]/30 hover:-translate-y-2 hover:scale-[1.02] transition-all duration-300 text-center flex flex-col items-center justify-center">
            @else
                <div class="group relative aspect-square overflow-hidden bg-white/60 backdrop-blur-sm p-6 md:p-8 rounded-[2.5rem] border-4 border-white shadow-md shadow-[#bde0fe]/10 text-center flex flex-col items-center justify-center opacity-80 grayscale-[20%]">
            @endif

                @if($categoryDiscountLabel)
                    <span class="discount-corner-ribbon">
                        <span class="discount-corner-ribbon__text">
                            {{ $categoryDiscountLabel }}
                        </span>
                    </span>
                @endif

                <div class="wiboost-logo-frame h-24 w-24 bg-[#f4f9ff] rounded-[1.5rem] mb-5 flex items-center justify-center {{ $hasActiveProducts ? 'group-hover:scale-110 group-hover:-rotate-3' : '' }} transition-transform duration-300 border-4 border-white shadow-inner ring-1 ring-[#bde0fe]/60 shrink-0 overflow-hidden">
                    @if($child->image)
                        <img src="{{ Storage::url($child->image) }}" alt="{{ $child->name }}" class="wiboost-square-logo-img wiboost-suborder-logo-content drop-shadow-sm">
                    @else
                        <span class="wiboost-suborder-emote-large text-5xl drop-shadow-md">{{ $child->emote ?? '📦' }}</span>
                    @endif
                </div>

                <span class="text-lg md:text-xl font-black text-[#2b3a67] leading-tight">{{ $child->name }}</span>
                @if($child->description)
                    <p class="mt-2 text-xs font-bold text-[#8faaf3] leading-relaxed line-clamp-2">{{ $child->description }}</p>
                @endif

            @if($isBrowsable)
                </a>
            @else
                </div>
            @endif
        @empty
            <div class="col-span-full text-center py-20 bg-white/90 backdrop-blur-sm rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20">
                <p class="text-[#5a76c8] font-black text-2xl">Layanan sedang dipersiapkan...</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
