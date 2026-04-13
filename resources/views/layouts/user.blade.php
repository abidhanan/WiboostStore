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
            ['route' => 'user.wallet.index', 'pattern' => 'user.wallet.*', 'label' => 'Wallet'],
            ['route' => 'profile.edit', 'pattern' => 'profile.edit', 'label' => 'Profil'],
        ];
    @endphp

    <div class="pointer-events-none fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-6">
        <div class="pointer-events-auto mx-auto max-w-6xl">
            <div class="flex items-center justify-between rounded-[2rem] border-4 border-white bg-white/90 px-4 py-3 shadow-xl shadow-[#bde0fe]/40 backdrop-blur-md sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-[1rem] border-2 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] text-xl font-black text-white shadow-inner">W</div>
                    <div>
                        <p class="text-lg font-black leading-none text-[#2b3a67] sm:text-2xl">Wiboost<span class="text-[#5a76c8]">Store</span></p>
                        <p class="hidden text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3] sm:block">Digital Checkout</p>
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
        <div class="mx-auto flex max-w-6xl flex-col gap-8 px-6 sm:px-8 md:flex-row md:items-center md:justify-between">
            <div class="text-center md:text-left">
                <div class="mb-3 flex items-center justify-center gap-2 md:justify-start">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg border-2 border-[#bde0fe] bg-[#5a76c8] text-sm font-black text-white shadow-inner">W</div>
                    <span class="text-xl font-black text-[#2b3a67]">Wiboost<span class="text-[#5a76c8]">Store</span></span>
                </div>
                <p class="max-w-xs text-xs font-bold text-[#8faaf3]">Penyedia kebutuhan digital, top up, dan layanan sosial media yang cepat, rapi, dan siap diproses setiap hari.</p>
            </div>

            <div class="text-center md:text-right">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#2b3a67]">Pusat Bantuan</p>
                <p class="mt-2 text-sm font-bold text-[#8faaf3]">Jika pesanan butuh bantuan tambahan, gunakan menu riwayat atau hubungi admin dari halaman dashboard.</p>
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
</body>
</html>
