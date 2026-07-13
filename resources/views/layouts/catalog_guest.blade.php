<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.site-icons')
    <title>@yield('title') - Wiboost Store</title>
    <link rel="preload" as="image" href="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" fetchpriority="high">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.vite-fallback')
    @include('partials.ui-performance-tuning')
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
        body.wiboost-welcome-page > nav,
        body.wiboost-welcome-page > nav > div {
            width: 100% !important;
            max-width: none !important;
        }
        body.wiboost-welcome-page > nav > div > div {
            border-radius: 0 !important;
        }

        @media (min-width: 1024px) {
            main,
            footer > div {
                max-width: min(1600px, calc(100vw - 8rem)) !important;
            }

            main {
                padding-left: 2rem !important;
                padding-right: 2rem !important;
            }
        }
    </style>
    @include('partials.ios-layout-fixes')
</head>
<body class="wiboost-compact-ui wiboost-welcome-page min-h-screen bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white flex flex-col">
    <nav class="fixed inset-x-0 top-0 z-50 px-0 pointer-events-none">
        <div class="w-full max-w-none pointer-events-auto">
            <div class="flex items-center justify-between rounded-none border-0 border-b-4 border-white bg-white/90 px-4 py-3 shadow-xl shadow-[#bde0fe]/50 backdrop-blur-md sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" onerror="this.onerror=null;this.src='{{ asset('images/wiboost-logo.png') }}?v=20260426-full';" alt="Logo Wiboost Store" width="192" height="192" fetchpriority="high" loading="eager" decoding="async" class="h-12 w-12 shrink-0 rounded-full object-contain drop-shadow-sm transition-transform group-hover:scale-105 sm:h-14 sm:w-14">
                    <p class="text-lg font-black leading-none text-[#2b3a67] drop-shadow-sm sm:text-2xl">Wiboost <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]">Store</span></p>
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('register') }}" class="hidden rounded-full border-2 border-white bg-[#f0f5ff] px-6 py-2.5 text-sm font-extrabold text-[#5a76c8] shadow-md shadow-[#bde0fe]/30 transition-transform hover:-translate-y-0.5 hover:bg-white active:scale-95 sm:block">Daftar</a>
                    <a href="{{ route('login') }}" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white text-sm font-extrabold px-7 py-2.5 rounded-full transition-transform active:scale-95 shadow-lg shadow-[#8faaf3]/40 border-2 border-white">Masuk</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="h-24 sm:h-28"></div>

    <main class="relative z-10 mx-auto w-full max-w-7xl flex-1 px-4 pb-20 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <footer class="bg-white pt-12 pb-8 relative overflow-hidden mt-auto">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 relative z-10">
            <div class="flex flex-col items-center gap-6 text-center md:flex-row md:justify-between md:gap-8 md:text-left mb-10">
                <div class="min-w-0 text-center md:text-left">
                    <div class="flex items-center justify-center gap-2 mb-3 md:justify-start">
                        <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" onerror="this.onerror=null;this.src='{{ asset('images/wiboost-logo.png') }}?v=20260426-full';" alt="Logo Wiboost Store" width="192" height="192" loading="lazy" decoding="async" class="h-10 w-10 shrink-0 object-contain drop-shadow-sm md:h-12 md:w-12">
                        <span class="whitespace-nowrap font-black text-lg text-[#2b3a67] sm:text-xl md:text-2xl">Wiboost <span class="text-[#5a76c8]">Store</span></span>
                    </div>
                    <p class="mx-auto max-w-xs text-[10px] font-bold text-[#8faaf3] sm:text-xs md:mx-0 md:min-w-max md:max-w-none md:whitespace-nowrap md:text-sm">Pusat Layanan dan Produk Digital dalam Satu Platform.</p>
                </div>

                <div class="flex shrink-0 flex-col items-center gap-3 md:items-end">
                    <p class="mb-1 text-xs font-black text-[#2b3a67] md:text-sm">Ikuti Kami</p>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <a href="https://instagram.com/ahawi_channel" target="_blank" class="w-10 h-10 sm:w-12 sm:h-12 bg-[#f4f9ff] text-[#e1306c] rounded-2xl flex items-center justify-center border-2 border-white shadow-sm hover:bg-[#5a76c8] hover:text-white transition-all md:h-14 md:w-14">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="https://tiktok.com/@ahawi_channel" target="_blank" class="w-10 h-10 sm:w-12 sm:h-12 bg-[#f4f9ff] text-[#2b3a67] rounded-2xl flex items-center justify-center border-2 border-white shadow-sm hover:bg-[#5a76c8] hover:text-white transition-all md:h-14 md:w-14">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>
                        </a>
                        <a href="https://youtube.com/@ahawi_channel" target="_blank" class="w-10 h-10 sm:w-12 sm:h-12 bg-[#f4f9ff] text-[#FF0000] rounded-2xl flex items-center justify-center border-2 border-white shadow-sm hover:bg-[#5a76c8] hover:text-white transition-all md:h-14 md:w-14">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t-2 border-[#f0f5ff] pt-8 text-center">
                <div class="mb-5 flex w-full flex-nowrap items-center justify-between gap-0.5 px-0 sm:justify-center sm:gap-2 sm:px-2">
                    <a href="{{ route('legal.show', 'terms') }}" class="flex min-w-0 flex-1 items-center justify-center rounded-full bg-[#f4f9ff] px-0.5 py-1 text-center text-[6px] font-black text-[#5a76c8] transition hover:bg-[#e0fbfc] min-[380px]:text-[7px] sm:flex-none sm:px-3 sm:py-1.5 sm:text-[10px] md:px-3 md:text-xs whitespace-nowrap">Syarat & Ketentuan</a>
                    <a href="{{ route('legal.show', 'privacy-policy') }}" class="flex min-w-0 flex-1 items-center justify-center rounded-full bg-[#f4f9ff] px-0.5 py-1 text-center text-[6px] font-black text-[#5a76c8] transition hover:bg-[#e0fbfc] min-[380px]:text-[7px] sm:flex-none sm:px-3 sm:py-1.5 sm:text-[10px] md:px-3 md:text-xs whitespace-nowrap">Privasi</a>
                    <a href="{{ route('legal.show', 'refund-policy') }}" class="flex min-w-0 flex-1 items-center justify-center rounded-full bg-[#f4f9ff] px-0.5 py-1 text-center text-[6px] font-black text-[#5a76c8] transition hover:bg-[#e0fbfc] min-[380px]:text-[7px] sm:flex-none sm:px-3 sm:py-1.5 sm:text-[10px] md:px-3 md:text-xs whitespace-nowrap">Refund</a>
                    <a href="{{ route('legal.show', 'contact') }}" class="flex min-w-0 flex-1 items-center justify-center rounded-full bg-[#f4f9ff] px-0.5 py-1 text-center text-[6px] font-black text-[#5a76c8] transition hover:bg-[#e0fbfc] min-[380px]:text-[7px] sm:flex-none sm:px-3 sm:py-1.5 sm:text-[10px] md:px-3 md:text-xs whitespace-nowrap">Kontak Admin</a>
                </div>
                <p class="text-[10px] font-black text-[#a3bbfb] md:text-sm lg:text-base">&copy; {{ date('Y') }} Wiboost Store - Dibuat oleh <a href="https://www.abidhanan.my.id/" target="_blank" rel="noopener noreferrer" style="color: #5a76c8;" onmouseover="this.style.color='#4bc6b9'" onmouseout="this.style.color='#5a76c8'" class="transition">Abid Hanan Wicaksono</a>.</p>
            </div>
        </div>
    </footer>
</body>
</html>
