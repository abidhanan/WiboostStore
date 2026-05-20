@extends('layouts.user')

@section('title', 'Dashboard Saya')
@section('body_class', 'wiboost-user-dashboard-page')

@section('content')
@php
    $categoryDiscountLabel = \App\Models\Product::currentDiscountLabel();
@endphp
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    .tutorial-accordion > summary { list-style: none; }
    .tutorial-accordion > summary::-webkit-details-marker { display: none; }
    .tutorial-accordion[open] summary .faq-icon { transform: rotate(180deg); }
    .faq-icon { transition: transform 0.25s ease-in-out; }
    .tut-filter-btn.is-active {
        background: #5a76c8 !important;
        color: #ffffff !important;
        border-color: #ffffff !important;
        box-shadow: 0 8px 18px rgba(90, 118, 200, 0.18) !important;
    }
    .tutorial-sub-filter.is-active {
        border-color: #bde0fe !important;
        background: #f8fbff !important;
        box-shadow: 0 12px 24px rgba(189, 224, 254, 0.32) !important;
        transform: translateY(-2px);
    }
    .discount-corner-ribbon {
        pointer-events: none;
        position: absolute;
        right: -1px;
        top: -1px;
        z-index: 20;
        width: 7rem;
        height: 7rem;
        overflow: hidden;
        border-top-right-radius: 1.55rem;
    }
    .discount-corner-ribbon__text {
        position: absolute;
        right: -2.55rem;
        top: 1.2rem;
        display: block;
        width: 9.75rem;
        transform: rotate(45deg);
        transform-origin: center;
        background: #22c55e;
        padding: 0.42rem 0;
        text-align: center;
        font-size: 0.5rem;
        font-weight: 900;
        letter-spacing: 0.14em;
        color: white;
        text-transform: uppercase;
        box-shadow: 0 12px 26px rgba(34, 197, 94, 0.28);
    }
    .promo-banner-shell {
        width: 100%;
        margin-left: auto;
        margin-right: auto;
    }
    .promo-slide {
        aspect-ratio: 8 / 3;
        height: auto;
        min-height: 0;
        max-height: none;
        padding: 1.5rem !important;
    }
    .promo-slide h2 {
        font-size: clamp(1.65rem, 2.1vw, 2.6rem) !important;
        line-height: 1.08 !important;
        margin-bottom: 0.5rem !important;
    }
    .promo-slide p {
        font-size: clamp(0.85rem, 1vw, 1rem) !important;
        line-height: 1.45 !important;
    }
    @media (max-width: 640px) {
        .promo-banner-shell {
            width: 100%;
        }
        .promo-slide {
            aspect-ratio: 2.25 / 1;
            height: auto;
            min-height: 0;
            max-height: none;
        }
    }
    @media (min-width: 1024px) {
        .promo-slide h2 {
            font-size: clamp(3rem, 4vw, 5.25rem) !important;
            line-height: 0.98 !important;
            margin-bottom: 1rem !important;
        }
        .promo-slide p {
            font-size: clamp(1.2rem, 1.35vw, 1.65rem) !important;
            line-height: 1.35 !important;
        }
        .promo-slide span {
            font-size: 1rem !important;
            letter-spacing: 0.22em !important;
        }
    }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font relative">
    <div class="relative overflow-hidden bg-wiboost-sky rounded-[2.5rem] p-8 md:p-10 mb-8 shadow-xl shadow-[#bde0fe]/50 border-4 border-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        
        <div class="relative z-10">
            <div class="inline-block px-4 py-1 bg-white/80 backdrop-blur-sm text-[#5a76c8] font-black rounded-full mb-3 text-xs border-2 border-white shadow-sm">
                Area Pengguna
            </div>
            <h1 class="text-3xl md:text-5xl font-black text-[#2b3a67] mb-2 drop-shadow-sm">Halo, {{ Auth::user()->name }}! 👋</h1>
        </div>

        <div class="relative z-10 bg-white/90 backdrop-blur-md px-8 py-6 rounded-[2rem] border-4 border-white w-full md:w-auto text-center shadow-lg shadow-[#bde0fe]/30 hover:scale-105 transition-transform duration-300">
            <p class="text-[#8faaf3] text-xs font-black uppercase tracking-widest mb-1">Saldo Anda</p>
            <p class="text-3xl md:text-4xl font-black text-[#5a76c8] tracking-tight mb-4">Rp {{ number_format(Auth::user()->balance, 0, ',', '.') }}</p>
            <a href="{{ route('user.wallet.index') }}" class="inline-flex items-center justify-center gap-2 bg-[#5a76c8] text-white px-6 py-2.5 rounded-full text-sm font-extrabold hover:bg-[#4760a9] hover:-translate-y-1 active:scale-95 transition-all shadow-lg shadow-[#5a76c8]/30 border-2 border-white w-full md:w-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Top Up Saldo
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-10">
        <div class="bg-white p-6 rounded-[2rem] border-4 border-white shadow-xl shadow-[#bde0fe]/40 flex items-center gap-5 hover:-translate-y-1 transition duration-300">
            <div class="w-14 h-14 bg-[#f4f9ff] rounded-2xl flex items-center justify-center text-[#5a76c8] border-2 border-white shadow-inner shrink-0 text-2xl">
                🛍️
            </div>
            <div>
                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Pesanan Bulan Ini</p>
                <p class="text-3xl font-black text-[#2b3a67] leading-none mt-1">{{ $totalThisMonth ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[2rem] border-4 border-white shadow-xl shadow-[#bde0fe]/40 flex items-center gap-5 hover:-translate-y-1 transition duration-300">
            <div class="w-14 h-14 bg-[#e6fff7] rounded-2xl flex items-center justify-center text-emerald-500 border-2 border-white shadow-inner shrink-0 text-2xl">
                💸
            </div>
            <div class="truncate">
                <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Pengeluaran Bulan Ini</p>
                <p class="text-2xl font-black text-[#2b3a67] leading-tight mt-1 truncate">Rp {{ number_format($totalSpent ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="promo-banner-shell mb-10 relative rounded-[2.5rem] overflow-hidden shadow-xl shadow-[#bde0fe]/30 border-4 border-white group bg-white">
        <div id="promo-slider" class="flex overflow-x-auto snap-x snap-mandatory hide-scroll scroll-smooth">
            @forelse($promos as $promo)
                @php
                    $hasPromoText = filled($promo->badge_text) || filled($promo->title) || filled($promo->description);
                @endphp
                <div class="promo-slide snap-center shrink-0 w-full p-8 md:p-12 text-white relative overflow-hidden flex items-center group/slide
                    @if(!$promo->image)
                        @if($promo->theme == 'teal') bg-gradient-to-r from-[#4bc6b9] to-[#3ba398]
                        @elseif($promo->theme == 'orange') bg-gradient-to-r from-[#fbbf24] to-[#d97706]
                        @elseif($promo->theme == 'rose') bg-gradient-to-r from-[#fb7185] to-[#e11d48]
                        @else bg-gradient-to-r from-[#8faaf3] to-[#5a76c8] @endif
                    @else bg-[#2b3a67] @endif">

                    @if(!empty($promo->link))
                        <a href="{{ $promo->link }}" target="_blank" class="absolute inset-0 z-30 cursor-pointer"></a>
                        <div class="absolute top-6 right-6 z-20 bg-white/20 backdrop-blur-md p-3 rounded-full opacity-0 group-hover/slide:opacity-100 transition-opacity duration-300 pointer-events-none border-2 border-white/50">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </div>
                    @endif

                    @if($promo->image)
                        <img src="{{ Storage::url($promo->image) }}" class="absolute inset-0 w-full h-full z-0 {{ $hasPromoText ? 'object-cover opacity-70 group-hover/slide:scale-105' : 'object-cover opacity-100 bg-[#f4f9ff]' }} transition-transform duration-700" alt="{{ $promo->title ?: 'Banner promo Wiboost Store' }}">
                        @if($hasPromoText)
                            <div class="absolute inset-0 bg-gradient-to-r from-black/80 to-transparent z-10"></div>
                        @endif
                    @endif

                    @if($hasPromoText && filled($promo->badge_text))
                        <div class="absolute left-4 top-4 z-40 pointer-events-none md:left-6 md:top-6">
                            <span class="bg-white/90 backdrop-blur-sm text-[#5a76c8] text-xs md:text-sm font-black px-4 py-1.5 rounded-full inline-block shadow-sm uppercase tracking-widest border-2 border-white">{{ $promo->badge_text }}</span>
                        </div>
                    @endif

                    @if($hasPromoText)
                        <div class="relative z-20 pointer-events-none pt-8 md:pt-10">
                            @if(filled($promo->title))
                                <h2 class="text-3xl md:text-6xl font-black mb-3 drop-shadow-md tracking-tight">{{ $promo->title }}</h2>
                            @endif
                            @if(filled($promo->description))
                                <p class="font-bold text-white/90 text-sm md:text-xl max-w-2xl drop-shadow-md">{{ $promo->description }}</p>
                            @endif
                        </div>
                    @endif

                    @if(!$promo->image && filled($promo->emoji))
                        <div class="text-7xl md:text-8xl opacity-50 absolute right-10 transform {{ $loop->iteration % 2 == 0 ? '-rotate-12' : 'rotate-12' }} pointer-events-none group-hover/slide:scale-110 transition-transform duration-500 drop-shadow-xl">
                            {{ $promo->emoji }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="promo-slide snap-center shrink-0 w-full bg-gradient-to-r from-[#8faaf3] to-[#5a76c8] p-8 md:p-12 text-white relative overflow-hidden flex items-center justify-center">
                    <div class="text-center relative z-20">
                        <span class="text-6xl mb-4 block animate-float">🎁</span>
                        <h2 class="text-3xl md:text-5xl font-black mb-3 drop-shadow-md">Belum Ada Promo</h2>
                        <p class="font-bold text-white/90 text-sm md:text-xl drop-shadow-md">Nantikan penawaran menarik dari Wiboost.</p>
                    </div>
                </div>
            @endforelse
        </div>

    </div>

    <div class="text-center md:text-left mb-8 pl-2">
        <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">Katalog Utama</div>
        <h2 class="text-3xl font-black text-[#2b3a67] tracking-tight">Pilih Kategori</h2>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6 mb-14">
        @forelse($categories as $category)
            <a href="{{ route('user.order.category', $category->slug) }}" class="group relative overflow-hidden bg-white p-6 md:p-8 rounded-[2rem] border-4 border-white hover:border-[#bde0fe] shadow-xl shadow-[#bde0fe]/30 hover:-translate-y-2 transition-all text-center flex flex-col items-center justify-center">
                @if($categoryDiscountLabel)
                    <span class="discount-corner-ribbon">
                        <span class="discount-corner-ribbon__text">
                            {{ $categoryDiscountLabel }}
                        </span>
                    </span>
                @endif
                <div class="w-16 h-16 bg-[#f4f9ff] rounded-2xl border-2 border-white shadow-inner flex items-center justify-center text-[#5a76c8] mb-4 group-hover:scale-110 transition-transform duration-300">
                    @if($category->image)
                        <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="h-full w-full object-contain drop-shadow-sm">
                    @else
                    <span class="text-4xl drop-shadow-sm">{{ $category->emote ?? '🛒' }}</span>
                    @endif
                </div>
                <span class="text-sm md:text-base font-black text-[#2b3a67] leading-tight">{{ $category->name }}</span>
            </a>
        @empty
            <div class="col-span-full text-center py-16 bg-white rounded-[2rem] border-4 border-dashed border-[#bde0fe]">
                <p class="text-[#8faaf3] font-black text-xl">Belum ada kategori layanan yang aktif.</p>
            </div>
        @endforelse
    </div>

    <div class="text-center md:text-left mb-6 pl-2">
        <div class="inline-block px-4 py-1 bg-[#fff0f0] text-[#ff6b6b] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">Pusat Bantuan</div>
        <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Pusat Tutorial</h3>
    </div>

    @if(($tutorialGroups ?? collect())->isNotEmpty())
        <div class="flex gap-3 overflow-x-auto hide-scroll mb-8 pb-2">
            @foreach($tutorialGroups as $group)
                <button type="button" onclick="filterTutorialMain(@js($group['name']))" data-main-filter="{{ $group['name'] }}" class="tut-filter-btn {{ $loop->first ? 'is-active' : '' }} px-6 py-2.5 rounded-full font-extrabold text-sm transition-transform active:scale-95 bg-white text-[#5a76c8] hover:bg-[#f0f5ff] shadow-sm shadow-[#bde0fe]/20 border-2 border-transparent shrink-0">
                    {{ $group['name'] }}
                </button>
            @endforeach
        </div>

        <div class="mb-12">
            @foreach($tutorialGroups as $group)
                <div data-main-panel="{{ $group['name'] }}" class="{{ $loop->first ? '' : 'hidden' }}">
                    @if(collect($group['subcategories'] ?? [])->isNotEmpty())
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6 mb-8">
                            @foreach($group['subcategories'] as $sub)
                                <a href="{{ route('user.tutorials.category', ['main' => $group['name'], 'sub' => $sub['name']]) }}" class="tutorial-sub-filter group flex flex-col items-center justify-center rounded-[2rem] border-4 border-white bg-white p-6 text-center shadow-xl shadow-[#bde0fe]/30 transition-all hover:-translate-y-2 hover:border-[#bde0fe] active:scale-95 md:p-8">
                                    <span class="mb-4 flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl border-2 border-white bg-[#f4f9ff] shadow-inner transition-transform duration-300 group-hover:scale-110">
                                        @if($sub['image'])
                                            <img src="{{ Storage::url($sub['image']) }}" alt="{{ $sub['name'] }}" class="h-full w-full object-contain p-2">
                                        @else
                                            <span class="text-2xl font-black text-[#5a76c8]">{{ Str::upper(Str::substr($sub['name'], 0, 1)) }}</span>
                                        @endif
                                    </span>
                                    <span class="text-sm md:text-base font-black text-[#2b3a67] leading-tight">{{ $sub['name'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if(collect($group['tutorials'] ?? [])->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($group['tutorials'] as $tutorial)
                                <details class="tutorial-accordion w-full cursor-pointer overflow-hidden rounded-[1.75rem] border-4 border-white bg-white shadow-lg shadow-[#bde0fe]/20">
                                    <summary class="flex items-start justify-between gap-4 p-6 text-left focus:outline-none md:p-7">
                                        <h4 class="text-lg font-black leading-tight text-[#2b3a67] md:text-xl">{{ $tutorial->title }}</h4>
                                        <span class="mt-1 shrink-0 text-[#5a76c8]">
                                            <svg class="faq-icon h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                                        </span>
                                    </summary>
                                    <div class="border-t-2 border-[#f0f5ff] bg-white px-6 pb-6 pt-5 text-left text-sm font-bold leading-relaxed text-[#4a5f96] md:px-7">
                                        @if($tutorial->description)
                                            <p class="mb-4 rounded-[1.25rem] border-2 border-white bg-[#f8fbff] p-4 text-[#8faaf3] shadow-inner">{{ $tutorial->description }}</p>
                                        @endif
                                        @if($tutorial->content)
                                            <div class="whitespace-pre-wrap rounded-[1.25rem] border-2 border-white bg-[#f8fbff] p-5 text-[#2b3a67] shadow-inner">{{ $tutorial->content }}</div>
                                        @elseif(!$tutorial->youtube_url)
                                            <p class="rounded-[1.25rem] border-2 border-dashed border-[#bde0fe] bg-[#f8fbff] p-5 text-[#8faaf3]">Isi panduan belum tersedia.</p>
                                        @endif
                                        @if($tutorial->youtube_url)
                                            <div class="mt-5 flex justify-center">
                                                <a href="{{ $tutorial->youtube_url }}" target="_blank" class="inline-flex w-full items-center justify-center gap-2 rounded-full border-2 border-white bg-[#ffe5e5] px-6 py-3 font-black text-[#ff6b6b] shadow-sm transition-colors hover:bg-[#ff6b6b] hover:text-white sm:w-auto">
                                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                                    Tonton Video Tutorial
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @elseif(collect($group['subcategories'] ?? [])->isEmpty())
                        <div class="rounded-[2rem] border-4 border-dashed border-[#bde0fe] bg-white px-6 py-10 text-center">
                            <p class="text-[#8faaf3] font-black">Belum ada tutorial di kategori ini.</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="mb-12 text-center py-16 bg-white rounded-[2rem] border-4 border-dashed border-[#bde0fe]">
            <p class="text-[#8faaf3] font-black text-lg">Belum ada tutorial tersedia.</p>
        </div>
    @endif

    @if(false)
    <div class="hidden grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6 mb-12" id="tutorial-grid">
        @forelse($tutorials ?? [] as $tutorial)
            <div class="tutorial-card bg-white p-6 rounded-[2rem] border-4 border-white hover:border-[#bde0fe] shadow-xl shadow-[#bde0fe]/30 hover:-translate-y-2 transition-all flex flex-col h-full group" data-category="{{ $tutorial->category }}">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-14 h-14 bg-[#f4f9ff] rounded-2xl flex items-center justify-center text-3xl border-2 border-white shadow-inner overflow-hidden shrink-0 group-hover:scale-110 transition-transform">
                        @if($tutorial->image)
                            <img src="{{ Storage::url($tutorial->image) }}" class="w-full h-full object-cover">
                        @else
                            {{ $tutorial->icon ?? '📖' }}
                        @endif
                    </div>
                    <span class="bg-[#f0f5ff] text-[#5a76c8] text-[9px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest text-right border border-white shadow-sm">{{ $tutorial->category }}</span>
                </div>

                <h4 class="font-black text-lg text-[#2b3a67] mb-2 leading-tight">{{ $tutorial->title }}</h4>
                <p class="text-xs font-bold text-[#8faaf3] mb-6 flex-1 line-clamp-3">{{ $tutorial->description }}</p>

                <button onclick="openTutorialModal('tut-{{ $tutorial->id }}')" class="w-full bg-[#f4f9ff] hover:bg-[#5a76c8] text-[#5a76c8] hover:text-white font-extrabold text-sm py-3.5 rounded-full transition-colors border-2 border-white shadow-sm flex items-center justify-center gap-2">
                    Lihat Panduan
                </button>
            </div>
        @empty
            <div class="col-span-full text-center py-16 bg-white rounded-[2rem] border-4 border-dashed border-[#bde0fe]">
                <div class="text-6xl mb-3 opacity-50 animate-float">📖</div>
                <p class="text-[#8faaf3] font-black text-lg">Belum ada tutorial tersedia.</p>
            </div>
        @endforelse
    </div>
    @endif
</div>

@if(false)
@foreach($tutorials ?? [] as $tutorial)
    <div id="tut-{{ $tutorial->id }}" class="fixed inset-0 flex items-center justify-center bg-[#2b3a67]/80 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 p-4" style="z-index: 999999;">
        <div class="absolute inset-0 w-full h-full" onclick="closeTutorialModal('tut-{{ $tutorial->id }}')"></div>

        <div class="relative bg-white w-full max-w-2xl rounded-[2.5rem] p-6 md:p-8 shadow-2xl transform scale-95 transition-transform duration-300 flex flex-col overflow-hidden max-h-[90vh] border-4 border-white">
            <div class="flex justify-between items-center mb-6 pb-4 border-b-4 border-dashed border-[#f4f9ff]">
                <h3 class="text-xl md:text-2xl font-black text-[#2b3a67] flex items-center gap-3">
                    @if($tutorial->image)
                        <img src="{{ Storage::url($tutorial->image) }}" class="w-10 h-10 object-cover rounded-xl border-2 border-[#f0f5ff] shadow-sm">
                    @else
                        <span class="text-3xl">{{ $tutorial->icon ?? '📖' }}</span>
                    @endif
                    {{ $tutorial->title }}
                </h3>
                <button onclick="closeTutorialModal('tut-{{ $tutorial->id }}')" class="w-12 h-12 bg-[#ffe5e5] text-[#ff6b6b] hover:bg-[#ff6b6b] hover:text-white rounded-[1.2rem] flex items-center justify-center transition-colors font-black border-2 border-white shadow-sm shrink-0 text-xl">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto hide-scroll pr-2 text-left">
                @if($tutorial->youtube_url)
                    <a href="{{ $tutorial->youtube_url }}" target="_blank" class="w-full bg-[#ffe5e5] hover:bg-[#ff6b6b] text-[#ff6b6b] hover:text-white p-4 rounded-[1.5rem] flex items-center justify-center gap-3 mb-6 font-black transition-transform active:scale-95 border-2 border-white shadow-sm">
                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        Tonton Video di YouTube
                    </a>
                @endif

                @if($tutorial->content)
                    <div class="text-[#2b3a67] font-bold text-sm md:text-base leading-relaxed whitespace-pre-wrap text-left block w-full bg-[#f8fbff] p-5 rounded-[1.5rem] border-2 border-white shadow-inner">
{{ $tutorial->content }}
                    </div>
                @else
                    @if(!$tutorial->youtube_url)
                        <p class="text-center text-[#8faaf3] italic font-bold py-10">Isi panduan belum tersedia.</p>
                    @endif
                @endif
            </div>

            <div class="mt-6 pt-4 border-t-4 border-dashed border-[#f4f9ff] shrink-0">
                <button onclick="closeTutorialModal('tut-{{ $tutorial->id }}')" class="w-full bg-[#f4f9ff] hover:bg-[#5a76c8] text-[#5a76c8] hover:text-white py-4 rounded-full font-black transition-colors border-2 border-white shadow-sm text-base">Tutup Panduan</button>
            </div>
        </div>
    </div>
@endforeach
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const slider = document.getElementById('promo-slider');
        if (slider && slider.children.length > 1) {
            let autoSlide = setInterval(() => slidePromo(1), 5000);

            window.slidePromo = function (direction) {
                const scrollAmount = slider.clientWidth;
                slider.scrollBy({ left: scrollAmount * direction, behavior: 'smooth' });

                if (direction === 1 && slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 10) {
                    slider.scrollTo({ left: 0, behavior: 'smooth' });
                } else if (direction === -1 && slider.scrollLeft <= 10) {
                    slider.scrollTo({ left: slider.scrollWidth, behavior: 'smooth' });
                }

                clearInterval(autoSlide);
                autoSlide = setInterval(() => slidePromo(1), 5000);
            };
        }
    });

    function filterTutorialMain(mainCategory) {
        document.querySelectorAll('[data-main-filter]').forEach(btn => {
            btn.classList.toggle('is-active', btn.dataset.mainFilter === mainCategory);
        });

        document.querySelectorAll('[data-main-panel]').forEach(panel => {
            const isActive = panel.dataset.mainPanel === mainCategory;
            panel.classList.toggle('hidden', !isActive);
        });
    }
</script>
@endsection
