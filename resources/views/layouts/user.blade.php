<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Wiboost Store</title>
    @include('partials.site-icons')
    <link rel="preload" as="image" href="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" fetchpriority="high">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.vite-fallback')
    @include('partials.ui-performance-tuning')
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        html,
        body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        body { font-family: 'Nunito', sans-serif; }
        main,
        footer {
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
        }

        img,
        svg,
        video,
        canvas {
            max-width: 100%;
        }

        [x-cloak] { display: none !important; }
        .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f4f9ff; }
        ::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
        
        /* Floating Animations */
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        @media (min-width: 1024px) {
            body.wiboost-user-shell .wiboost-user-navbar > div,
            body.wiboost-user-shell main,
            body.wiboost-user-shell footer > div {
                max-width: min(1600px, calc(100vw - 8rem)) !important;
            }

            body.wiboost-user-shell main {
                padding-left: 2rem !important;
                padding-right: 2rem !important;
            }

            body.wiboost-user-shell main > .wiboost-font {
                max-width: 100% !important;
            }

            body.wiboost-user-shell main > [class*="max-w-"],
            body.wiboost-user-shell main > .wiboost-font > [class*="max-w-"] {
                max-width: 100% !important;
            }
        }

        @media (max-width: 480px) {
            .wiboost-user-navbar-card {
                gap: 0.45rem;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            .wiboost-user-navbar-brand {
                flex: 1 1 auto;
                gap: 0.5rem !important;
                min-width: 0;
            }

            .wiboost-user-navbar-logo {
                height: 2.65rem !important;
                width: 2.65rem !important;
            }

            .wiboost-user-navbar-title {
                font-size: clamp(0.88rem, 4.2vw, 1.08rem) !important;
                letter-spacing: -0.01em;
                line-height: 1 !important;
                white-space: nowrap !important;
            }

            .wiboost-user-navbar-actions {
                flex: 0 0 auto;
                gap: 0.45rem !important;
            }

            .wiboost-user-navbar-balance {
                font-size: clamp(0.64rem, 2.9vw, 0.74rem) !important;
                line-height: 1 !important;
                min-width: max-content;
                padding: 0.58rem 0.65rem !important;
                white-space: nowrap !important;
            }

            .wiboost-user-navbar-menu {
                height: 2.55rem !important;
                width: 2.55rem !important;
            }
        }
    </style>
    @include('partials.ios-layout-fixes')
</head>
<body x-data="{ mobileNavOpen: false }" class="wiboost-compact-ui wiboost-user-shell @yield('body_class') min-h-screen w-full max-w-full overflow-x-hidden bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white relative flex flex-col">

    @php
        $userNav = [
            ['route' => 'user.dashboard', 'pattern' => 'user.dashboard', 'label' => 'Home', 'icon' => '🏠'],
            ['route' => 'user.history', 'pattern' => 'user.history', 'label' => 'Riwayat', 'icon' => '🕒'],
            ['route' => 'profile.edit', 'pattern' => 'profile.edit', 'label' => 'Profil', 'icon' => '👤']
        ];
    @endphp

    <div class="wiboost-user-navbar pointer-events-none fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-6">
        <div class="pointer-events-auto mx-auto max-w-6xl">
            <div class="wiboost-user-navbar-card flex items-center justify-between rounded-[2rem] border-4 border-white bg-white/90 px-4 py-3 shadow-xl shadow-[#bde0fe]/40 backdrop-blur-md sm:px-6 transition-all hover:shadow-[#bde0fe]/60 hover:border-[#e0fbfc]">
                <a href="{{ route('user.dashboard') }}" class="wiboost-user-navbar-brand flex items-center gap-3 group">
                    <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" onerror="this.onerror=null;this.src='{{ asset('images/wiboost-logo.png') }}?v=20260426-full';" alt="Logo Wiboost Store" width="192" height="192" fetchpriority="high" loading="eager" decoding="async" class="wiboost-user-navbar-logo h-12 w-12 shrink-0 object-contain drop-shadow-sm sm:h-14 sm:w-14 group-hover:scale-105 transition-transform">
                    <div>
                        <p class="wiboost-user-navbar-title text-lg font-black leading-none text-[#2b3a67] sm:text-2xl drop-shadow-sm">Wiboost <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]">Store</span></p>
                    </div>
                </a>
                
                <div class="hidden items-center gap-2 md:flex">
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-full border-2 border-white bg-[#ffe5e5] px-4 py-2 text-xs font-black text-[#ff6b6b] shadow-sm transition hover:bg-[#ffcccc] hover:scale-105">Panel Admin</a>
                    @endif
                    
                    @foreach($userNav as $item)
                        <a href="{{ route($item['route']) }}" class="rounded-full px-5 py-2.5 text-sm font-black transition-all hover:scale-105 {{ request()->routeIs($item['pattern']) ? 'border-2 border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30' : 'text-[#8faaf3] hover:bg-[#f0f5ff] hover:text-[#5a76c8]' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <form method="POST" action="{{ route('logout') }}" class="m-0 ml-2">
                        @csrf
                        <button type="submit" class="rounded-full border-2 border-white bg-[#ffe5e5] px-5 py-2.5 text-sm font-black text-[#ff6b6b] shadow-sm transition-all hover:bg-[#ffcccc] hover:scale-105 active:scale-95">
                            Keluar
                        </button>
                    </form>
                </div>

                <div class="wiboost-user-navbar-actions flex items-center gap-2 md:hidden">
                    <a href="{{ route('user.wallet.index') }}" class="wiboost-user-navbar-balance rounded-full border-2 border-white bg-[#f0f5ff] px-3 py-2 text-xs font-black text-[#5a76c8] shadow-sm">
                        Rp {{ number_format((float) (Auth::user()->balance ?? 0), 0, ',', '.') }}
                    </a>
                    <button type="button" @click="mobileNavOpen = !mobileNavOpen" :aria-expanded="mobileNavOpen.toString()" class="wiboost-user-navbar-menu flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-white bg-[#f0f5ff] text-[#5a76c8] shadow-sm hover:scale-105 transition-transform">
                        <svg x-show="!mobileNavOpen" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <svg x-show="mobileNavOpen" x-cloak class="h-6 w-6 text-[#ff6b6b]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Menu Cepat Mobile --}}
    <div x-cloak x-show="mobileNavOpen" class="fixed inset-x-0 bottom-0 top-24 z-40 md:hidden" aria-modal="true" role="dialog">
        <div x-show="mobileNavOpen" x-transition.opacity class="absolute inset-0 bg-[#2b3a67]/55 backdrop-blur-sm" @click="mobileNavOpen = false"></div>
        
        <div x-show="mobileNavOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-y-6 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="-translate-y-6 opacity-0"
             class="absolute inset-x-0 top-0 rounded-b-[2rem] border-x-4 border-b-4 border-white bg-white/95 px-5 pt-5 pb-6 shadow-2xl">
            
            <div class="space-y-3">
                @if(Auth::check() && Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between rounded-[1.5rem] border-2 border-white bg-[#fff3f3] px-5 py-4 font-black text-[#ff6b6b] shadow-sm hover:scale-[1.02] hover:shadow-md transition-all">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white shadow-sm border border-[#ffe5e5]">
                                <span class="text-xl drop-shadow-sm">👑</span>
                            </div>
                            <span>Panel Admin</span>
                        </div>
                        <svg class="w-5 h-5 text-[#ff6b6b]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @endif
                
                {{-- List Vertical Minimalis --}}
                <div class="flex flex-col gap-2.5">
                    @foreach($userNav as $item)
                        <a href="{{ route($item['route']) }}" class="flex items-center justify-between rounded-[1.5rem] border-2 {{ request()->routeIs($item['pattern']) ? 'border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/30' : 'border-transparent bg-[#f8fbff] text-[#2b3a67] hover:border-white hover:bg-[#e0fbfc]' }} px-5 py-3.5 font-black transition-all hover:translate-x-1">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center w-10 h-10 rounded-xl {{ request()->routeIs($item['pattern']) ? 'bg-white/20 shadow-inner' : 'bg-white shadow-sm border border-[#f0f5ff]' }}">
                                    <span class="text-xl drop-shadow-sm">{{ $item['icon'] }}</span>
                                </div>
                                <span class="text-sm uppercase tracking-widest">{{ $item['label'] }}</span>
                            </div>
                            <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('logout') }}" class="pt-4 border-t-2 border-dashed border-[#f4f9ff]">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#ffe5e5] px-5 py-4 text-sm font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 hover:bg-[#ffcccc] transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Keluar Akun
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="h-28 sm:h-32"></div>

    <main class="flex-1 mx-auto w-full max-w-6xl px-4 pb-20 sm:px-6 lg:px-8 md:pb-16 relative z-10">
        @yield('content')
    </main>

    {{-- Footer User --}}
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

    {{-- Widget Lapor Admin --}}
    @include('partials.floating-admin-report')
    @include('partials.page-loading')
    <script>
        (() => {
            const cookieName = 'wiboost_after_admin_report';
            const pendingKey = 'wiboost_admin_report_pending';
            const leftPageKey = 'wiboost_admin_report_left_page';
            const dashboardUrl = @json(route('user.dashboard'));

            const readCookie = () => document.cookie
                .split('; ')
                .find((row) => row.startsWith(cookieName + '='))
                ?.split('=')[1] || null;

            const hasRedirectMarker = () => {
                try {
                    return readCookie() !== null
                        || window.localStorage?.getItem(cookieName) === 'dashboard'
                        || window.sessionStorage?.getItem(cookieName) === 'dashboard';
                } catch (error) {
                    return readCookie() !== null;
                }
            };

            const clearCookie = () => {
                document.cookie = cookieName + '=; Max-Age=0; path=/; SameSite=Lax';
            };

            window.markWiboostAdminChatOpened = () => {
                document.cookie = cookieName + '=dashboard; Max-Age=600; path=/; SameSite=Lax';
                try {
                    window.localStorage?.setItem(cookieName, 'dashboard');
                    window.sessionStorage?.setItem(cookieName, 'dashboard');
                    window.sessionStorage?.removeItem(pendingKey);
                    window.sessionStorage?.removeItem(leftPageKey);
                } catch (error) {}
            };

            window.markWiboostAdminReportSubmit = () => {
                try {
                    window.sessionStorage?.removeItem(pendingKey);
                    window.sessionStorage?.removeItem(leftPageKey);
                } catch (error) {}
            };

            const clearMarkers = () => {
                clearCookie();

                try {
                    window.localStorage?.removeItem(cookieName);
                    window.sessionStorage?.removeItem(cookieName);
                    window.sessionStorage?.removeItem(pendingKey);
                    window.sessionStorage?.removeItem(leftPageKey);
                } catch (error) {}
            };

            const redirectAfterAdminReport = () => {
                if (!hasRedirectMarker()) {
                    return;
                }

                clearMarkers();
                window.wiboostHidePageLoader?.();

                if (window.location.href !== dashboardUrl) {
                    window.location.replace(dashboardUrl);
                }
            };

            document.addEventListener('visibilitychange', () => {
                try {
                    if (document.hidden && hasRedirectMarker()) {
                        window.sessionStorage.setItem(leftPageKey, '1');
                    }
                } catch (error) {}

                if (!document.hidden) {
                    redirectAfterAdminReport();
                }
            });
            window.addEventListener('focus', () => redirectAfterAdminReport());
            window.addEventListener('pageshow', () => redirectAfterAdminReport());
            redirectAfterAdminReport();
        })();
    </script>
</body>
</html>
