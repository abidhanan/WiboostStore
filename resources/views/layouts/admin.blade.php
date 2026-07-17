<!DOCTYPE html>
<html lang="id">
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
        body { font-family: 'Nunito', sans-serif; overflow-x: hidden; }
        
        /* Custom Scrollbar Global */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f4f9ff; }
        ::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
        
        /* Hide Scrollbar khusus untuk Sidebar Navigasi agar lebih bersih */
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Floating Animations */
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        body.wiboost-admin-shell select:not([multiple]) {
            appearance: none;
            -webkit-appearance: none;
            background-color: #f4f9ff;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%235a76c8' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-position: right 1rem center;
            background-repeat: no-repeat;
            background-size: 1rem 1rem;
            border-color: #fff;
            border-radius: 1.2rem;
            box-shadow: inset 0 2px 8px rgba(143, 170, 243, .16), 0 8px 18px rgba(189, 224, 254, .18);
            color: #2b3a67;
            font-weight: 900;
            padding-right: 2.8rem !important;
        }

        body.wiboost-admin-shell select:not([multiple]):focus,
        body.wiboost-admin-shell select.wiboost-select-open {
            border-color: #bde0fe !important;
            outline: none;
        }

        body.wiboost-admin-shell select.wiboost-select-open {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%235a76c8' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m18 15-6-6-6 6'/%3E%3C/svg%3E");
        }

        body.wiboost-admin-shell .admin-clamp-1,
        body.wiboost-admin-shell .admin-clamp-2,
        body.wiboost-admin-shell .admin-clamp-3 {
            display: -webkit-box !important;
            -webkit-box-orient: vertical;
            white-space: normal !important;
            overflow: hidden !important;
            text-overflow: clip !important;
            word-break: break-word;
        }

        body.wiboost-admin-shell .admin-clamp-1 { -webkit-line-clamp: 1; }
        body.wiboost-admin-shell .admin-clamp-2 { -webkit-line-clamp: 2; }
        body.wiboost-admin-shell .admin-clamp-3 { -webkit-line-clamp: 3; }

        body.wiboost-admin-shell main .truncate,
        body.wiboost-admin-shell main .admin-readable {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            word-break: break-word;
        }

        @media (max-width: 767px) {
            body.wiboost-admin-shell {
                overflow-x: hidden;
            }

            body.wiboost-admin-shell .backdrop-blur,
            body.wiboost-admin-shell .backdrop-blur-sm,
            body.wiboost-admin-shell .backdrop-blur-md,
            body.wiboost-admin-shell .backdrop-blur-xl {
                -webkit-backdrop-filter: none !important;
                backdrop-filter: none !important;
            }

            body.wiboost-admin-shell .animate-float,
            body.wiboost-admin-shell .animate-float-delayed {
                animation: none !important;
            }

            body.wiboost-admin-shell .shadow-2xl,
            body.wiboost-admin-shell .shadow-xl {
                box-shadow: 0 8px 18px rgba(141, 174, 238, .16) !important;
            }

            body.wiboost-admin-shell main {
                overflow: visible !important;
                contain: layout paint;
            }

            body.wiboost-admin-shell.admin-detail-open main {
                overflow: visible !important;
                contain: none !important;
            }

            body.wiboost-admin-shell a,
            body.wiboost-admin-shell button,
            body.wiboost-admin-shell input,
            body.wiboost-admin-shell select,
            body.wiboost-admin-shell textarea,
            body.wiboost-admin-shell tr {
                transition-duration: 120ms !important;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            body.wiboost-admin-shell *,
            body.wiboost-admin-shell *::before,
            body.wiboost-admin-shell *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body
    x-data="{
        sidebarOpen: false,
        restoreSidebarScroll() {
            this.$nextTick(() => {
                try {
                    const savedScroll = window.localStorage.getItem('wiboost-admin-sidebar-scroll');
                    if (savedScroll && this.$refs.adminSidebarNav) {
                        this.$refs.adminSidebarNav.scrollTop = Number(savedScroll);
                    }
                } catch (error) {}
            });
        },
        saveSidebarScroll() {
            try {
                if (this.$refs.adminSidebarNav) {
                    window.localStorage.setItem('wiboost-admin-sidebar-scroll', this.$refs.adminSidebarNav.scrollTop);
                }
            } catch (error) {}
        }
    }"
    x-init="restoreSidebarScroll()"
    class="wiboost-compact-ui wiboost-admin-shell min-h-screen bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white relative flex"
>
    @php
        $adminNav = [
            ['route' => 'admin.resellers.index', 'pattern' => 'admin.resellers.*', 'label' => 'Reseller', 'title' => 'Manajemen Reseller', 'description' => 'Pantau komisi reseller dan konfirmasi penarikan.', 'icon' => 'R'],
            ['route' => 'admin.commissions.index', 'pattern' => 'admin.commissions.*', 'label' => 'Komisi', 'title' => 'Halaman Komisi', 'description' => 'Pantau komisi masuk dan konfirmasi penarikan reseller.', 'icon' => 'K'],
            ['route' => 'admin.members.index', 'pattern' => 'admin.members.*', 'label' => 'Member', 'title' => 'Halaman Member', 'description' => 'Pantau user dengan membership aktif.', 'icon' => 'M'],
            ['route' => 'admin.admins.index', 'pattern' => 'admin.admins.*', 'label' => 'Admin', 'title' => 'Halaman Admin', 'description' => 'Pantau akun admin Wiboost Store.', 'icon' => 'A'],
            ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'description' => 'Pantau ringkasan transaksi, saldo, stok, dan aktivitas terbaru.', 'icon' => '📊'],
            ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'label' => 'Pengguna', 'title' => 'Manajemen Pengguna', 'description' => 'Atur data admin dan pelanggan Wiboost Store.', 'icon' => '👥'],
            ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Kategori', 'title' => 'Manajemen Kategori', 'description' => 'Kelola pengelompokan layanan dan produk Wiboost Store.', 'icon' => '📂'],
            ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => 'Produk', 'title' => 'Manajemen Produk', 'description' => 'Kelola harga, stok, dan sinkronisasi layanan provider.', 'icon' => '📦'],
            ['route' => 'admin.promos.index', 'pattern' => 'admin.promos.*', 'label' => 'Banner', 'title' => 'Manajemen Banner', 'description' => 'Kelola slider pengumuman yang muncul di Dashboard User.', 'icon' => '📢'],
            ['route' => 'admin.tutorials.index', 'pattern' => 'admin.tutorials.*', 'label' => 'Tutorial', 'title' => 'Manajemen Tutorial', 'description' => 'Kelola kategori utama, kategori kedua, dan isi tutorial pelanggan.', 'icon' => '📖'],
            ['route' => 'admin.transactions.index', 'pattern' => 'admin.transactions.*', 'label' => 'Transaksi', 'title' => 'Manajemen Transaksi', 'description' => 'Pantau pesanan pelanggan dan filter berdasarkan bulan.', 'icon' => '💳'],
            ['route' => 'admin.manual-orders.index', 'pattern' => 'admin.manual-orders.*', 'label' => 'Manual Order', 'description' => 'Kelola pesanan manual pelanggan.', 'icon' => '✍️'],
            ['route' => 'admin.deposits.index', 'pattern' => 'admin.deposits.*', 'label' => 'Deposit', 'title' => 'Manajemen Deposit', 'description' => 'Pantau riwayat top-up atau pengisian saldo pengguna.', 'icon' => '💰'],
            ['route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'label' => 'Laporan', 'title' => 'Manajemen Laporan', 'description' => 'Pantau kendala yang dikirim pelanggan dari halaman bantuan.', 'icon' => '📩'],
            ['route' => 'profile.edit', 'pattern' => 'profile.edit', 'label' => 'Profil', 'description' => 'Kelola informasi profil dan keamanan akun.', 'icon' => '👤'],
        ];

        $adminNav = array_values(array_filter(
            $adminNav,
            fn (array $item) => $item['route'] !== 'admin.manual-orders.index'
        ));

        $adminNavMatches = function (array $item): bool {
            if ($item['route'] === 'admin.products.index' && request()->routeIs('admin.credentials.*')) {
                return true;
            }

            return request()->routeIs(...(array) $item['pattern']);
        };

        $activeAdminNav = collect($adminNav)->first($adminNavMatches);
        $adminNavbarTitle = $activeAdminNav['title'] ?? $activeAdminNav['label'] ?? trim($__env->yieldContent('title'));
        $adminNavbarSubtitle = $activeAdminNav['description'] ?? trim($__env->yieldContent('admin_header_subtitle'));
    @endphp

    <!-- Sidebar Desktop -->
    <aside class="sticky top-0 hidden h-screen w-80 shrink-0 p-5 xl:block z-40">
        <div class="flex h-full flex-col rounded-[2.5rem] border-4 border-white bg-white/80 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-xl">
            <div class="shrink-0 border-b-4 border-dashed border-[#f4f9ff] px-6 py-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" onerror="this.onerror=null;this.src='{{ asset('images/wiboost-logo.png') }}?v=20260426-full';" alt="Logo Wiboost Store" width="192" height="192" fetchpriority="high" loading="eager" decoding="async" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm group-hover:scale-110 transition-transform">
                    <div class="min-w-0 flex-1">
                        <p class="text-xl font-black leading-tight text-[#2b3a67] break-words">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                    </div>
                </a>
            </div>
            <nav x-ref="adminSidebarNav" @scroll.debounce.150ms="saveSidebarScroll()" class="flex-1 space-y-2 overflow-y-auto overscroll-contain px-5 py-6 hide-scroll">
                @foreach($adminNav as $item)
                    @php($isActiveAdminNav = $adminNavMatches($item))
                    <a href="{{ route($item['route']) }}" @click="saveSidebarScroll()" class="flex items-center gap-3 rounded-full px-6 py-3.5 text-sm font-black transition-all duration-300 {{ $isActiveAdminNav ? 'border-2 border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 scale-[1.02]' : 'border-2 border-transparent text-[#5a76c8] hover:border-white hover:bg-[#f4f9ff] hover:scale-[1.02]' }}">
                        <span class="text-lg">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="shrink-0 border-t-4 border-dashed border-[#f4f9ff] p-5">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-6 py-3.5 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 transition-all hover:bg-[#ffcccc] active:scale-95 gap-2">
                        Keluar Akun 🚪
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex min-h-screen min-w-0 flex-1 flex-col p-4 sm:p-6 xl:p-8 z-10">
        
        <!-- Header -->
        <div class="sticky top-4 z-40 mb-6 sm:mb-8 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 px-4 sm:px-6 py-4 shadow-xl shadow-[#bde0fe]/20 backdrop-blur-xl flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3 sm:gap-4 w-full lg:w-auto">
                <!-- Hamburger Button Mobile -->
                <button type="button" @click="sidebarOpen = true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition-transform active:scale-95 xl:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                
                <div class="min-w-0 flex-1 overflow-visible">
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-black leading-tight text-[#2b3a67] drop-shadow-sm whitespace-normal break-words">{{ $adminNavbarTitle }}</h1>
                    @if($adminNavbarSubtitle)
                        <p class="mt-1 block w-full max-w-full overflow-visible text-[10px] sm:text-xs font-bold leading-snug text-[#8faaf3] whitespace-normal break-words" style="-webkit-line-clamp: unset; text-overflow: clip;">
                            {{ $adminNavbarSubtitle }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Header Actions -->
            @hasSection('admin_header_actions')
                <div class="flex w-full shrink-0 flex-col gap-2 sm:flex-row sm:flex-wrap lg:w-auto lg:items-center lg:justify-end">
                    @yield('admin_header_actions')
                </div>
            @endif
        </div>

        <main class="flex-1 rounded-[2.5rem] min-w-0 w-full overflow-hidden">
            @yield('content')
        </main>
    </div>

    <!-- Sidebar Mobile Drawer -->
    <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-[70] xl:hidden" aria-modal="true" role="dialog">
        <!-- Backdrop bisa diklik untuk menutup sidebar tanpa tombol close -->
        <div x-show="sidebarOpen" x-transition.opacity class="absolute inset-0 bg-[#2b3a67]/60 backdrop-blur-md" @click="sidebarOpen = false"></div>
        
        <!-- Sidebar Container (Tanpa scroll di parent) -->
        <aside x-show="sidebarOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="absolute inset-y-4 left-4 max-h-[calc(100vh-2rem)] w-[min(20rem,calc(100vw-2.5rem))] rounded-[2.5rem] border-4 border-white bg-white p-5 shadow-2xl flex flex-col">
            
            <!-- Header Mobile Sidebar (Fixed/Tidak ikut scroll) -->
            <div class="shrink-0 mb-6 flex items-center gap-3 border-b-4 border-dashed border-[#f4f9ff] pb-6">
                <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" onerror="this.onerror=null;this.src='{{ asset('images/wiboost-logo.png') }}?v=20260426-full';" alt="Logo Wiboost Store" width="192" height="192" loading="eager" decoding="async" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm">
                <div class="min-w-0 flex-1">
                    <p class="text-xl font-black leading-tight text-[#2b3a67] break-words">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                </div>
            </div>
            
            <!-- Nav Menu Mobile Sidebar (Scrollable & Hide Scrollbar) -->
            <nav class="flex-1 space-y-2 overflow-y-auto overscroll-contain hide-scroll pr-1">
                @foreach($adminNav as $item)
                    @php($isActiveAdminNav = $adminNavMatches($item))
                    <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-full px-5 py-3 text-sm font-black transition-all {{ $isActiveAdminNav ? 'bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 border-2 border-white' : 'bg-[#f4f9ff] text-[#2b3a67] border-2 border-transparent hover:border-white' }}">
                        <span class="text-lg">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            
            <!-- Footer Mobile Sidebar (Fixed/Tidak ikut scroll) -->
            <div class="shrink-0 mt-6 pt-6 border-t-4 border-dashed border-[#f4f9ff]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-6 py-3.5 text-sm font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 active:scale-95 transition-transform gap-2">
                        Keluar Akun 🚪
                    </button>
                </form>
            </div>
        </aside>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('body.wiboost-admin-shell select:not([multiple])').forEach(function (select) {
                const open = () => select.classList.add('wiboost-select-open');
                const close = () => select.classList.remove('wiboost-select-open');

                select.addEventListener('mousedown', open);
                select.addEventListener('focus', open);
                select.addEventListener('touchstart', open, { passive: true });
                select.addEventListener('change', close);
                select.addEventListener('blur', close);
                select.addEventListener('keydown', function (event) {
                    if (['ArrowDown', 'ArrowUp', ' '].includes(event.key)) {
                        open();
                    }

                    if (['Escape', 'Enter', 'Tab'].includes(event.key)) {
                        close();
                    }
                });
            });
        });

        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || form.dataset.noSubmitLock === 'true') {
                return;
            }

            if (event.defaultPrevented) {
                return;
            }

            const spoofedMethod = form.querySelector('input[name="_method"]')?.value;
            const method = (spoofedMethod || form.method || 'get').toLowerCase();

            if (method === 'get') {
                return;
            }

            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';

            const button = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
            if (!button || button.dataset.keepEnabled === 'true') {
                return;
            }

            button.disabled = true;
            button.classList.add('cursor-wait', 'opacity-75');
        });
    </script>
    @include('partials.page-loading')
</body>
</html>
