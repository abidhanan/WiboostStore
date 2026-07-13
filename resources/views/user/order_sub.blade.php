@extends(($isGuestCatalog ?? false) ? 'layouts.catalog_guest' : 'layouts.user')

@section('title', 'Pilih Aplikasi - ' . $category->name)

@section('content')
@php
    $isGuestCatalog = $isGuestCatalog ?? false;
    $categoryRouteName = $isGuestCatalog ? 'guest.order.category' : 'user.order.category';
    $categoryHomeUrl = $isGuestCatalog ? route('home') . '#kategori' : route('user.dashboard');
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
        border-radius: 1rem;
    }

    .wiboost-header-logo-img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        border-radius: 1rem;
    }

    .wiboost-suborder-logo-content {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        border-radius: 1rem;
    }

    .wiboost-header-logo-frame {
        aspect-ratio: 1 / 1;
        flex: 0 0 4.25rem;
        height: 4.25rem !important;
        min-height: 4.25rem;
        min-width: 4.25rem;
        width: 4.25rem !important;
    }

    .wiboost-order-header-title {
        font-size: clamp(1.05rem, 5.5vw, 1.55rem);
        line-height: 1.08;
        max-width: 100%;
        overflow: visible;
        text-overflow: clip;
        white-space: nowrap;
    }

    .wiboost-order-header-description {
        font-size: clamp(0.78rem, 3.25vw, 0.9rem);
        line-height: 1.45;
    }

    .wiboost-category-card {
        aspect-ratio: 1 / 1;
        min-height: auto;
    }

    .wiboost-category-logo {
        height: 4.75rem !important;
        margin-bottom: 0.75rem;
        width: 4.75rem !important;
    }

    @media (min-width: 640px) {
        .wiboost-header-logo-frame {
            flex-basis: 6rem;
            height: 6rem !important;
            min-height: 6rem;
            min-width: 6rem;
            width: 6rem !important;
        }

        .wiboost-category-card {
            aspect-ratio: 1 / 1;
            min-height: 0;
        }

        .wiboost-category-logo {
            height: 6rem !important;
            margin-bottom: 1.25rem;
            width: 6rem !important;
        }

        .wiboost-order-header-title {
            font-size: clamp(1.75rem, 2.6vw, 2.25rem);
        }

        .wiboost-order-header-description {
            font-size: 0.95rem;
        }
    }

    @media (max-width: 767px) {
        .wiboost-suborder-logo-content {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
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

<div class="wiboost-font pb-24 w-full mx-auto mt-0 relative z-10">
    
    <div class="relative z-10 mb-10 grid grid-cols-[auto,minmax(0,1fr)] gap-x-4 gap-y-3 overflow-visible rounded-[2.5rem] border-4 border-white bg-white/95 p-5 text-left shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:gap-x-6 sm:p-6 md:gap-x-8 md:p-8">
        <div class="contents">
            <a href="{{ $category->parent ? route($categoryRouteName, $category->parent->slug) : $categoryHomeUrl }}" aria-label="Kembali" class="col-start-1 row-start-1 flex h-9 w-9 shrink-0 items-center justify-center self-center justify-self-center rounded-xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition-transform hover:scale-105 hover:bg-[#e0fbfc] active:scale-95">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div class="wiboost-header-logo-frame col-start-1 row-start-2 flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-[1.5rem] bg-[#f4f9ff] text-[#5a76c8] shadow-inner sm:h-24 sm:w-24">
                @if($category->image)
                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="wiboost-header-logo-img drop-shadow-sm">
                @else
                    <span class="wiboost-suborder-emote drop-shadow-sm">@include('partials.wiboost-icon', ['label' => $category->name, 'class' => 'h-10 w-10'])</span>
                @endif
            </div>
        </div>

        <div class="contents">
            <div class="col-start-2 row-start-1 inline-block self-center justify-self-start rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                Katalog
            </div>
            <div class="col-start-2 row-start-2 min-w-0 self-center text-left">
            <h2 class="wiboost-order-header-title mb-2 font-black tracking-tight text-[#2b3a67] drop-shadow-sm">{{ $category->name }}</h2>
            @if($category->description)
                <p class="wiboost-order-header-description inline-block w-full rounded-2xl border-2 border-white bg-[#f8faff] p-3 text-left font-bold text-[#4a5f96] shadow-inner md:w-auto md:p-4">
                    {!! nl2br(e($category->description)) !!}
                </p>
            @else
                <p class="wiboost-order-header-description font-bold text-[#8faaf3]">Pilih kategori di bawah ini untuk lanjut.</p>
            @endif
            </div>
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
                <a href="{{ route($categoryRouteName, $child->slug) }}" class="wiboost-category-card group relative overflow-hidden bg-white/95 backdrop-blur-sm p-2 sm:p-6 md:p-8 rounded-[1.5rem] sm:rounded-[2.5rem] border-4 border-white hover:border-[#bde0fe] shadow-xl shadow-[#bde0fe]/30 hover:-translate-y-2 hover:scale-[1.02] transition-all duration-300 text-center flex flex-col items-center justify-center">
            @else
                <div class="wiboost-category-card group relative overflow-hidden bg-white/60 backdrop-blur-sm p-2 sm:p-6 md:p-8 rounded-[1.5rem] sm:rounded-[2.5rem] border-4 border-white shadow-md shadow-[#bde0fe]/10 text-center flex flex-col items-center justify-center opacity-80 grayscale-[20%]">
            @endif

                @if($categoryDiscountLabel)
                    <span class="discount-corner-ribbon">
                        <span class="discount-corner-ribbon__text">
                            {{ $categoryDiscountLabel }}
                        </span>
                    </span>
                @endif

                <div class="wiboost-category-logo bg-[#f4f9ff] rounded-[1.25rem] sm:rounded-[1.5rem] flex items-center justify-center {{ $hasActiveProducts ? 'group-hover:scale-110 group-hover:-rotate-3' : '' }} transition-transform duration-300 shadow-inner shrink-0 overflow-hidden">
                    @if($child->image)
                        <img src="{{ Storage::url($child->image) }}" alt="{{ $child->name }}" class="wiboost-square-logo-img wiboost-suborder-logo-content drop-shadow-sm">
                    @else
                        <span class="wiboost-suborder-emote-large drop-shadow-md">@include('partials.wiboost-icon', ['label' => $child->name, 'class' => 'h-12 w-12'])</span>
                    @endif
                </div>

                <span class="max-w-full break-words px-1 text-xs font-black leading-tight text-[#2b3a67] sm:text-lg md:text-xl">{{ $child->name }}</span>

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
