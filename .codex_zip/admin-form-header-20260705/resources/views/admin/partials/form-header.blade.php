@php
    $badge = $badge ?? 'Admin';
    $description = $description ?? null;
@endphp

<section class="mb-10 rounded-[2.5rem] border-4 border-white bg-white/95 px-6 py-7 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:rounded-[3rem] sm:px-8 md:px-10 md:py-9">
    <div class="flex items-start gap-4 sm:gap-5">
        <a href="{{ $backUrl }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-md shadow-[#bde0fe]/30 transition hover:bg-[#e0fbfc] active:scale-95 sm:h-12 sm:w-12">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>

        <div class="min-w-0 pt-1">
            <span class="mb-3 inline-flex rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                {{ $badge }}
            </span>
            <h3 class="text-3xl font-black leading-tight tracking-tight text-[#2b3a67] drop-shadow-sm md:text-4xl">
                {{ $title }}
            </h3>
            @if($description)
                <p class="mt-2 max-w-3xl text-sm font-bold leading-relaxed text-[#8faaf3]">
                    {{ $description }}
                </p>
            @endif
        </div>
    </div>
</section>
