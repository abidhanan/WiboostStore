@extends('layouts.user')
@section('title', 'Tutorial ' . $subCategory)
@section('body_class', 'wiboost-user-tutorial-page')

@section('content')
<style>
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .tutorial-accordion > summary { list-style: none; }
    .tutorial-accordion > summary::-webkit-details-marker { display: none; }
    .tutorial-accordion[open] summary .faq-icon { transform: rotate(180deg); }
    .faq-icon { transition: transform 0.25s ease-in-out; }
    .wiboost-square-logo-img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
    .wiboost-logo-frame {
        aspect-ratio: 1 / 1;
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
    @media (min-width: 640px) {
        .wiboost-header-logo-frame {
            flex-basis: 6rem;
            height: 6rem !important;
            min-height: 6rem;
            min-width: 6rem;
            width: 6rem !important;
        }
        .wiboost-order-header-title {
            font-size: clamp(1.75rem, 2.6vw, 2.25rem);
        }
        .wiboost-order-header-description {
            font-size: 0.95rem;
        }
    }
</style>

<div class="wiboost-font pb-24 w-full mx-auto mt-4 relative z-10">
    <div class="relative z-10 mb-10 grid grid-cols-[auto,minmax(0,1fr)] gap-x-4 gap-y-3 overflow-visible rounded-[2.5rem] border-4 border-white bg-white/95 p-5 text-left shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:gap-x-6 sm:p-6 md:gap-x-8 md:p-8">
        <div class="contents">
            <a href="{{ route('user.dashboard') }}" aria-label="Kembali" class="col-start-1 row-start-1 flex h-9 w-9 shrink-0 items-center justify-center self-center justify-self-center rounded-xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition-transform hover:scale-105 hover:bg-[#e0fbfc] active:scale-95">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            </a>

            <div class="wiboost-header-logo-frame col-start-1 row-start-2 flex shrink-0 items-center justify-center overflow-hidden rounded-[1.5rem] bg-[#f4f9ff] text-[#5a76c8] shadow-inner">
                @if($subCategoryImage)
                    <img src="{{ \App\Support\WiboostImage::thumb($subCategoryImage, 192) }}" alt="" class="wiboost-square-logo-img drop-shadow-sm">
                @else
                    <span class="text-4xl font-black drop-shadow-sm">{{ Str::upper(Str::substr($subCategory, 0, 1)) }}</span>
                @endif
            </div>
        </div>

        <div class="contents">
            <div class="col-start-2 row-start-1 inline-block self-center justify-self-start rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                Tutorial
            </div>
            <div class="col-start-2 row-start-2 min-w-0 self-center text-left">
                <h1 data-fit-single-line class="wiboost-order-header-title mb-2 font-black tracking-tight text-[#2b3a67] drop-shadow-sm">{{ $subCategory }}</h1>
                <p class="wiboost-order-header-description font-bold text-[#8faaf3]">Pilih tutorial di bawah ini untuk melihat panduan.</p>
            </div>
        </div>
    </div>

    <div class="mx-auto flex max-w-6xl flex-col gap-4">
        @foreach($tutorials as $tutorial)
            <details class="tutorial-accordion w-full cursor-pointer overflow-hidden rounded-[1.75rem] border-4 border-white bg-white shadow-lg shadow-[#bde0fe]/20">
                <summary class="flex items-start justify-between gap-4 p-6 focus:outline-none md:p-7">
                    <div class="text-left">
                        <h2 class="text-lg font-black leading-tight text-[#2b3a67] md:text-xl">{{ $tutorial->title }}</h2>
                    </div>
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
</div>
@endsection
