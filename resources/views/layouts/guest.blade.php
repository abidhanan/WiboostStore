@php
    $pageTitle = trim($__env->yieldContent('title', config('app.name', 'Wiboost Store')));
    $heroBadge = trim($__env->yieldContent('hero_badge', 'Aman dan cepat'));
    $heroTitle = trim($__env->yieldContent('hero_title', 'Masuk ke dashboard untuk kelola transaksi, saldo, dan layanan digitalmu.'));
    $heroCopy = trim($__env->yieldContent('hero_copy', 'Dari top up game, akun premium, sampai pesanan manual, semua flow sekarang sudah dirapikan supaya lebih siap dipakai harian di desktop maupun mobile.'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f9ff] text-slate-800 antialiased" style="font-family: 'Nunito', sans-serif;">
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(143,170,243,0.35),_transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(75,198,185,0.20),_transparent_30%),linear-gradient(180deg,_#eaf4ff_0%,_#f4f9ff_55%,_#ffffff_100%)]"></div>
        <div class="absolute -left-12 top-10 h-40 w-40 rounded-full bg-white/40 blur-3xl"></div>
        <div class="absolute -right-12 bottom-10 h-52 w-52 rounded-full bg-[#bde0fe]/50 blur-3xl"></div>

        <div class="relative z-10 grid w-full max-w-5xl gap-6 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="hidden rounded-[2.5rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] via-[#6f8ddc] to-[#4bc6b9] p-10 text-white shadow-2xl shadow-[#8faaf3]/30 lg:flex lg:flex-col lg:justify-between">
                <div>
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-[1.2rem] border-2 border-white/60 bg-white/15 text-2xl font-black shadow-inner">W</div>
                        <div>
                            <p class="text-3xl font-black">Wiboost Store</p>
                            <p class="text-xs font-black uppercase tracking-[0.35em] text-white/75">Digital Access</p>
                        </div>
                    </a>
                </div>

                <div>
                    <p class="mb-3 inline-flex rounded-full border border-white/40 bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-[0.3em]">{{ $heroBadge }}</p>
                    <h1 class="max-w-md text-4xl font-black leading-tight">{{ $heroTitle }}</h1>
                    <p class="mt-4 max-w-lg text-sm font-bold text-white/85">{{ $heroCopy }}</p>
                </div>
            </div>

            <div class="rounded-[2.5rem] border-4 border-white bg-white/95 p-6 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur md:p-8">
                <div class="mb-8 flex items-center justify-between lg:hidden">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-[1rem] border-2 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] text-xl font-black text-white shadow-inner">W</div>
                        <div>
                            <p class="text-xl font-black text-[#2b3a67]">Wiboost Store</p>
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Digital Checkout</p>
                        </div>
                    </a>
                </div>

                @hasSection('content')
                    @yield('content')
                @else
                    @isset($slot)
                        {{ $slot }}
                    @endisset
                @endif
            </div>
        </div>
    </div>

    @include('partials.floating-admin-report')
</body>
</html>
