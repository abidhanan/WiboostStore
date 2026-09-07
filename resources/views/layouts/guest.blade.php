@php
    $pageTitle = trim($__env->yieldContent('title', config('app.name', 'Wiboost Store')));
    $isCompactAuth = request()->routeIs('login', 'register', 'admin.login', 'password.confirm', 'password.request', 'password.reset', 'verification.notice');
    $isPublicMarketingForm = request()->routeIs('public.forms.*') || request()->is('formulir*');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    @include('partials.site-icons')
    <link rel="preload" as="image" href="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" fetchpriority="high">

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
        body {
            font-family: 'Nunito', sans-serif;
        }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f4f9ff; }
        ::-webkit-scrollbar-thumb {
            background: #bde0fe;
            border: 2px solid #f4f9ff;
            border-radius: 999px;
        }
        ::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
        .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
    </style>
    @if($isPublicMarketingForm)
        <style>
            .wiboost-public-guest-shell {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                overflow-x: hidden;
                padding-left: 0 !important;
                padding-right: 0 !important;
                background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%);
            }

            .wiboost-public-guest-nav {
                position: fixed;
                left: 0;
                right: 0;
                top: 0;
                z-index: 50;
                width: 100%;
                max-width: none;
                padding: 0;
                pointer-events: none;
            }

            .wiboost-public-guest-nav-bar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                width: 100%;
                max-width: none;
                border-bottom: 4px solid #fff;
                background: rgba(255, 255, 255, .92);
                padding: .75rem 1rem;
                box-shadow: 0 14px 36px rgba(189, 224, 254, .45);
                backdrop-filter: blur(12px);
                pointer-events: auto;
            }

            .wiboost-public-guest-brand {
                display: inline-flex;
                align-items: center;
                gap: .75rem;
                color: #2b3a67;
                text-decoration: none;
            }

            .wiboost-public-guest-logo {
                width: 3rem;
                height: 3rem;
                flex-shrink: 0;
                border-radius: 9999px;
                object-fit: contain;
                filter: drop-shadow(0 4px 8px rgba(43, 58, 103, .12));
            }

            .wiboost-public-guest-brand-text {
                margin: 0;
                color: #2b3a67;
                font-size: 1.125rem;
                font-weight: 900;
                line-height: 1;
            }

            .wiboost-public-guest-brand-accent {
                color: #5a76c8;
            }

            .wiboost-public-guest-actions {
                display: flex;
                align-items: center;
                gap: .75rem;
                flex-shrink: 0;
            }

            .wiboost-public-guest-spacer {
                height: 6rem;
                flex-shrink: 0;
            }

            .wiboost-public-guest-register {
                display: none;
                border: 2px solid #fff;
                border-radius: 9999px;
                background: #f0f5ff;
                padding: .7rem 1.5rem;
                color: #5a76c8;
                font-size: .875rem;
                font-weight: 800;
                line-height: 1;
                text-decoration: none;
                box-shadow: 0 10px 22px rgba(189, 224, 254, .3);
                transition: background-color .18s ease, transform .18s ease;
            }

            .wiboost-public-guest-register:hover {
                background: #fff;
                transform: translateY(-2px);
            }

            .wiboost-public-guest-login {
                display: inline-flex;
                flex-shrink: 0;
                align-items: center;
                justify-content: center;
                border: 2px solid #fff;
                border-radius: 9999px;
                background: #5a76c8;
                padding: .7rem 1.75rem;
                color: #fff;
                font-size: .875rem;
                font-weight: 800;
                line-height: 1;
                text-decoration: none;
                box-shadow: 0 10px 22px rgba(143, 170, 243, .4);
                transition: background-color .18s ease, transform .18s ease;
            }

            .wiboost-public-guest-login:hover {
                background: #4760a9;
            }

            .wiboost-public-guest-login:active {
                transform: scale(.96);
            }

            .wiboost-public-guest-main {
                position: relative;
                z-index: 10;
                display: flex;
                width: 100%;
                max-width: 56rem;
                flex: 1;
                align-items: flex-start;
                justify-content: center;
                box-sizing: border-box;
                margin: 0 auto;
                padding: 2rem 1rem;
            }

            .wiboost-public-guest-card {
                box-sizing: border-box;
                width: 100%;
                border: 4px solid #fff;
                border-radius: 2.5rem;
                background: rgba(255, 255, 255, .95);
                padding: 1.25rem;
                box-shadow: 0 28px 70px rgba(189, 224, 254, .5);
                backdrop-filter: blur(10px);
            }

            .wiboost-public-guest-footer {
                position: relative;
                z-index: 10;
                margin-top: auto;
                background: #fff;
                padding: 3rem 1rem 2rem;
            }

            .wiboost-public-guest-footer > div {
                max-width: 72rem;
                margin-left: auto;
                margin-right: auto;
            }

            .wiboost-public-guest-footer img {
                width: 3rem;
                height: 3rem;
                border-radius: 9999px;
                object-fit: contain;
            }

            .wiboost-public-guest-footer a {
                text-decoration: none;
            }

            .wiboost-public-guest-footer svg {
                width: 1.35rem;
                height: 1.35rem;
            }

            @media (min-width: 640px) {
                .wiboost-public-guest-nav-bar {
                    padding-left: 1.5rem;
                    padding-right: 1.5rem;
                }

                .wiboost-public-guest-logo {
                    width: 3.5rem;
                    height: 3.5rem;
                }

                .wiboost-public-guest-brand-text {
                    font-size: 1.5rem;
                }

                .wiboost-public-guest-register {
                    display: inline-flex;
                }

                .wiboost-public-guest-spacer {
                    height: 7rem;
                }

                .wiboost-public-guest-main {
                    padding-top: 2.5rem;
                    padding-bottom: 2.5rem;
                    padding-left: 1.5rem;
                    padding-right: 1.5rem;
                }

                .wiboost-public-guest-card {
                    padding: 1.75rem;
                }
            }

            @media (min-width: 768px) {
                .wiboost-public-guest-card {
                    padding: 2.25rem;
                }
            }

            @media (min-width: 1024px) {
                .wiboost-public-guest-nav-bar {
                    padding-left: 2rem;
                    padding-right: 2rem;
                }
            }
        </style>
    @endif
    @include('partials.ios-layout-fixes')
</head>
<body class="wiboost-compact-ui min-h-screen bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white">
    <div class="relative bg-wiboost-sky px-4 sm:px-6 lg:px-8 {{ $isPublicMarketingForm ? 'wiboost-public-guest-shell min-h-screen overflow-x-hidden pt-0 pb-0 flex flex-col' : 'overflow-hidden' }} {{ $isCompactAuth ? 'min-h-screen py-4 flex flex-col' : ($isPublicMarketingForm ? '' : 'min-h-screen py-5') }}">
        
        @unless($isCompactAuth)
            <nav class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-nav fixed inset-x-0 top-0 z-50 px-0 pointer-events-none' : 'relative z-20 -mx-4 mt-0 w-auto sm:-mx-6 lg:-mx-8' }}">
                <div class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-nav-bar' : '' }} flex items-center justify-between rounded-none border-0 border-b-4 border-white bg-white/90 px-4 py-3 shadow-xl shadow-[#bde0fe]/50 backdrop-blur-md sm:px-6 lg:px-8">
                    <a href="{{ Auth::check() ? (Auth::user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard')) : route('home') }}" class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-brand' : '' }} flex items-center gap-3 group">
                        <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" alt="Logo Wiboost Store" width="192" height="192" fetchpriority="high" loading="eager" decoding="async" class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-logo' : '' }} h-12 w-12 shrink-0 rounded-full object-contain drop-shadow-sm transition-transform group-hover:scale-105 sm:h-14 sm:w-14">
                        <p class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-brand-text' : '' }} text-lg font-black leading-none text-[#2b3a67] drop-shadow-sm sm:text-2xl">Wiboost <span class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-brand-accent' : 'text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]' }}">Store</span></p>
                    </a>

                    @if($isPublicMarketingForm)
                        <div class="wiboost-public-guest-actions">
                            <a href="{{ route('register') }}" class="wiboost-public-guest-register hidden rounded-full border-2 border-white bg-[#f0f5ff] px-6 py-2.5 text-sm font-extrabold text-[#5a76c8] shadow-md shadow-[#bde0fe]/30 transition-transform hover:-translate-y-0.5 hover:bg-white active:scale-95 sm:block">Daftar</a>
                            <a href="{{ route('login') }}" class="wiboost-public-guest-login bg-[#5a76c8] hover:bg-[#4760a9] text-white text-sm font-extrabold px-7 py-2.5 rounded-full transition-transform active:scale-95 shadow-lg shadow-[#8faaf3]/40 border-2 border-white">Masuk</a>
                        </div>
                    @endif
                </div>
            </nav>
        @endunless

        @if($isPublicMarketingForm)
            <div class="wiboost-public-guest-spacer h-24 shrink-0 sm:h-28"></div>
        @endif

        <main class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-main' : '' }} relative z-10 mx-auto flex w-full {{ $isPublicMarketingForm ? 'max-w-4xl flex-1 items-start justify-center py-8 sm:py-10 lg:py-12' : 'max-w-xl items-center justify-center' }} {{ $isCompactAuth ? 'flex-1 py-8 lg:py-10' : ($isPublicMarketingForm ? '' : 'min-h-[calc(100vh-7rem)] py-12 lg:py-16') }}">
            <section class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-card' : '' }} w-full rounded-[2.5rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/50 backdrop-blur {{ $isPublicMarketingForm ? 'p-5 sm:p-7 md:p-9' : 'p-6 md:p-8' }}">
                @auth
                    @if(! \App\Support\WiboostInputRules::validName(auth()->user()?->name))
                        <div class="mb-6 rounded-[1.5rem] border-4 border-white bg-[#fff5eb] px-5 py-4 text-sm font-black text-amber-600 shadow-lg shadow-amber-100/60">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 items-start gap-3">
                                    <span class="mt-0.5 shrink-0 drop-shadow-sm">@include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-6 w-6'])</span>
                                    <span class="min-w-0">{{ \App\Support\WiboostInputRules::invalidNameWarning() }}</span>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="inline-flex shrink-0 items-center justify-center rounded-full border-2 border-white bg-[#5a76c8] px-4 py-2 text-xs font-black text-white shadow-md shadow-[#5a76c8]/25">
                                    Ubah Nama
                                </a>
                            </div>
                        </div>
                    @endif
                @endauth

                @hasSection('content')
                    @yield('content')
                @else
                    @isset($slot)
                        {{ $slot }}
                    @endisset
                @endif
            </section>
        </main>

        @unless($isCompactAuth)
            <footer class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-footer' : '' }} relative z-10 -mx-4 mt-auto bg-white px-4 pt-12 pb-8 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                <div class="max-w-6xl mx-auto px-6 sm:px-8">
                    <div class="flex flex-col items-center gap-6 text-center md:flex-row md:justify-between md:gap-8 md:text-left mb-10">
                        <div class="min-w-0 text-center md:text-left">
                            <div class="flex items-center justify-center gap-2 mb-3 md:justify-start">
                                <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" alt="Logo Wiboost Store" width="192" height="192" loading="lazy" decoding="async" class="h-10 w-10 shrink-0 object-contain drop-shadow-sm md:h-12 md:w-12">
                                <span class="whitespace-nowrap font-black text-lg text-[#2b3a67] sm:text-xl md:text-2xl">Wiboost <span class="text-[#5a76c8]">Store</span></span>
                            </div>
                            <p class="mx-auto max-w-xs text-[10px] font-bold text-[#8faaf3] sm:text-xs md:mx-0 md:min-w-max md:max-w-none md:whitespace-nowrap md:text-sm">Pusat Layanan dan Produk Digital dalam Satu Platform.</p>
                        </div>

                        <div class="flex shrink-0 flex-col items-center gap-3 md:items-end">
                            <p class="mb-1 text-xs font-black text-[#2b3a67] md:text-sm">Ikuti Kami</p>
                            <div class="flex items-center gap-2 sm:gap-4">
                                <a href="https://www.instagram.com/wiboost.store?igsh=MXJzdWM3ZzBjdnNnZQ==" target="_blank" rel="noopener noreferrer" class="w-10 h-10 sm:w-12 sm:h-12 bg-[#f4f9ff] text-[#e1306c] rounded-2xl flex items-center justify-center border-2 border-white shadow-sm hover:bg-[#5a76c8] hover:text-white transition-all md:h-14 md:w-14">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                                <a href="https://www.tiktok.com/@wiboostore?_r=1&_t=ZS-98IS1s3zWa7" target="_blank" rel="noopener noreferrer" class="w-10 h-10 sm:w-12 sm:h-12 bg-[#f4f9ff] text-[#2b3a67] rounded-2xl flex items-center justify-center border-2 border-white shadow-sm hover:bg-[#5a76c8] hover:text-white transition-all md:h-14 md:w-14">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>
                                </a>
                                <a href="https://youtube.com/@wiboostore?si=rDBRNJjvs5HSZYwC" target="_blank" rel="noopener noreferrer" class="w-10 h-10 sm:w-12 sm:h-12 bg-[#f4f9ff] text-[#FF0000] rounded-2xl flex items-center justify-center border-2 border-white shadow-sm hover:bg-[#5a76c8] hover:text-white transition-all md:h-14 md:w-14">
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
        @endunless
    </div>

    @unless($isCompactAuth || $isPublicMarketingForm)
        @include('partials.floating-admin-report')
    @endunless
    @include('partials.wiboost-input-guards')
    @include('partials.hut-ri-decoration')
    @include('partials.tv-safe')
</body>
</html>
