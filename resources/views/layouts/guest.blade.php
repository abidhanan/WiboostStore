@php
    $pageTitle = trim($__env->yieldContent('title', config('app.name', 'Wiboost Store')));
    $isCompactAuth = request()->routeIs('login', 'register', 'admin.login', 'password.confirm', 'password.request', 'password.reset', 'verification.notice');
    $isPublicMarketingForm = request()->routeIs('public.forms.*') || request()->is('formulir*');
    $isPublicFormEntry = request()->routeIs('public.forms.show');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    @include('partials.seo-meta', [
        'title' => $pageTitle,
        'description' => trim($__env->yieldContent('meta_description', 'Akses layanan Wiboost Store dengan aman dan praktis.')),
        'robots' => 'noindex,nofollow,noarchive,nosnippet',
    ])
    @include('partials.site-icons')
    <link rel="preload" as="image" href="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" fetchpriority="high">

    @include('partials.font-nunito')
    @include('partials.vite-preload')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.vite-fallback')
    @include('partials.ui-performance-tuning')
    @include('partials.microsoft-clarity')
    @include('partials.google-analytics')
    @include('partials.responsive-qa-guards')
    @include('partials.skeuomorphism-ui')
    
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
        @if($isCompactAuth)
            .wiboost-auth-card .max-w-md > .mb-8,
            .wiboost-auth-card .max-w-md > .mb-8 h1,
            .wiboost-auth-card .max-w-md > .mb-8 h2,
            .wiboost-auth-card .max-w-md > .mb-8 h3,
            .wiboost-auth-card .max-w-md > .mb-8 p {
                text-align: center !important;
            }

            .wiboost-auth-card .max-w-md > .mb-8 > .inline-block:first-child {
                display: none !important;
            }

            .wiboost-auth-card form,
            .wiboost-auth-card form p,
            .wiboost-auth-card form a {
                text-align: left !important;
            }

            .wiboost-auth-card form > div > .mb-2.flex {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                text-align: left;
            }

            .wiboost-auth-card label[for="remember_me"] {
                justify-content: flex-start;
                text-align: left;
            }

            .wiboost-auth-card input,
            .wiboost-auth-card textarea {
                text-align: left !important;
            }

            .wiboost-auth-card form button[type="submit"] {
                justify-content: center !important;
                text-align: center !important;
            }
        @endif
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

            .wiboost-public-guest-card--unframed {
                border: 0 !important;
                border-radius: 0 !important;
                background: transparent !important;
                background-color: transparent !important;
                background-image: none !important;
                padding: 0 !important;
                box-shadow: none !important;
                backdrop-filter: none !important;
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

                .wiboost-public-guest-footer > div {
                    max-width: min(1600px, calc(100vw - 8rem));
                }
            }
        </style>
    @endif
    @include('partials.ios-layout-fixes')
</head>
<body class="wiboost-compact-ui wiboost-skeu min-h-screen bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white">
    <div class="relative bg-wiboost-sky px-4 sm:px-6 lg:px-8 {{ $isPublicMarketingForm ? 'wiboost-public-guest-shell min-h-screen overflow-x-hidden pt-0 pb-0 flex flex-col' : 'overflow-hidden' }} {{ $isCompactAuth ? 'min-h-screen py-4 flex flex-col' : ($isPublicMarketingForm ? '' : 'min-h-screen py-5') }}">
        
        @unless($isCompactAuth)
            <nav class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-nav fixed inset-x-0 top-0 z-50 px-0 pointer-events-none' : 'relative z-20 -mx-4 mt-0 w-auto sm:-mx-6 lg:-mx-8' }}">
                <div class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-nav-bar' : '' }} flex items-center justify-between rounded-none border-0 border-b-4 border-white bg-white/90 px-4 py-3 shadow-xl shadow-[#bde0fe]/50 backdrop-blur-md sm:px-6 lg:px-8">
                    <a href="{{ Auth::check() ? (Auth::user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard')) : route('home') }}" class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-brand' : '' }} flex items-center gap-3 group">
                        <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" alt="Logo Wiboost Store" width="192" height="192" fetchpriority="high" loading="eager" decoding="async" class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-logo' : '' }} h-12 w-12 shrink-0 rounded-full object-contain drop-shadow-sm transition-transform group-hover:scale-105 sm:h-14 sm:w-14">
                        <p class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-brand-text' : '' }} text-lg font-black leading-none text-[#2b3a67] drop-shadow-sm sm:text-2xl">Wiboost Store</p>
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
            <section class="{{ $isPublicMarketingForm ? 'wiboost-public-guest-card' : '' }} {{ $isPublicFormEntry ? 'wiboost-public-guest-card--unframed' : '' }} {{ $isCompactAuth ? 'wiboost-auth-card' : '' }} w-full rounded-[2.5rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/50 backdrop-blur {{ $isPublicMarketingForm ? 'p-5 sm:p-7 md:p-9' : 'p-6 md:p-8' }}">
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
            @include('partials.site-footer', ['class' => ($isPublicMarketingForm ? 'wiboost-public-guest-footer' : '').' -mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8'])
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
