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
        :root {
            --wiboost-admin-topbar-height: 6.25rem;
            --wiboost-admin-profile-border: #22c55e;
        }
        
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

        body.wiboost-admin-shell {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        body.wiboost-admin-shell .wiboost-admin-navbar {
            padding-left: 0 !important;
            padding-right: 0 !important;
            padding-top: 0 !important;
        }

        body.wiboost-admin-shell .wiboost-admin-navbar > div {
            width: 100% !important;
            max-width: none !important;
        }

        body.wiboost-admin-shell .wiboost-admin-navbar-card {
            min-height: 5.25rem;
            border-width: 0 0 4px 0 !important;
            border-color: #ffffff !important;
            border-radius: 0 !important;
        }

        body.wiboost-admin-shell .wiboost-admin-navbar-card:hover {
            border-color: #ffffff !important;
            box-shadow: 0 20px 45px rgba(189, 224, 254, 0.4) !important;
        }

        .wiboost-admin-navbar-toggle {
            display: none;
        }

        .wiboost-admin-content {
            padding-top: calc(var(--wiboost-admin-topbar-height) + 1rem);
        }

        @media (min-width: 1024px) {
            body.wiboost-admin-shell .wiboost-admin-sidebar {
                display: block !important;
                top: 0 !important;
                bottom: 0 !important;
                left: 0 !important;
                width: 18rem !important;
                max-height: 100vh !important;
                border-radius: 0 !important;
                transition: width 0.28s ease;
            }

            body.wiboost-admin-shell .wiboost-admin-sidebar > div {
                height: 100% !important;
                min-height: 0;
                overflow: hidden;
                border-width: 0 4px 0 0 !important;
                border-color: #ffffff !important;
                border-radius: 0 !important;
            }

            body.wiboost-admin-shell .wiboost-admin-sidebar > div > div:first-child {
                min-height: var(--wiboost-admin-topbar-height) !important;
                display: flex;
                align-items: center;
            }

            body.wiboost-admin-shell .wiboost-admin-sidebar nav {
                min-height: 0;
            }

            body.wiboost-admin-shell .wiboost-admin-navbar {
                left: 18rem !important;
                right: 0 !important;
                top: 0 !important;
                transition: left 0.28s ease;
            }

            body.wiboost-admin-shell .wiboost-admin-navbar-card {
                min-height: var(--wiboost-admin-topbar-height);
                justify-content: space-between !important;
                overflow: visible !important;
                padding-top: 0.65rem !important;
                padding-bottom: 0.65rem !important;
            }

            body.wiboost-admin-shell .wiboost-admin-mobile-toggle {
                display: none !important;
            }

            body.wiboost-admin-shell .wiboost-admin-navbar-toggle {
                display: flex !important;
            }

            body.wiboost-admin-shell .wiboost-admin-content {
                margin-left: 18rem !important;
                max-width: calc(100vw - 18rem) !important;
                width: calc(100vw - 18rem) !important;
                padding-top: calc(var(--wiboost-admin-topbar-height) + 2rem) !important;
                transition: margin-left 0.28s ease, max-width 0.28s ease, width 0.28s ease;
            }

            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-sidebar {
                width: 5.5rem !important;
            }

            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-navbar {
                left: 5.5rem !important;
            }

            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-content {
                margin-left: 5.5rem !important;
                max-width: calc(100vw - 5.5rem) !important;
                width: calc(100vw - 5.5rem) !important;
            }

            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-sidebar > div > div:first-child {
                display: grid !important;
                place-items: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-sidebar > div > div:first-child a,
            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-sidebar nav a,
            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-sidebar-footer a,
            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-sidebar-footer button {
                justify-content: center !important;
            }

            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-sidebar nav {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-sidebar nav a {
                height: 3.75rem;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-sidebar-brand-text,
            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-sidebar-link-label,
            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-sidebar-profile-text,
            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-sidebar-logout-label {
                display: none !important;
            }

            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-sidebar-footer {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-sidebar-footer a,
            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-sidebar-footer button {
                height: 3.75rem;
                width: 3.75rem;
                padding: 0 !important;
            }

            body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .wiboost-admin-navbar-toggle svg {
                transform: rotate(180deg);
            }
        }

        @media (max-width: 1023px) {
            body.wiboost-admin-shell .wiboost-admin-navbar-card {
                min-height: 4.75rem;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            body.wiboost-admin-shell .wiboost-admin-content {
                padding-top: 6rem !important;
            }

            body.wiboost-admin-shell .wiboost-admin-mobile-menu-panel {
                border-radius: 0 !important;
                height: 100dvh !important;
                max-height: 100dvh !important;
                overflow: hidden !important;
            }

            body.wiboost-admin-shell .wiboost-admin-mobile-sidebar-stack {
                display: grid !important;
                grid-template-rows: auto minmax(0, 1fr) auto !important;
                height: 100% !important;
                min-height: 0 !important;
                row-gap: 0.75rem !important;
            }

            body.wiboost-admin-shell .wiboost-admin-mobile-sidebar-nav {
                min-height: 0 !important;
                overflow-y: auto !important;
                overscroll-behavior: contain !important;
                padding-bottom: 0.25rem !important;
            }

            body.wiboost-admin-shell .wiboost-admin-mobile-sidebar-footer {
                min-height: 0 !important;
                padding-bottom: max(0.25rem, env(safe-area-inset-bottom)) !important;
            }
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
        mobileNavOpen: false,
        profileMenuOpen: false,
        desktopSidebarOpen: (() => {
            try {
                return localStorage.getItem('wiboost_admin_desktop_sidebar_open') !== '0';
            } catch (error) {
                return true;
            }
        })(),
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
    x-init="
        try {
            $watch('desktopSidebarOpen', value => localStorage.setItem('wiboost_admin_desktop_sidebar_open', value ? '1' : '0'));
        } catch (error) {}
        restoreSidebarScroll();
    "
    :class="{ 'wiboost-admin-sidebar-collapsed': !desktopSidebarOpen }"
    class="wiboost-compact-ui wiboost-admin-shell min-h-screen w-full max-w-full overflow-x-hidden bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white relative flex flex-col"
>
    <script>
        try {
            if (localStorage.getItem('wiboost_admin_desktop_sidebar_open') === '0') {
                document.body.classList.add('wiboost-admin-sidebar-collapsed');
            }
        } catch (error) {}
    </script>

    @php
        $adminNav = [
            ['route' => 'admin.resellers.index', 'pattern' => 'admin.resellers.*', 'label' => 'Reseller', 'title' => 'Manajemen Reseller', 'description' => 'Kelola reseller dan penarikan komisi.', 'icon' => 'R'],
            ['route' => 'admin.commissions.index', 'pattern' => 'admin.commissions.*', 'label' => 'Komisi', 'title' => 'Halaman Komisi', 'description' => 'Pantau komisi dan status pencairan.', 'icon' => 'K'],
            ['route' => 'admin.members.index', 'pattern' => 'admin.members.*', 'label' => 'Member', 'title' => 'Halaman Member', 'description' => 'Pantau member aktif dan masa berlaku.', 'icon' => 'M'],
            ['route' => 'admin.admins.index', 'pattern' => 'admin.admins.*', 'label' => 'Admin', 'title' => 'Halaman Admin', 'description' => 'Kelola akun admin Wiboost Store.', 'icon' => 'A'],
            ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'description' => 'Ringkasan transaksi dan aktivitas toko.', 'icon' => '📊'],
            ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'label' => 'Pengguna', 'title' => 'Manajemen Pengguna', 'description' => 'Kelola data pengguna dan role akun.', 'icon' => '👥'],
            ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Kategori', 'title' => 'Manajemen Kategori', 'description' => 'Atur kategori layanan dan produk.', 'icon' => '📂'],
            ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => 'Produk', 'title' => 'Manajemen Produk', 'description' => 'Kelola harga, stok, dan provider.', 'icon' => '📦'],
            ['route' => 'admin.promos.index', 'pattern' => 'admin.promos.*', 'label' => 'Banner', 'title' => 'Manajemen Banner', 'description' => 'Kelola banner dan pengumuman user.', 'icon' => '📢'],
            ['route' => 'admin.tutorials.index', 'pattern' => 'admin.tutorials.*', 'label' => 'Tutorial', 'title' => 'Manajemen Tutorial', 'description' => 'Kelola panduan dan tutorial pelanggan.', 'icon' => '📖'],
            ['route' => 'admin.transactions.index', 'pattern' => 'admin.transactions.*', 'label' => 'Transaksi', 'title' => 'Manajemen Transaksi', 'description' => 'Pantau transaksi dan pesanan pelanggan.', 'icon' => '💳'],
            ['route' => 'admin.manual-orders.index', 'pattern' => 'admin.manual-orders.*', 'label' => 'Manual Order', 'description' => 'Kelola pesanan manual pelanggan.', 'icon' => '✍️'],
            ['route' => 'admin.deposits.index', 'pattern' => 'admin.deposits.*', 'label' => 'Deposit', 'title' => 'Manajemen Deposit', 'description' => 'Pantau top-up saldo pengguna.', 'icon' => '💰'],
            ['route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'label' => 'Laporan', 'title' => 'Manajemen Laporan', 'description' => 'Pantau laporan kendala pelanggan.', 'icon' => '📩'],
            ['route' => 'profile.edit', 'pattern' => 'profile.edit', 'label' => 'Profil', 'description' => 'Kelola profil dan keamanan akun.', 'icon' => '👤'],
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

        $adminNav = [
            ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'description' => 'Ringkasan transaksi dan aktivitas toko.', 'icon' => 'dashboard'],
            ['route' => 'admin.resellers.index', 'pattern' => 'admin.resellers.*', 'label' => 'Reseller', 'title' => 'Manajemen Reseller', 'description' => 'Kelola reseller dan penarikan komisi.', 'icon' => 'reseller'],
            ['route' => 'admin.commissions.index', 'pattern' => 'admin.commissions.*', 'label' => 'Komisi', 'title' => 'Halaman Komisi', 'description' => 'Pantau komisi dan status pencairan.', 'icon' => 'commission'],
            ['route' => 'admin.members.index', 'pattern' => 'admin.members.*', 'label' => 'Member', 'title' => 'Halaman Member', 'description' => 'Pantau member aktif dan masa berlaku.', 'icon' => 'member'],
            ['route' => 'admin.admins.index', 'pattern' => 'admin.admins.*', 'label' => 'Admin', 'title' => 'Halaman Admin', 'description' => 'Kelola akun admin Wiboost Store.', 'icon' => 'admin'],
            ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'label' => 'Pengguna', 'title' => 'Manajemen Pengguna', 'description' => 'Kelola data pengguna dan role akun.', 'icon' => 'users'],
            ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Kategori', 'title' => 'Manajemen Kategori', 'description' => 'Atur kategori layanan dan produk.', 'icon' => 'category'],
            ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => 'Produk', 'title' => 'Manajemen Produk', 'description' => 'Kelola harga, stok, dan provider.', 'icon' => 'product'],
            ['route' => 'admin.promos.index', 'pattern' => 'admin.promos.*', 'label' => 'Banner', 'title' => 'Manajemen Banner', 'description' => 'Kelola banner dan pengumuman user.', 'icon' => 'banner'],
            ['route' => 'admin.tutorials.index', 'pattern' => 'admin.tutorials.*', 'label' => 'Tutorial', 'title' => 'Manajemen Tutorial', 'description' => 'Kelola panduan dan tutorial pelanggan.', 'icon' => 'tutorial'],
            ['route' => 'admin.transactions.index', 'pattern' => 'admin.transactions.*', 'label' => 'Transaksi', 'title' => 'Manajemen Transaksi', 'description' => 'Pantau transaksi dan pesanan pelanggan.', 'icon' => 'transaction'],
            ['route' => 'admin.deposits.index', 'pattern' => 'admin.deposits.*', 'label' => 'Deposit', 'title' => 'Manajemen Deposit', 'description' => 'Pantau top-up saldo pengguna.', 'icon' => 'deposit'],
            ['route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'label' => 'Laporan', 'title' => 'Manajemen Laporan', 'description' => 'Pantau laporan kendala pelanggan.', 'icon' => 'report'],
        ];

        $adminNavIconPaths = [
            'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 13.5h6.5V4H4v9.5Zm9.5 6.5H20V4h-6.5v16ZM4 20h6.5v-3.5H4V20Z" />',
            'reseller' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.5 10.5V8A3.5 3.5 0 0 1 12 4.5 3.5 3.5 0 0 1 15.5 8v2.5M5.5 10.5h13l-1 9h-11l-1-9ZM9 14h6" />',
            'commission' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M7 7.5h7.5a3 3 0 0 1 0 6H9.5a3 3 0 0 0 0 6H17" />',
            'member' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.5 4.5h11L21 9l-9 11L3 9l3.5-4.5Z M3 9h18" />',
            'admin' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5 18 6v5.5c0 4-2.45 7.35-6 8.8-3.55-1.45-6-4.8-6-8.8V6l6-2.5Z M9.5 12l1.7 1.7 3.6-3.9" />',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm6.5 9a6.5 6.5 0 0 0-13 0M17 10.5a3 3 0 1 0 0-6M21.5 20a5.5 5.5 0 0 0-4-5.3" />',
            'category' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h6l2 2h8v11H4v-13Z" />',
            'product' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5 20 8v8.5L12 21l-8-4.5V8l8-4.5Z M4 8l8 4.5L20 8 M12 12.5V21" />',
            'banner' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6.5h16v11H4v-11Zm3 7 2.2-2.2 2 2 3.3-3.3L18 13.5" />',
            'tutorial' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.5A2.5 2.5 0 0 1 7 3h11.5v16H7a2.5 2.5 0 0 0-2.5 2.5v-16Zm0 0v16M8.25 7.25h6.5M8.25 10.75h5M8.25 14.25h4" />',
            'transaction' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h16v11H4v-11Zm0 3.5h16M8 15h4" />',
            'deposit' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h15a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-10a2 2 0 0 1 2-2Zm0 0 12-3v3M17 13.5h.01" />',
            'report' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 4.5h14v15H5v-15Zm3.5 4h7M8.5 12h7M8.5 15.5h4" />',
            'profile' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0" />',
            'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1" />',
        ];

        $activeAdminNav = collect($adminNav)->first($adminNavMatches);
        $adminNavbarTitle = $activeAdminNav['title'] ?? $activeAdminNav['label'] ?? trim($__env->yieldContent('title'));
        $adminNavbarSubtitle = $activeAdminNav['description'] ?? trim($__env->yieldContent('admin_header_subtitle'));
        if (request()->routeIs('profile.edit')) {
            $adminNavbarTitle = 'Pengaturan Akun';
            $adminNavbarSubtitle = 'Kelola profil dan keamanan akun.';
        }
        if (! filled($adminNavbarSubtitle)) {
            $adminNavbarSubtitle = 'Kelola data halaman admin.';
        }
        $currentAdmin = Auth::user();
        $adminDefaultPhotoUrl = \App\Models\User::defaultProfilePhotoUrl();
        $adminPhotoUrl = $currentAdmin?->profile_photo_url ?? $adminDefaultPhotoUrl;
        $adminDisplayName = trim((string) ($currentAdmin?->name ?? 'Admin'));
        $adminNameParts = preg_split('/\s+/', $adminDisplayName, -1, PREG_SPLIT_NO_EMPTY);
        $adminShortName = trim(implode(' ', array_slice($adminNameParts ?: ['Admin'], 0, 2))) ?: 'Admin';
    @endphp

    <aside class="wiboost-admin-sidebar fixed inset-y-0 left-0 z-40 hidden w-72 rounded-none lg:block">
        <div class="flex h-full flex-col rounded-none border-r-4 border-white bg-white/90 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-xl">
            <div class="shrink-0 border-b-4 border-dashed border-[#f4f9ff] px-6 py-5">
                <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-3">
                    <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" onerror="this.onerror=null;this.src='{{ asset('images/wiboost-logo.png') }}?v=20260426-full';" alt="Logo Wiboost Store" width="192" height="192" fetchpriority="high" loading="eager" decoding="async" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm transition-transform group-hover:scale-110">
                    <div class="wiboost-sidebar-brand-text min-w-0">
                        <p class="text-xl font-black leading-tight text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                    </div>
                </a>
            </div>

            <nav x-ref="adminSidebarNav" @scroll.debounce.150ms="saveSidebarScroll()" class="hide-scroll flex-1 space-y-2 overflow-y-auto overscroll-contain px-5 py-4">
                @foreach($adminNav as $item)
                    @php($isActiveAdminNav = $adminNavMatches($item))
                    @php($adminIconStyle = $item['icon'] === 'commission' ? 'transform: scaleX(-1); transform-origin: center;' : '')
                    <a href="{{ route($item['route']) }}" @click="saveSidebarScroll()" class="flex items-center gap-3 rounded-full px-6 py-3.5 text-sm font-black transition-all duration-300 {{ $isActiveAdminNav ? 'border-2 border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 scale-[1.02]' : 'border-2 border-transparent text-[#5a76c8] hover:border-white hover:bg-[#f4f9ff] hover:scale-[1.02]' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $isActiveAdminNav ? 'bg-white/20 text-white' : 'bg-white text-[#5a76c8] shadow-sm' }}">
                            <svg class="h-5 w-5" style="{{ $adminIconStyle }}" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">
                                {!! $adminNavIconPaths[$item['icon']] ?? $adminNavIconPaths['dashboard'] !!}
                            </svg>
                        </span>
                        <span class="wiboost-sidebar-link-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="wiboost-admin-sidebar-footer flex shrink-0 items-center gap-3 border-t-4 border-dashed border-[#f4f9ff] p-4">
                <a href="{{ route('profile.edit') }}" class="flex min-w-0 flex-1 items-center gap-3 rounded-[1.5rem] border-2 border-white bg-[#f4f9ff] px-4 py-3 text-[#2b3a67] shadow-sm transition hover:bg-[#e0fbfc]">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 bg-white ring-2 ring-white" style="border-color: #22c55e;">
                        <img src="{{ $adminPhotoUrl }}" onerror="this.onerror=null;this.src='{{ $adminDefaultPhotoUrl }}';" alt="Foto profil {{ $adminShortName }}" class="h-full w-full object-cover">
                    </span>
                    <span class="wiboost-sidebar-profile-text min-w-0">
                        <span class="block truncate text-sm font-black">{{ $adminShortName }}</span>
                        <span class="block text-[10px] font-black uppercase tracking-widest" style="color: #22c55e;">Admin</span>
                    </span>
                </a>
            </div>
        </div>
    </aside>

    <div class="wiboost-admin-navbar pointer-events-none fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-6 lg:px-0 lg:pt-0">
        <div class="pointer-events-auto mx-auto max-w-6xl lg:max-w-none">
            <div class="wiboost-admin-navbar-card flex items-center justify-between gap-3 border-4 border-white bg-white/90 px-4 py-3 shadow-xl shadow-[#bde0fe]/40 backdrop-blur-md sm:px-6 lg:border-x-0 lg:border-t-0 transition-all">
                <button type="button" @click="mobileNavOpen = true; profileMenuOpen = false" aria-label="Buka menu" class="wiboost-admin-mobile-toggle flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition active:scale-95 lg:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>

                <button
                    type="button"
                    @click="desktopSidebarOpen = !desktopSidebarOpen; try { localStorage.setItem('wiboost_admin_desktop_sidebar_open', desktopSidebarOpen ? '1' : '0') } catch (error) {}"
                    :aria-expanded="desktopSidebarOpen.toString()"
                    aria-label="Buka tutup sidebar"
                    class="wiboost-admin-navbar-toggle h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition hover:bg-[#e0fbfc] active:scale-95"
                >
                    <svg class="h-6 w-6 transition-transform duration-300" :class="desktopSidebarOpen ? 'rotate-0' : 'rotate-180'" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 5-7 7 7 7" />
                    </svg>
                </button>

                <div class="min-w-0 flex-1 px-1 sm:px-3">
                    <h1 class="truncate text-lg font-black leading-tight text-[#2b3a67] drop-shadow-sm sm:text-2xl md:text-3xl">{{ $adminNavbarTitle }}</h1>
                    @if($adminNavbarSubtitle)
                        <p class="mt-1 block max-w-full truncate whitespace-nowrap text-[10px] font-bold leading-snug text-[#8faaf3] sm:text-xs">
                            {{ $adminNavbarSubtitle }}
                        </p>
                    @endif
                </div>

                @hasSection('admin_header_actions')
                    <div class="hidden shrink-0 items-center gap-2 lg:flex">
                        @yield('admin_header_actions')
                    </div>
                @endif

                <div class="relative" @click.outside="profileMenuOpen = false">
                    <button type="button" @click="profileMenuOpen = !profileMenuOpen; mobileNavOpen = false" :aria-expanded="profileMenuOpen.toString()" aria-label="Menu profil admin" class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 bg-white shadow-sm ring-2 ring-white transition active:scale-95 sm:h-12 sm:w-12" style="border-color: #22c55e;">
                        <img src="{{ $adminPhotoUrl }}" onerror="this.onerror=null;this.src='{{ $adminDefaultPhotoUrl }}';" alt="Foto profil {{ $adminShortName }}" class="h-full w-full object-cover">
                    </button>

                    <div x-cloak x-show="profileMenuOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="translate-y-1 opacity-0"
                         x-transition:enter-end="translate-y-0 opacity-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="translate-y-0 opacity-100"
                         x-transition:leave-end="translate-y-1 opacity-0"
                         class="absolute right-0 top-full z-[80] mt-3 w-max min-w-56 rounded-[1.5rem] border-4 border-white bg-white p-2 shadow-2xl shadow-[#bde0fe]/50">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 whitespace-nowrap rounded-[1.1rem] px-4 py-3 text-sm font-black text-[#5a76c8] transition hover:bg-[#f4f9ff]">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#f4f9ff]">
                                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">{!! $adminNavIconPaths['profile'] !!}</svg>
                            </span>
                            <span class="whitespace-nowrap">Profil</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 whitespace-nowrap rounded-[1.1rem] px-4 py-3 text-left text-sm font-black text-[#ff6b6b] transition hover:bg-[#fff3f3]">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#ffe5e5]">
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">{!! $adminNavIconPaths['logout'] !!}</svg>
                                </span>
                                <span class="whitespace-nowrap">Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wiboost-admin-content flex min-h-screen min-w-0 flex-1 flex-col px-4 pb-8 sm:px-6 lg:px-8 z-10">
        @hasSection('admin_header_actions')
            <div class="mb-4 flex flex-col gap-2 lg:hidden">
                @yield('admin_header_actions')
            </div>
        @endif

        <main class="flex-1 min-w-0 w-full overflow-hidden">
            @yield('content')
        </main>
    </div>

    <div x-cloak x-show="mobileNavOpen" class="fixed inset-0 z-[70] lg:hidden" aria-modal="true" role="dialog">
        <div x-show="mobileNavOpen" x-transition.opacity class="absolute inset-0 bg-[#2b3a67]/55 backdrop-blur-sm" @click="mobileNavOpen = false"></div>
        
        <div x-show="mobileNavOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="-translate-x-full opacity-0"
             class="wiboost-admin-mobile-menu-panel absolute inset-y-0 left-0 flex w-[min(82vw,20rem)] flex-col border-r-4 border-white bg-white/95 px-5 py-5 shadow-2xl shadow-[#2b3a67]/30 backdrop-blur-xl">
            
            <div class="wiboost-admin-mobile-sidebar-stack min-h-0 flex-1">
                <div class="flex shrink-0 items-center gap-3 border-b-4 border-dashed border-[#d7ecff] pb-4">
                    <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" onerror="this.onerror=null;this.src='{{ asset('images/wiboost-logo.png') }}?v=20260426-full';" alt="Logo Wiboost Store" width="192" height="192" loading="eager" decoding="async" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm">
                    <p class="text-xl font-black leading-tight text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                </div>
                
                <nav class="wiboost-admin-mobile-sidebar-nav hide-scroll flex min-h-0 flex-col gap-2 overflow-y-auto py-4">
                    @foreach($adminNav as $item)
                        @php($isActiveAdminNav = $adminNavMatches($item))
                        @php($adminIconStyle = $item['icon'] === 'commission' ? 'transform: scaleX(-1); transform-origin: center;' : '')
                        <a href="{{ route($item['route']) }}" class="flex items-center rounded-[1.5rem] border-2 {{ $isActiveAdminNav ? 'border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/30' : 'border-transparent bg-[#f8fbff] text-[#5a76c8] hover:border-white hover:bg-[#e0fbfc]' }} px-5 py-3 font-black transition-all">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ $isActiveAdminNav ? 'bg-white/20 shadow-inner' : 'bg-white shadow-sm border border-[#f0f5ff]' }}">
                                <svg class="h-5 w-5" style="{{ $adminIconStyle }}" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">
                                    {!! $adminNavIconPaths[$item['icon']] ?? $adminNavIconPaths['dashboard'] !!}
                                </svg>
                            </span>
                            <span class="ml-4 text-sm uppercase tracking-widest">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="wiboost-admin-mobile-sidebar-footer flex shrink-0 items-center gap-3 border-t-4 border-dashed border-[#d7ecff] pt-4">
                    <a href="{{ route('profile.edit') }}" class="flex min-w-0 flex-1 items-center gap-3 rounded-[1.5rem] border-2 border-white bg-[#f4f9ff] px-4 py-3 text-[#2b3a67] shadow-sm transition hover:bg-[#e0fbfc]">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 bg-white ring-2 ring-white" style="border-color: #22c55e;">
                            <img src="{{ $adminPhotoUrl }}" onerror="this.onerror=null;this.src='{{ $adminDefaultPhotoUrl }}';" alt="Foto profil {{ $adminShortName }}" class="h-full w-full object-cover">
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-black text-[#2b3a67]">{{ $adminShortName }}</span>
                            <span class="block text-[10px] font-black uppercase tracking-widest" style="color: #22c55e;">Admin</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(false)
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
                        <p class="mt-1 block w-full max-w-full truncate whitespace-nowrap text-[10px] sm:text-xs font-bold leading-snug text-[#8faaf3]">
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

    @endif

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
