@extends('layouts.user')
@section('title', 'Tutorial ' . $subCategory)
@section('body_class', 'wiboost-user-tutorial-page')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
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
</style>

<div class="wiboost-font pb-24 w-full mx-auto mt-4 relative z-10">
    <div class="relative z-10 mb-10 flex flex-col items-center gap-6 overflow-hidden rounded-[2.5rem] border-4 border-white bg-white/95 p-6 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm md:flex-row md:gap-8 md:p-8">
        <div class="relative z-10 flex w-full items-center justify-between gap-4 md:w-auto md:justify-start">
            <a href="{{ route('user.dashboard') }}" class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.2rem] border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition-transform hover:scale-110 hover:bg-[#e0fbfc] active:scale-95">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            </a>

            <div class="wiboost-logo-frame flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-inner ring-1 ring-[#bde0fe]/60">
                @if($subCategoryImage)
                    <img src="{{ Storage::url($subCategoryImage) }}" alt="{{ $subCategory }}" class="wiboost-square-logo-img drop-shadow-sm">
                @else
                    <span class="text-4xl font-black drop-shadow-sm">{{ Str::upper(Str::substr($subCategory, 0, 1)) }}</span>
                @endif
            </div>
        </div>

        <div class="relative z-10 flex-1 text-center md:text-left">
            <div class="mb-3 inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                Tutorial
            </div>
            <h1 class="mb-2 text-3xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm md:text-4xl">{{ $subCategory }}</h1>
            <p class="font-bold text-[#8faaf3]">Pilih tutorial di bawah ini untuk melihat panduan.</p>
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
