<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f4f9ff; }
        ::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
    </style>
</head>
<body x-data="{ mobileNavOpen: false }" class="min-h-screen bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white">
    @php
        $userNav = [
            ['route' => 'user.dashboard', 'pattern' => 'user.dashboard', 'label' => 'Home'],
            ['route' => 'user.history', 'pattern' => 'user.history', 'label' => 'Riwayat'],
            ['route' => 'profile.edit', 'pattern' => 'profile.edit', 'label' => 'Profil'],
        ];
    @endphp

    <div class="pointer-events-none fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-6">
        <div class="pointer-events-auto mx-auto max-w-6xl">
            <div class="flex items-center justify-between rounded-[2rem] border-4 border-white bg-white/90 px-4 py-3 shadow-xl shadow-[#bde0fe]/40 backdrop-blur-md sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426-full" alt="Logo Wiboost Store" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm sm:h-14 sm:w-14">
                    <div>
                        <p class="text-lg font-black leading-none text-[#2b3a67] sm:text-2xl">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                    </div>
                </a>

                <div class="hidden items-center gap-2 md:flex">
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-full border-2 border-white bg-[#ffe5e5] px-4 py-2 text-xs font-black text-[#ff6b6b] shadow-sm transition hover:bg-[#ffcccc]">Panel Admin</a>
                    @endif

                    @foreach($userNav as $item)
                        <a href="{{ route($item['route']) }}" class="rounded-full px-4 py-2 text-sm font-black transition {{ request()->routeIs($item['pattern']) ? 'border-2 border-white bg-[#f0f5ff] text-[#5a76c8] shadow-sm' : 'text-[#8faaf3] hover:bg-[#f0f5ff] hover:text-[#5a76c8]' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach

                    <form method="POST" action="{{ route('logout') }}" class="m-0 ml-2">
                        @csrf
                        <button type="submit" class="rounded-full border-2 border-white bg-[#ffe5e5] px-5 py-2.5 text-sm font-black text-[#ff6b6b] shadow-sm transition hover:bg-[#ffcccc]">
                            Keluar
                        </button>
                    </form>
                </div>

                <div class="flex items-center gap-2 md:hidden">
                    <a href="{{ route('user.wallet.index') }}" class="rounded-full border-2 border-white bg-[#f0f5ff] px-3 py-2 text-xs font-black text-[#5a76c8] shadow-sm">
                        Rp {{ number_format((float) (Auth::user()->balance ?? 0), 0, ',', '.') }}
                    </a>
                    <button type="button" @click="mobileNavOpen = true" class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-white bg-[#f0f5ff] text-[#5a76c8] shadow-sm">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="mobileNavOpen" class="fixed inset-0 z-[60] md:hidden" aria-modal="true" role="dialog">
        <div x-show="mobileNavOpen" x-transition.opacity class="absolute inset-0 bg-[#2b3a67]/55 backdrop-blur-sm" @click="mobileNavOpen = false"></div>
        <div x-show="mobileNavOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-6 opacity-0" class="absolute inset-x-3 top-4 rounded-[2rem] border-4 border-white bg-white p-5 shadow-2xl">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Menu Cepat</p>
                    <h2 class="text-2xl font-black text-[#2b3a67]">{{ Auth::user()->name }}</h2>
                </div>
                <button type="button" @click="mobileNavOpen = false" class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-sm">✕</button>
            </div>

            <div class="space-y-3">
                @if(Auth::check() && Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between rounded-[1.5rem] border-2 border-white bg-[#fff3f3] px-5 py-4 font-black text-[#ff6b6b] shadow-sm">
                        <span>Panel Admin</span>
                        <span>→</span>
                    </a>
                @endif

                @foreach($userNav as $item)
                    <a href="{{ route($item['route']) }}" class="flex items-center justify-between rounded-[1.5rem] border-2 {{ request()->routeIs($item['pattern']) ? 'border-white bg-[#f0f5ff] text-[#5a76c8]' : 'border-transparent bg-[#f8fbff] text-[#2b3a67]' }} px-5 py-4 font-black shadow-sm">
                        <span>{{ $item['label'] }}</span>
                        <span>→</span>
                    </a>
                @endforeach

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mt-2 flex w-full items-center justify-center rounded-[1.5rem] border-2 border-white bg-[#ffe5e5] px-5 py-4 font-black text-[#ff6b6b] shadow-sm">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="h-28 sm:h-32"></div>

    <main class="mx-auto w-full max-w-6xl px-4 pb-28 sm:px-6 lg:px-8 md:pb-16">
        @yield('content')
    </main>

    <footer class="relative z-20 mt-auto overflow-hidden border-t-[6px] border-[#f0f5ff] bg-white pb-24 pt-12 md:pb-8">
        <div class="relative z-10 mx-auto max-w-6xl px-6 sm:px-8">
            <div class="mb-10 flex flex-col items-center justify-between gap-8 md:flex-row">
                <div class="text-center md:text-left">
                    <div class="mb-3 flex items-center justify-center gap-2 md:justify-start">
                        <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426-full" alt="Logo Wiboost Store" class="h-9 w-9 shrink-0 object-contain drop-shadow-sm">
                        <span class="text-xl font-black text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Store</span></span>
                    </div>
                    <p class="max-w-xs text-xs font-bold text-[#8faaf3]">Penyedia layanan sosial media dan top up game termurah & tercepat di Indonesia.</p>
                </div>

                <div class="flex flex-col items-center gap-3 md:items-end">
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-[#2b3a67]">Follow Us</p>
                    <div class="flex items-center gap-4">
                        <a href="https://instagram.com/ahawi_channel" target="_blank" class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#e1306c] shadow-sm transition-all hover:bg-[#5a76c8] hover:text-white">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>

                        <a href="https://tiktok.com/@ahawi_channel" target="_blank" class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#2b3a67] shadow-sm transition-all hover:bg-[#5a76c8] hover:text-white">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>
                        </a>

                        <a href="https://youtube.com/@ahawi_channel" target="_blank" class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#FF0000] shadow-sm transition-all hover:bg-[#5a76c8] hover:text-white">
                            <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t-2 border-[#f0f5ff] pt-8 text-center">
                <div class="mb-5 flex flex-wrap items-center justify-center gap-3 text-xs font-black text-[#5a76c8]">
                    <a href="{{ route('legal.show', 'terms') }}" class="rounded-full bg-[#f4f9ff] px-4 py-2 transition hover:bg-[#e0fbfc]">Syarat & Ketentuan</a>
                    <a href="{{ route('legal.show', 'privacy-policy') }}" class="rounded-full bg-[#f4f9ff] px-4 py-2 transition hover:bg-[#e0fbfc]">Privasi</a>
                    <a href="{{ route('legal.show', 'refund-policy') }}" class="rounded-full bg-[#f4f9ff] px-4 py-2 transition hover:bg-[#e0fbfc]">Refund</a>
                    <a href="{{ route('legal.show', 'contact') }}" class="rounded-full bg-[#f4f9ff] px-4 py-2 transition hover:bg-[#e0fbfc]">Kontak Admin</a>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-[#a3bbfb]">&copy; {{ date('Y') }} Wiboost Store. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <nav class="fixed inset-x-3 bottom-3 z-50 rounded-[2rem] border-4 border-white bg-white/95 p-2 shadow-2xl shadow-[#bde0fe]/40 backdrop-blur-md md:hidden">
        <div class="grid grid-cols-4 gap-2">
            @foreach($userNav as $item)
                <a href="{{ route($item['route']) }}" class="rounded-[1.25rem] px-3 py-3 text-center text-[11px] font-black {{ request()->routeIs($item['pattern']) ? 'bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/20' : 'text-[#8faaf3]' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    @include('partials.floating-admin-report', ['floatingOffsetClass' => 'bottom-24 md:bottom-6'])
</body>
</html>
