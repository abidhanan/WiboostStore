@php
    $badge = $badge ?? 'Admin';
    $description = $description ?? null;
@endphp

<section class="relative z-10 mb-10 overflow-visible rounded-[2.5rem] border-4 border-white bg-white/95 p-5 text-left shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:p-6 md:p-8">
    <div class="relative z-10 min-w-0 text-left">
        <div class="mb-3 flex items-center justify-start gap-2">
            <a href="{{ $backUrl }}" data-wiboost-back aria-label="Kembali" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition-transform hover:scale-105 hover:bg-[#e0fbfc] active:scale-95">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
            </a>

            <span class="inline-flex rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                {{ $badge }}
            </span>
        </div>

        <h2 class="text-3xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm md:text-4xl">
            {{ $title }}
        </h2>
        @if($description)
            <p class="mt-2 text-sm font-bold leading-relaxed text-[#8faaf3] md:text-base">
                {{ $description }}
            </p>
        @endif
    </div>
</section>
