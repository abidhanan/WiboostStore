<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $catalogPageTitle = trim($__env->yieldContent('title', 'Katalog'));
        $catalogDocumentTitle = str_contains($catalogPageTitle, 'Wiboost Store') ? $catalogPageTitle : $catalogPageTitle.' - Wiboost Store';
    @endphp
    <title>{{ $catalogDocumentTitle }}</title>
    @include('partials.seo-meta', [
        'title' => $catalogDocumentTitle,
        'description' => trim($__env->yieldContent('meta_description', 'Temukan layanan dan produk digital yang tersedia di Wiboost Store.')),
    ])
    @include('partials.site-icons')
    <link rel="preload" as="image" type="image/webp" href="{{ asset('images/wiboost-logo-nav.png.webp') }}?v=20260613-nav2" fetchpriority="high">
    @include('partials.font-nunito')

    @include('partials.vite-preload')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.vite-fallback')
    @include('partials.ui-performance-tuning')
    @include('partials.microsoft-clarity')
    @include('partials.google-analytics')
    <style>
        html {
            scrollbar-color: #bde0fe #f4f9ff;
            scrollbar-width: thin;
        }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f4f9ff; }
        ::-webkit-scrollbar-thumb {
            background: #bde0fe;
            border: 2px solid #f4f9ff;
            border-radius: 999px;
        }
        ::-webkit-scrollbar-thumb:hover { background: #8faaf3; }

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
    @include('partials.skeuomorphism-ui')
    @include('partials.ios-layout-fixes')
</head>
<body class="wiboost-compact-ui wiboost-skeu wiboost-welcome-page min-h-screen bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white flex flex-col">
    <nav class="fixed inset-x-0 top-0 z-50 px-0 pointer-events-none">
        <div class="w-full max-w-none pointer-events-auto">
            <div class="flex items-center justify-between rounded-none border-0 border-b-4 border-white bg-white/90 px-4 py-3 shadow-xl shadow-[#bde0fe]/50 backdrop-blur-md sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/wiboost-logo-nav.png.webp') }}?v=20260613-nav2" alt="Logo Wiboost Store" width="192" height="192" fetchpriority="high" loading="eager" decoding="async" class="h-12 w-12 shrink-0 rounded-full object-contain drop-shadow-sm transition-transform group-hover:scale-105 sm:h-14 sm:w-14">
                    <p class="text-lg font-black leading-none text-[#2b3a67] drop-shadow-sm sm:text-2xl">Wiboost Store</p>
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

    @include('partials.site-footer')
    @include('partials.hut-ri-decoration')
    @include('partials.tv-safe')
</body>
</html>
