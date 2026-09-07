@extends('layouts.user')

@section('title', 'Tutorial')
@section('body_class', 'wiboost-user-tutorials-page')

@section('content')
<style>
    .wiboost-font { font-family: 'Nunito', sans-serif; }
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
    .wiboost-main-category-card {
        aspect-ratio: 1 / 1;
    }
    .wiboost-main-category-logo {
        height: 4.75rem !important;
        margin-bottom: 0.75rem;
        width: 4.75rem !important;
    }
    .wiboost-main-category-title {
        font-size: 0.75rem;
        line-height: 1.15;
    }
    @media (min-width: 640px) {
        .wiboost-main-category-logo {
            height: 6rem !important;
            margin-bottom: 1.25rem;
            width: 6rem !important;
        }
        .wiboost-main-category-title {
            font-size: 1.125rem;
        }
    }
    @media (min-width: 768px) {
        .wiboost-main-category-title {
            font-size: 1.25rem;
        }
    }
</style>

<div class="wiboost-font pb-24">
    <div class="mb-6 text-center md:text-left pl-2">
        <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">Tutorial</div>
        <h1 class="flex items-center justify-center gap-2 text-3xl font-black text-[#2b3a67] tracking-tight md:justify-start">
            <span class="text-[#5a76c8]">@include('partials.wiboost-icon', ['name' => 'tutorial', 'class' => 'h-8 w-8'])</span>
            <span>Pusat Tutorial</span>
        </h1>
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
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
                            @foreach($group['subcategories'] as $sub)
                                <a href="{{ route('user.tutorials.category', ['main' => $group['name'], 'sub' => $sub['name']]) }}" class="tutorial-sub-filter wiboost-main-category-card group relative flex flex-col items-center justify-center overflow-hidden rounded-[1.5rem] border-4 border-white bg-white p-2 text-center shadow-xl shadow-[#bde0fe]/30 transition-all hover:-translate-y-2 hover:border-[#bde0fe] active:scale-95 sm:rounded-[2rem] sm:p-6 md:p-8">
                                    <span class="wiboost-logo-frame wiboost-main-category-logo flex shrink-0 items-center justify-center overflow-hidden rounded-[1.25rem] bg-[#f4f9ff] shadow-inner transition-transform duration-300 group-hover:scale-110 sm:rounded-[1.5rem]">
                                        @if($sub['image'])
                                            <img src="{{ Storage::url($sub['image']) }}" alt="{{ $sub['name'] }}" class="wiboost-square-logo-img">
                                        @else
                                            <span class="text-2xl font-black text-[#5a76c8]">{{ Str::upper(Str::substr($sub['name'], 0, 1)) }}</span>
                                        @endif
                                    </span>
                                    <span class="wiboost-main-category-title max-w-full break-words px-1 font-black text-[#2b3a67]">{{ $sub['name'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if(collect($group['tutorials'] ?? [])->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($group['tutorials'] as $tutorial)
                                <details class="tutorial-accordion w-full cursor-pointer overflow-hidden rounded-[1.75rem] border-4 border-white bg-white shadow-lg shadow-[#bde0fe]/20">
                                    <summary class="flex items-start justify-between gap-4 p-6 text-left focus:outline-none md:p-7">
                                        <h2 class="text-lg font-black leading-tight text-[#2b3a67] md:text-xl">{{ $tutorial->title }}</h2>
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
</div>

<script>
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
