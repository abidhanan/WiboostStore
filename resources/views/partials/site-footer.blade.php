@php
    $footerClass = trim((string) ($class ?? ''));
@endphp

<footer class="wiboost-site-footer footer-top-skeu relative mt-auto overflow-hidden bg-white pt-12 pb-8 {{ $footerClass }}">
    <div class="relative z-10 mx-auto max-w-6xl px-6 sm:px-8">
        <div class="mb-10 flex flex-col items-center gap-6 text-center md:flex-row md:justify-between md:gap-8 md:text-left">
            <div class="min-w-0 text-center md:text-left">
                <div class="mb-3 flex items-center justify-center gap-2 md:justify-start">
                    <img src="{{ asset('images/wiboost-logo-nav.png.webp') }}?v=20260613-nav2" alt="Logo Wiboost Store" width="192" height="192" loading="lazy" decoding="async" class="h-10 w-10 shrink-0 object-contain drop-shadow-sm md:h-12 md:w-12">
                    <span class="whitespace-nowrap text-xl font-black text-[#2b3a67] md:text-2xl">Wiboost Store</span>
                </div>
                <p class="footer-description mx-auto font-bold text-[#8faaf3] md:mx-0"><span class="md:block md:whitespace-nowrap">Platform penyedia layanan suntik sosmed, top up game,</span> <span class="md:block md:whitespace-nowrap">paket data operator, dan aplikasi premium dengan proses cepat, aman, dan praktis.</span></p>
            </div>

            <div class="flex shrink-0 flex-col items-center gap-3 md:items-end">
                <p class="mb-1 text-sm font-black text-[#2b3a67]">Ikuti Kami</p>
                <div class="flex items-center gap-2 sm:gap-4">
                    <a href="https://www.instagram.com/wiboost.store?igsh=MXJzdWM3ZzBjdnNnZQ==" target="_blank" rel="noopener noreferrer" aria-label="Instagram Wiboost Store" class="footer-social flex h-10 w-10 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#e1306c] shadow-sm transition-all hover:bg-[#5a76c8] hover:text-white sm:h-12 sm:w-12 md:h-14 md:w-14">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="https://www.tiktok.com/@wiboostore?_r=1&_t=ZS-98IS1s3zWa7" target="_blank" rel="noopener noreferrer" aria-label="TikTok Wiboost Store" class="footer-social flex h-10 w-10 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#2b3a67] shadow-sm transition-all hover:bg-[#5a76c8] hover:text-white sm:h-12 sm:w-12 md:h-14 md:w-14">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>
                    </a>
                    <a href="https://youtube.com/@wiboostore?si=rDBRNJjvs5HSZYwC" target="_blank" rel="noopener noreferrer" aria-label="YouTube Wiboost Store" class="footer-social flex h-10 w-10 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#FF0000] shadow-sm transition-all hover:bg-[#5a76c8] hover:text-white sm:h-12 sm:w-12 md:h-14 md:w-14">
                        <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-skeu-divider pt-8 text-center">
            <div class="footer-legal-links mb-5 flex w-full flex-nowrap items-center justify-between gap-0.5 px-0 sm:justify-center sm:gap-2 sm:px-2">
                <a href="{{ route('legal.show', 'terms') }}" class="footer-legal-link flex min-w-0 flex-1 items-center justify-center whitespace-nowrap rounded-full px-0.5 py-1 text-center text-[6px] font-black text-[#5a76c8] transition min-[380px]:text-[7px] sm:flex-none sm:px-3 sm:py-1.5 sm:text-[10px] md:px-3 md:text-xs">Syarat & Ketentuan</a>
                <a href="{{ route('legal.show', 'privacy-policy') }}" class="footer-legal-link flex min-w-0 flex-1 items-center justify-center whitespace-nowrap rounded-full px-0.5 py-1 text-center text-[6px] font-black text-[#5a76c8] transition min-[380px]:text-[7px] sm:flex-none sm:px-3 sm:py-1.5 sm:text-[10px] md:px-3 md:text-xs">Privasi</a>
                <a href="{{ route('legal.show', 'refund-policy') }}" class="footer-legal-link flex min-w-0 flex-1 items-center justify-center whitespace-nowrap rounded-full px-0.5 py-1 text-center text-[6px] font-black text-[#5a76c8] transition min-[380px]:text-[7px] sm:flex-none sm:px-3 sm:py-1.5 sm:text-[10px] md:px-3 md:text-xs">Refund</a>
                <a href="{{ route('legal.show', 'contact') }}" class="footer-legal-link flex min-w-0 flex-1 items-center justify-center whitespace-nowrap rounded-full px-0.5 py-1 text-center text-[6px] font-black text-[#5a76c8] transition min-[380px]:text-[7px] sm:flex-none sm:px-3 sm:py-1.5 sm:text-[10px] md:px-3 md:text-xs">Kontak Admin</a>
            </div>
            <p class="text-xs font-black text-[#a3bbfb] md:text-sm lg:text-base">&copy; {{ date('Y') }} Wiboost Store - Dibuat oleh <a href="https://www.abidhanan.my.id/" target="_blank" rel="noopener noreferrer" class="text-[#5a76c8] transition hover:text-[#4bc6b9]">Abid Hanan Wicaksono</a>.</p>
        </div>
    </div>
</footer>
