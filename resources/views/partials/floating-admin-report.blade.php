@php
    $adminReportUrl = \App\Support\WiboostAdminContact::reportUrl();
    $adminLabel = config('wiboost.admin_contact.label', 'Admin Wiboost');
    $floatingOffsetClass = $floatingOffsetClass ?? 'bottom-6 md:bottom-8';
@endphp

@if ($adminReportUrl)
    <div x-data="{ open: false }" @keydown.escape.window="open = false" class="fixed right-4 z-[99] sm:right-8 {{ $floatingOffsetClass }}">
        
        <div x-cloak x-show="open" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="translate-y-5 opacity-0 scale-95" 
             x-transition:enter-end="translate-y-0 opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="translate-y-0 opacity-100 scale-100" 
             x-transition:leave-end="translate-y-5 opacity-0 scale-95" 
             @click.outside="open = false" 
             class="mb-4 w-[min(22rem,calc(100vw-2rem))] rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-6 shadow-2xl shadow-[#25D366]/30">
            
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 shrink-0 bg-[#e9fff0] rounded-2xl flex items-center justify-center text-3xl border-2 border-white shadow-inner animate-bounce">
                    💬
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-[#25D366]">Lapor Admin</p>
                    <h3 class="text-xl font-black text-[#2b3a67] leading-tight mt-1">Butuh bantuan?</h3>
                </div>
            </div>
            
            <p class="text-sm font-bold leading-relaxed text-[#8faaf3] bg-[#f4f9ff] p-4 rounded-[1.5rem] border-2 border-white shadow-inner mb-5">
                Jika ada kendala saat order atau halaman error, langsung kirim pesan ke {{ $adminLabel }} via WhatsApp ya! ✨
            </p>
            
            <div class="flex items-center gap-3">
                <a href="{{ $adminReportUrl }}" target="_blank" rel="noopener" class="inline-flex flex-1 items-center justify-center gap-2 rounded-full border-4 border-white bg-[#25D366] py-3.5 text-sm font-black text-white shadow-lg shadow-[#25D366]/30 transition-transform active:scale-95 hover:bg-[#1fb458]">
                    Chat Admin 🚀
                </a>
                <button type="button" @click="open = false" class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] transition-transform active:scale-95 hover:bg-[#ffcccc] shadow-md text-xl font-black">
                    ✕
                </button>
            </div>
        </div>

        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="ml-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] border-4 border-white bg-[#25D366] text-white shadow-xl shadow-[#25D366]/40 transition-transform hover:scale-110 active:scale-95 hover:-rotate-6">
            <svg x-show="!open" x-transition.opacity class="h-8 w-8 drop-shadow-sm" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.52 3.48A11.77 11.77 0 0012.06 0C5.58 0 .3 5.27.3 11.75c0 2.07.54 4.09 1.58 5.87L0 24l6.58-1.83a11.67 11.67 0 005.48 1.4h.01c6.48 0 11.76-5.27 11.76-11.75 0-3.14-1.22-6.09-3.31-8.34zm-8.46 18.1h-.01a9.78 9.78 0 01-4.98-1.36l-.36-.21-3.91 1.09 1.04-3.81-.23-.39a9.75 9.75 0 01-1.49-5.15c0-5.4 4.4-9.8 9.81-9.8 2.62 0 5.08 1.02 6.93 2.87a9.73 9.73 0 012.87 6.93c0 5.4-4.4 9.8-9.81 9.8zm5.37-7.35c-.29-.15-1.7-.84-1.96-.93-.26-.1-.45-.15-.64.14-.19.29-.73.93-.9 1.12-.16.19-.33.22-.62.07-.29-.15-1.22-.45-2.33-1.44-.86-.76-1.44-1.7-1.61-1.99-.17-.29-.02-.45.13-.6.13-.13.29-.33.43-.5.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.5-.07-.14-.64-1.54-.88-2.1-.23-.56-.47-.48-.64-.49h-.55c-.19 0-.5.07-.76.36-.26.29-1 1-1 2.44s1.03 2.83 1.17 3.02c.14.19 2.02 3.08 4.89 4.32.68.29 1.21.46 1.62.59.68.22 1.29.19 1.78.12.54-.08 1.7-.69 1.94-1.35.24-.66.24-1.22.17-1.35-.07-.12-.26-.19-.55-.34z"/>
            </svg>
            <span x-cloak x-show="open" x-transition.opacity class="text-3xl drop-shadow-sm font-black">✕</span>
        </button>
    </div>
@endif