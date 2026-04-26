@php
    $pageTitle = trim($__env->yieldContent('title', config('app.name', 'Wiboost Store')));
    $isCompactAuth = request()->routeIs('login', 'register', 'password.confirm', 'password.request', 'password.reset', 'verification.notice');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth {{ $isCompactAuth ? 'overflow-hidden' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
        .auth-float { animation: authFloat 6s ease-in-out infinite; }
        .auth-float-delayed { animation: authFloat 7s ease-in-out 1.5s infinite; }
        @keyframes authFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-14px); }
        }
    </style>
</head>
<body class="min-h-screen bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white {{ $isCompactAuth ? 'overflow-hidden' : '' }}">
    <div class="relative overflow-hidden bg-wiboost-sky px-4 sm:px-6 lg:px-8 {{ $isCompactAuth ? 'h-screen py-3 sm:py-4' : 'min-h-screen py-5' }}">
        <div class="pointer-events-none absolute left-8 top-28 h-28 w-28 rounded-full border-[18px] border-white/45 auth-float"></div>
        <div class="pointer-events-none absolute right-10 top-24 h-20 w-20 rounded-full bg-white/45 blur-sm auth-float-delayed"></div>
        <div class="pointer-events-none absolute bottom-24 left-[18%] h-24 w-24 rounded-full bg-[#8faaf3]/20 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-24 right-[-4rem] h-72 w-72 rounded-full bg-white/35 blur-3xl"></div>

        @unless($isCompactAuth)
            <nav class="relative z-20 mx-auto max-w-6xl">
                <div class="flex h-20 items-center justify-between rounded-[2rem] border-4 border-white bg-white/90 px-5 shadow-xl shadow-[#bde0fe]/50 backdrop-blur-md sm:px-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426-full" alt="Logo Wiboost Store" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm sm:h-14 sm:w-14">
                        <span class="text-xl font-extrabold tracking-tight text-[#2b3a67] sm:text-2xl">Wiboost <span class="text-[#5a76c8]">Store</span></span>
                    </a>

                    <div class="flex items-center gap-2">
                        @auth
                            <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard') }}" class="rounded-full border-2 border-white bg-[#5a76c8] px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#8faaf3]/40 transition hover:bg-[#4760a9]">
                                Dashboard
                            </a>
                        @else
                            @if(! request()->routeIs('login'))
                                <a href="{{ route('login') }}" class="hidden rounded-full px-5 py-2.5 text-sm font-extrabold text-[#5a76c8] transition hover:bg-[#f0f5ff] sm:inline-flex">Masuk</a>
                            @endif
                            @if(! request()->routeIs('register'))
                                <a href="{{ route('register') }}" class="rounded-full border-2 border-white bg-[#5a76c8] px-6 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#8faaf3]/40 transition hover:bg-[#4760a9]">Daftar</a>
                            @endif
                        @endauth
                    </div>
                </div>
            </nav>
        @endunless

        <main class="relative z-10 mx-auto flex w-full max-w-xl items-center {{ $isCompactAuth ? 'h-full py-0' : 'min-h-[calc(100vh-7rem)] py-12 lg:py-16' }}">
            <section class="w-full rounded-[2.5rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/35 backdrop-blur {{ $isCompactAuth ? 'max-h-[calc(100vh-1.5rem)] overflow-hidden p-5 sm:p-6 md:p-7' : 'p-6 md:p-8' }}">
                @hasSection('content')
                    @yield('content')
                @else
                    @isset($slot)
                        {{ $slot }}
                    @endisset
                @endif
            </section>
        </main>
    </div>

    @include('partials.floating-admin-report')
</body>
</html>
