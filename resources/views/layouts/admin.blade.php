<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $adminPageTitle = trim($__env->yieldContent('title', 'Admin'));
        $adminDocumentTitle = str_contains($adminPageTitle, 'Wiboost Store') ? $adminPageTitle : $adminPageTitle.' - Wiboost Store';
    @endphp
    <title>{{ $adminDocumentTitle }}</title>
    @include('partials.seo-meta', [
        'title' => $adminDocumentTitle,
        'description' => 'Halaman administrasi Wiboost Store.',
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
    <style>
        html { min-height: 100%; overflow-x: hidden; }
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

        body.wiboost-admin-shell main a[title*="Edit"]:not(.wiboost-form-action-button),
        body.wiboost-admin-shell main button[title*="Edit"]:not(.wiboost-form-action-button) {
            color: #d99b16 !important;
            box-shadow: 0 10px 22px rgba(217, 155, 22, .16) !important;
        }

        body.wiboost-admin-shell main a[title*="Edit"]:not(.wiboost-form-action-button):hover,
        body.wiboost-admin-shell main button[title*="Edit"]:not(.wiboost-form-action-button):hover {
            background: #fff5eb !important;
        }

        body.wiboost-admin-shell main a[title*="Hapus"]:not(.wiboost-form-action-button),
        body.wiboost-admin-shell main button[title*="Hapus"]:not(.wiboost-form-action-button) {
            color: #ff4d4d !important;
            box-shadow: 0 10px 22px rgba(255, 107, 107, .18) !important;
        }

        body.wiboost-admin-shell main a[title*="Hapus"]:not(.wiboost-form-action-button):hover,
        body.wiboost-admin-shell main button[title*="Hapus"]:not(.wiboost-form-action-button):hover {
            background: #ffe5e5 !important;
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
            html {
                height: auto !important;
                min-height: 100% !important;
                overflow-y: auto !important;
            }

            body.wiboost-admin-shell {
                display: block !important;
                height: auto !important;
                min-height: 100dvh !important;
                max-height: none !important;
                overflow-x: hidden !important;
                overflow-y: auto !important;
                overscroll-behavior-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
                touch-action: pan-y !important;
            }

            body.wiboost-admin-shell:not(.admin-detail-open) {
                height: auto !important;
                min-height: 100dvh !important;
                overflow-y: auto !important;
                touch-action: pan-y !important;
            }

            body.wiboost-admin-shell .wiboost-admin-navbar-card {
                min-height: 4.75rem;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            body.wiboost-admin-shell .wiboost-admin-content {
                display: block !important;
                height: auto !important;
                min-height: calc(100dvh - 6rem) !important;
                max-height: none !important;
                overflow: visible !important;
                padding-top: 6rem !important;
                padding-bottom: max(2rem, env(safe-area-inset-bottom)) !important;
            }

            body.wiboost-admin-shell main {
                display: block !important;
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow: visible !important;
                contain: none !important;
                touch-action: pan-y !important;
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
                overscroll-behavior: auto !important;
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

        body.wiboost-admin-shell main table tbody td {
            vertical-align: middle !important;
        }

        body.wiboost-admin-shell main nav[role="navigation"] {
            max-width: 100%;
            overflow-x: auto;
            scrollbar-width: none;
        }

        body.wiboost-admin-shell main nav[role="navigation"]::-webkit-scrollbar {
            display: none;
        }

        body.wiboost-admin-shell main nav[role="navigation"] > div {
            min-width: max-content;
            flex-wrap: nowrap !important;
        }

        body.wiboost-admin-shell .admin-theme-select {
            position: relative;
            min-width: 0;
        }

        body.wiboost-admin-shell .admin-theme-select.is-open {
            z-index: 5000;
        }

        body.wiboost-admin-shell .admin-theme-select-button {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            min-height: 2.75rem;
            border: 4px solid #fff;
            background: #f4f9ff;
            color: #2b3a67;
            box-shadow: inset 0 2px 8px rgba(143, 170, 243, .16), 0 8px 18px rgba(189, 224, 254, .18);
            transition: border-color .18s ease, background .18s ease, transform .18s ease;
        }

        body.wiboost-admin-shell .admin-theme-select-button [data-admin-theme-select-label] {
            min-width: 0;
            flex: 1 1 auto;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        body.wiboost-admin-shell .admin-theme-select-button:hover,
        body.wiboost-admin-shell .admin-theme-select.is-open .admin-theme-select-button {
            border-color: #bde0fe;
            background: #eef8ff;
        }

        body.wiboost-admin-shell .admin-theme-select-arrow {
            display: inline-flex;
            flex: 0 0 .5rem;
            width: .5rem;
            height: .5rem;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            color: #5a76c8;
            font-size: 0;
            line-height: 0;
            transform: rotate(45deg) translate(-1px, -1px);
            transform-origin: center;
            transition: transform .18s ease;
        }

        body.wiboost-admin-shell .admin-theme-select.is-open .admin-theme-select-arrow {
            transform: rotate(225deg) translate(-1px, -1px);
        }

        body.wiboost-admin-shell .admin-theme-select-menu {
            position: absolute;
            z-index: 5200;
            left: 0;
            right: 0;
            top: calc(100% + .45rem);
            min-width: 100%;
            max-height: 16rem;
            overflow-y: auto;
            border: 4px solid #fff;
            border-radius: 1.1rem;
            background: #fff;
            padding: .35rem;
            box-shadow: 0 18px 40px rgba(90, 118, 200, .22);
        }

        body.wiboost-admin-shell .admin-theme-select-menu.is-floating-open {
            display: block !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            transform: none !important;
        }

        body.wiboost-admin-shell .admin-theme-select:not(.is-open) .admin-theme-select-menu {
            display: none;
        }

        body.wiboost-admin-shell .admin-theme-select-option {
            width: 100%;
            display: block;
            border-radius: .85rem;
            padding: .78rem .9rem;
            text-align: left;
            font-size: .72rem;
            font-weight: 900;
            color: #2b3a67;
            white-space: nowrap;
            transition: background .16s ease, color .16s ease;
        }

        body.wiboost-admin-shell .admin-theme-select-option + .admin-theme-select-option {
            margin-top: .45rem;
        }

        body.wiboost-admin-shell .admin-theme-select-option:hover,
        body.wiboost-admin-shell .admin-theme-select-option.is-selected {
            background: #e0fbfc;
            color: #5a76c8;
        }

        body.wiboost-admin-shell .table-scroll tr:has(.admin-theme-select.is-open) {
            position: relative;
            z-index: 5100;
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
                contain: none !important;
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
                    const sidebarTargets = [
                        ['wiboost-admin-sidebar-nav', 'wiboost-admin-sidebar-scroll'],
                        ['wiboost-admin-mobile-sidebar-nav', 'wiboost-admin-mobile-sidebar-scroll'],
                    ];
                    const applyScroll = () => {
                        sidebarTargets.forEach(([id, key]) => {
                            const nav = document.getElementById(id);
                            const savedScroll = Number(window.localStorage.getItem(key) || 0);

                            if (nav && Number.isFinite(savedScroll)) {
                                nav.scrollTop = savedScroll;
                            }
                        });
                    };
                    applyScroll();
                    window.requestAnimationFrame(applyScroll);
                    window.setTimeout(applyScroll, 90);
                    window.setTimeout(applyScroll, 160);
                    window.setTimeout(applyScroll, 260);
                } catch (error) {}
            });
        },
        saveSidebarScroll(event = null) {
            try {
                const sidebarTargets = [
                    ['wiboost-admin-sidebar-nav', 'wiboost-admin-sidebar-scroll'],
                    ['wiboost-admin-mobile-sidebar-nav', 'wiboost-admin-mobile-sidebar-scroll'],
                ];

                sidebarTargets.forEach(([id, key]) => {
                    const nav = document.getElementById(id);

                    if (nav) {
                        window.localStorage.setItem(key, String(nav.scrollTop || 0));
                    }
                });

                const current = event?.currentTarget;
                if (current?.id === 'wiboost-admin-sidebar-nav') {
                    window.localStorage.setItem('wiboost-admin-sidebar-scroll', String(current.scrollTop || 0));
                }

                if (current?.id === 'wiboost-admin-mobile-sidebar-nav') {
                    window.localStorage.setItem('wiboost-admin-mobile-sidebar-scroll', String(current.scrollTop || 0));
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
        $currentAdmin = Auth::user();
        $currentAdminRoleId = (int) ($currentAdmin?->role_id ?? 0);
        $adminRoleLabels = [
            1 => 'Super Admin',
            4 => 'Keuangan',
            6 => 'Gudang',
            7 => 'Marketing',
            8 => 'Customer Service',
        ];
        $adminRoleLabel = $adminRoleLabels[$currentAdminRoleId] ?? 'Admin';
        $adminProfileBorderColors = [
            1 => '#22c55e',
            4 => '#8b5a2b',
            6 => '#111827',
            7 => '#991b1b',
            8 => '#ec4899',
        ];
        $adminProfileBorderColor = $adminProfileBorderColors[$currentAdminRoleId] ?? '#22c55e';
        $adminCanAccessArea = function (string $area) use ($currentAdminRoleId): bool {
            return in_array($currentAdminRoleId, \App\Models\User::ADMIN_ROLE_IDS, true);
        };

        $adminNavMatches = function (array $item): bool {
            if ($item['route'] === 'admin.products.index' && request()->routeIs('admin.credentials.*')) {
                return true;
            }

            return request()->routeIs(...(array) $item['pattern']);
        };

        $adminNav = [
            ['access' => 'dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'description' => 'Ringkasan transaksi dan aktivitas toko.', 'icon' => 'dashboard'],
            ['access' => 'users', 'route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'label' => 'Pengguna', 'title' => 'Manajemen Pengguna', 'description' => 'Kelola data pengguna dan role akun.', 'icon' => 'users'],
            ['access' => 'categories', 'route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Kategori', 'title' => 'Manajemen Kategori', 'description' => 'Atur kategori layanan dan produk.', 'icon' => 'category'],
            ['access' => 'products', 'route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => 'Produk', 'title' => 'Manajemen Produk', 'description' => 'Kelola harga, stok, dan provider.', 'icon' => 'product'],
            ['access' => 'durations', 'route' => 'admin.durations.index', 'pattern' => 'admin.durations.*', 'label' => 'Durasi', 'title' => 'Durasi Produk', 'description' => 'Pantau produk aplikasi premium yang masa aksesnya sudah habis.', 'icon' => 'duration'],
            ['access' => 'transactions', 'route' => 'admin.transactions.index', 'pattern' => 'admin.transactions.*', 'label' => 'Transaksi', 'title' => 'Manajemen Transaksi', 'description' => 'Pantau transaksi dan pesanan pelanggan.', 'icon' => 'transaction'],
            ['access' => 'refunds', 'route' => 'admin.refunds.index', 'pattern' => 'admin.refunds.*', 'label' => 'Refund', 'title' => 'Manajemen Refund', 'description' => 'Catat refund transaksi ke saldo user.', 'icon' => 'refund'],
            ['access' => 'reports', 'route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'label' => 'Laporan', 'title' => 'Manajemen Laporan', 'description' => 'Pantau laporan kendala pelanggan.', 'icon' => 'report'],
            ['access' => 'deposits', 'route' => 'admin.deposits.index', 'pattern' => 'admin.deposits.*', 'label' => 'Deposit', 'title' => 'Manajemen Deposit', 'description' => 'Pantau top-up dan refund saldo pengguna.', 'icon' => 'deposit'],
            ['access' => 'commissions', 'route' => 'admin.commissions.index', 'pattern' => 'admin.commissions.*', 'label' => 'Komisi', 'title' => 'Manajemen Komisi', 'description' => 'Pantau komisi masuk reseller.', 'icon' => 'commission'],
            ['access' => 'withdrawals', 'route' => 'admin.withdrawals.index', 'pattern' => 'admin.withdrawals.*', 'label' => 'Penarikan', 'title' => 'Manajemen Penarikan', 'description' => 'Konfirmasi penarikan reseller setelah transfer.', 'icon' => 'withdrawal'],
            ['access' => 'promos', 'route' => 'admin.promos.index', 'pattern' => 'admin.promos.*', 'label' => 'Banner', 'title' => 'Manajemen Banner', 'description' => 'Kelola banner dan pengumuman user.', 'icon' => 'banner'],
            ['access' => 'displays', 'route' => 'admin.displays.index', 'pattern' => 'admin.displays.*', 'label' => 'Display', 'title' => 'Manajemen Display', 'description' => 'Kelola display popup user.', 'icon' => 'display'],
            ['access' => 'forms', 'route' => 'admin.forms.index', 'pattern' => 'admin.forms.*', 'label' => 'Formulir', 'title' => 'Manajemen Formulir', 'description' => 'Kelola form publik dan benefit aplikasi premium.', 'icon' => 'forms'],
            ['access' => 'emails', 'route' => 'admin.emails.index', 'pattern' => 'admin.emails.*', 'label' => 'Email', 'title' => 'Manajemen Email', 'description' => 'Kirim informasi kepada pengguna.', 'icon' => 'email'],
            ['access' => 'tutorials', 'route' => 'admin.tutorials.index', 'pattern' => 'admin.tutorials.*', 'label' => 'Tutorial', 'title' => 'Manajemen Tutorial', 'description' => 'Kelola panduan dan tutorial pelanggan.', 'icon' => 'tutorial'],
            ['access' => 'logs', 'route' => 'admin.logs.index', 'pattern' => 'admin.logs.*', 'label' => 'Log Aktivitas', 'title' => 'Log Aktivitas', 'description' => 'Aktivitas & lokasi (IP) seluruh pengunjung situs.', 'icon' => 'log'],
        ];

        $adminNav = array_values(array_filter($adminNav, fn (array $item) => $adminCanAccessArea($item['access'])));
        $adminNavByAccess = collect($adminNav)->keyBy('access');
        $adminNavGroups = collect([
            ['label' => 'Utama', 'items' => ['dashboard']],
            ['label' => 'Operasional', 'items' => ['transactions', 'reports', 'refunds', 'deposits']],
            ['label' => 'Produk & Gudang', 'items' => ['categories', 'products', 'durations', 'tutorials']],
            ['label' => 'Akun & Reseller', 'items' => ['users', 'commissions', 'withdrawals']],
            ['label' => 'Konten', 'items' => ['promos', 'displays', 'forms', 'emails']],
            ['label' => 'Sistem', 'items' => ['logs']],
        ])->map(function (array $group) use ($adminNavByAccess) {
            $group['items'] = collect($group['items'])
                ->map(fn (string $access) => $adminNavByAccess->get($access))
                ->filter()
                ->values()
                ->all();

            return $group;
        })->filter(fn (array $group) => count($group['items']) > 0)->values()->all();

        $adminNavIconPaths = [
            'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 13.5h6.5V4H4v9.5Zm9.5 6.5H20V4h-6.5v16ZM4 20h6.5v-3.5H4V20Z" />',
            'reseller' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.5 10.5V8A3.5 3.5 0 0 1 12 4.5 3.5 3.5 0 0 1 15.5 8v2.5M5.5 10.5h13l-1 9h-11l-1-9ZM9 14h6" />',
            'commission' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M7 7.5h7.5a3 3 0 0 1 0 6H9.5a3 3 0 0 0 0 6H17" />',
            'withdrawal' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 4.5h14A2.5 2.5 0 0 1 21.5 7v10A2.5 2.5 0 0 1 19 19.5H5A2.5 2.5 0 0 1 2.5 17V7A2.5 2.5 0 0 1 5 4.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 8h11M7.5 12h3M13.5 12h3M8 15.5h8" /><path stroke-linecap="round" stroke-linejoin="round" d="M10 19.5v1.5M14 19.5v1.5" />',
            'member' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.5 4.5h11L21 9l-9 11L3 9l3.5-4.5Z M3 9h18" />',
            'admin' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5 18 6v5.5c0 4-2.45 7.35-6 8.8-3.55-1.45-6-4.8-6-8.8V6l6-2.5Z M9.5 12l1.7 1.7 3.6-3.9" />',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm6.5 9a6.5 6.5 0 0 0-13 0M17 10.5a3 3 0 1 0 0-6M21.5 20a5.5 5.5 0 0 0-4-5.3" />',
            'category' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h6l2 2h8v11H4v-13Z" />',
            'product' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5 20 8v8.5L12 21l-8-4.5V8l8-4.5Z M4 8l8 4.5L20 8 M12 12.5V21" />',
            'duration' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14A2.5 2.5 0 0 1 21.5 8v10A2.5 2.5 0 0 1 19 20.5H5A2.5 2.5 0 0 1 2.5 18V8A2.5 2.5 0 0 1 5 5.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 3.5v4M16 3.5v4M2.5 10h19M12 13v3l2 1.3" />',
            'banner' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6.5h16v11H4v-11Zm3 7 2.2-2.2 2 2 3.3-3.3L18 13.5" />',
            'display' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.5h15v10h-15v-10Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 19h8M10 16.5 9.5 19M14 16.5l.5 2.5" />',
            'forms' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 3.5h9l3 3v14H6v-17Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 3.5v3h3M9 9.5h6M9 13h6M9 16.5h4" />',
            'email' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.5 6.5h17v12h-17v-12Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8 6 8-6" />',
            'tutorial' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.5A2.5 2.5 0 0 1 7 3h11.5v16H7a2.5 2.5 0 0 0-2.5 2.5v-16Zm0 0v16M8.25 7.25h6.5M8.25 10.75h5M8.25 14.25h4" />',
            'transaction' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h16v11H4v-11Zm0 3.5h16M8 15h4" />',
            'refund' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.2 7.1A7.2 7.2 0 0 1 18.5 9.2" /><path stroke-linecap="round" stroke-linejoin="round" d="M18.5 5.6v3.6h-3.6" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.8 16.9A7.2 7.2 0 0 1 5.5 14.8" /><path stroke-linecap="round" stroke-linejoin="round" d="M5.5 18.4v-3.6h3.6" />',
            'deposit' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h15a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-10a2 2 0 0 1 2-2Zm0 0 12-3v3M17 13.5h.01" />',
            'report' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 4.5h14v15H5v-15Zm3.5 4h7M8.5 12h7M8.5 15.5h4" />',
            'log' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5h6a2 2 0 0 1 2 2V19a1.5 1.5 0 0 1-1.5 1.5H8.5A1.5 1.5 0 0 1 7 19V6.5a2 2 0 0 1 2-2Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 8.5h5M9.5 12h5M9.5 15.5h3" />',
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
                    <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" alt="Logo Wiboost Store" width="192" height="192" fetchpriority="high" loading="eager" decoding="async" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm transition-transform group-hover:scale-110">
                    <div class="wiboost-sidebar-brand-text min-w-0">
                        <p class="text-xl font-black leading-tight text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                    </div>
                </a>
            </div>

            <nav id="wiboost-admin-sidebar-nav" x-ref="adminSidebarNav" @scroll.debounce.100ms="saveSidebarScroll()" class="hide-scroll flex-1 overflow-y-auto overscroll-contain px-5 py-4">
                @foreach($adminNavGroups as $group)
                    <div class="py-3 first:pt-0">
                        <div class="flex items-center gap-3 px-6 pb-2">
                            <p class="wiboost-sidebar-link-label shrink-0 text-[9px] font-black uppercase tracking-[0.25em] text-[#8faaf3]">{{ $group['label'] }}</p>
                        </div>
                        <div class="space-y-2">
                            @foreach($group['items'] as $item)
                                <?php $isActiveAdminNav = $adminNavMatches($item); ?>
                                <?php $adminIconStyle = $item['icon'] === 'commission' ? 'transform: scaleX(-1); transform-origin: center;' : ''; ?>
                                <a href="{{ route($item['route']) }}" data-admin-sidebar-link @click="saveSidebarScroll()" class="flex items-center gap-3 rounded-full px-6 py-3.5 text-sm font-black transition-all duration-300 {{ $isActiveAdminNav ? 'border-2 border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 scale-[1.02]' : 'border-2 border-transparent text-[#5a76c8] hover:border-white hover:bg-[#f4f9ff] hover:scale-[1.02]' }}">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $isActiveAdminNav ? 'bg-white/20 text-white' : 'bg-white text-[#5a76c8] shadow-sm' }}">
                                        <svg class="h-5 w-5" style="{{ $adminIconStyle }}" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">
                                            {!! $adminNavIconPaths[$item['icon']] ?? $adminNavIconPaths['dashboard'] !!}
                                        </svg>
                                    </span>
                                    <span class="wiboost-sidebar-link-label">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            <div class="wiboost-admin-sidebar-footer flex shrink-0 items-center gap-3 border-t-4 border-dashed border-[#f4f9ff] p-4">
                <a href="{{ route('profile.edit') }}" class="flex min-w-0 flex-1 items-center gap-3 rounded-[1.5rem] border-2 border-white bg-[#f4f9ff] px-4 py-3 text-[#2b3a67] shadow-sm transition hover:bg-[#e0fbfc]">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 bg-white ring-2 ring-white" style="border-color: {{ $adminProfileBorderColor }};">
                        <img src="{{ $adminPhotoUrl }}" onerror="this.onerror=null;this.src='{{ $adminDefaultPhotoUrl }}';" alt="Foto profil {{ $adminShortName }}" class="h-full w-full object-cover">
                    </span>
                    <span class="wiboost-sidebar-profile-text min-w-0">
                        <span class="block truncate text-sm font-black">{{ $adminShortName }}</span>
                        <span class="block text-[10px] font-black uppercase tracking-widest" style="color: {{ $adminProfileBorderColor }};">{{ $adminRoleLabel }}</span>
                    </span>
                </a>
            </div>
        </div>
    </aside>

    <div class="wiboost-admin-navbar pointer-events-none fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-6 lg:px-0 lg:pt-0">
        <div class="pointer-events-auto mx-auto max-w-6xl lg:max-w-none">
            <div class="wiboost-admin-navbar-card flex items-center justify-between gap-3 border-4 border-white bg-white/90 px-4 py-3 shadow-xl shadow-[#bde0fe]/40 backdrop-blur-md sm:px-6 lg:border-x-0 lg:border-t-0 transition-all">
                <button type="button" @click="mobileNavOpen = true; profileMenuOpen = false; restoreSidebarScroll(); window.wiboostRestoreAdminSidebarScroll?.(); setTimeout(() => window.wiboostRestoreAdminSidebarScroll?.(), 120); setTimeout(() => window.wiboostRestoreAdminSidebarScroll?.(), 320)" aria-label="Buka menu" class="wiboost-admin-mobile-toggle flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition active:scale-95 lg:hidden">
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
                    <button type="button" @click="profileMenuOpen = !profileMenuOpen; mobileNavOpen = false" :aria-expanded="profileMenuOpen.toString()" aria-label="Menu profil admin" class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 bg-white shadow-sm ring-2 ring-white transition active:scale-95 sm:h-12 sm:w-12" style="border-color: {{ $adminProfileBorderColor }};">
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
                    <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" alt="Logo Wiboost Store" width="192" height="192" loading="eager" decoding="async" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm">
                    <p class="text-xl font-black leading-tight text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                </div>
                
                <nav id="wiboost-admin-mobile-sidebar-nav" @scroll.debounce.100ms="saveSidebarScroll($event)" class="wiboost-admin-mobile-sidebar-nav hide-scroll flex min-h-0 flex-col overflow-y-auto py-4">
                    @foreach($adminNavGroups as $group)
                        <div class="py-3 first:pt-0">
                            <div class="flex items-center gap-3 px-3 pb-2">
                                <p class="shrink-0 text-[9px] font-black uppercase tracking-[0.25em] text-[#8faaf3]">{{ $group['label'] }}</p>
                            </div>
                            <div class="space-y-2">
                                @foreach($group['items'] as $item)
                                    <?php $isActiveAdminNav = $adminNavMatches($item); ?>
                                    <?php $adminIconStyle = $item['icon'] === 'commission' ? 'transform: scaleX(-1); transform-origin: center;' : ''; ?>
                                    <a href="{{ route($item['route']) }}" data-admin-sidebar-link @click="saveSidebarScroll($event)" class="flex items-center rounded-[1.5rem] border-2 {{ $isActiveAdminNav ? 'border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/30' : 'border-transparent bg-[#f8fbff] text-[#5a76c8] hover:border-white hover:bg-[#e0fbfc]' }} px-5 py-3 font-black transition-all">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ $isActiveAdminNav ? 'bg-white/20 shadow-inner' : 'bg-white shadow-sm border border-[#f0f5ff]' }}">
                                            <svg class="h-5 w-5" style="{{ $adminIconStyle }}" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">
                                                {!! $adminNavIconPaths[$item['icon']] ?? $adminNavIconPaths['dashboard'] !!}
                                            </svg>
                                        </span>
                                        <span class="ml-4 text-sm uppercase tracking-widest">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>

                <div class="wiboost-admin-mobile-sidebar-footer flex shrink-0 items-center gap-3 border-t-4 border-dashed border-[#d7ecff] pt-4">
                    <a href="{{ route('profile.edit') }}" class="flex min-w-0 flex-1 items-center gap-3 rounded-[1.5rem] border-2 border-white bg-[#f4f9ff] px-4 py-3 text-[#2b3a67] shadow-sm transition hover:bg-[#e0fbfc]">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 bg-white ring-2 ring-white" style="border-color: {{ $adminProfileBorderColor }};">
                            <img src="{{ $adminPhotoUrl }}" onerror="this.onerror=null;this.src='{{ $adminDefaultPhotoUrl }}';" alt="Foto profil {{ $adminShortName }}" class="h-full w-full object-cover">
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-black text-[#2b3a67]">{{ $adminShortName }}</span>
                            <span class="block text-[10px] font-black uppercase tracking-widest" style="color: {{ $adminProfileBorderColor }};">{{ $adminRoleLabel }}</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.wiboostResetFloatingMenu = window.wiboostResetFloatingMenu || function (dropdown, menuSelector = '.admin-theme-select-menu') {
            const menu = dropdown.querySelector(menuSelector) || dropdown._wiboostSharedFloatingMenu;

            if (!menu) {
                return;
            }

            if (dropdown._wiboostSharedFloatingMenuParent && menu.parentElement !== dropdown._wiboostSharedFloatingMenuParent) {
                if (dropdown._wiboostSharedFloatingMenuNext && dropdown._wiboostSharedFloatingMenuNext.parentElement === dropdown._wiboostSharedFloatingMenuParent) {
                    dropdown._wiboostSharedFloatingMenuParent.insertBefore(menu, dropdown._wiboostSharedFloatingMenuNext);
                } else {
                    dropdown._wiboostSharedFloatingMenuParent.appendChild(menu);
                }
            }

            menu.style.position = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.top = '';
            menu.style.bottom = '';
            menu.style.width = '';
            menu.style.minWidth = '';
            menu.style.maxHeight = '';
            menu.style.zIndex = '';
            menu.style.display = '';
            menu.classList.remove('is-floating-open');
        };

        window.wiboostPositionFloatingMenu = window.wiboostPositionFloatingMenu || function (dropdown, menuSelector = '.admin-theme-select-menu', minimumWidth = 152, maximumHeight = 288) {
            const menu = dropdown.querySelector(menuSelector) || dropdown._wiboostSharedFloatingMenu;

            if (!menu) {
                return;
            }

            if (!dropdown._wiboostSharedFloatingMenuParent) {
                dropdown._wiboostSharedFloatingMenuParent = menu.parentElement;
                dropdown._wiboostSharedFloatingMenuNext = menu.nextSibling;
                dropdown._wiboostSharedFloatingMenu = menu;
            }

            if (menu.parentElement !== document.body) {
                document.body.appendChild(menu);
            }

            const rect = dropdown.getBoundingClientRect();
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
            const gap = 8;
            menu.style.display = 'block';
            menu.classList.add('is-floating-open');
            const desiredHeight = Math.min(menu.scrollHeight || maximumHeight, maximumHeight);
            const openUp = viewportHeight - rect.bottom < Math.min(desiredHeight, 160) && rect.top > desiredHeight;
            const availableHeight = openUp
                ? Math.max(120, rect.top - (gap * 2))
                : Math.max(120, viewportHeight - rect.bottom - (gap * 2));
            const width = Math.max(rect.width, minimumWidth);
            const left = Math.min(Math.max(gap, rect.left), Math.max(gap, viewportWidth - width - gap));

            menu.style.position = 'fixed';
            menu.style.left = `${left}px`;
            menu.style.right = 'auto';
            menu.style.width = `${width}px`;
            menu.style.minWidth = `${width}px`;
            menu.style.maxHeight = `${Math.min(maximumHeight, availableHeight)}px`;
            menu.style.bottom = 'auto';
            menu.style.top = openUp
                ? `${Math.max(gap, rect.top - Math.min(desiredHeight, availableHeight) - gap)}px`
                : `${Math.min(viewportHeight - gap, rect.bottom + gap)}px`;
            menu.style.zIndex = '100000';
        };

        document.addEventListener('DOMContentLoaded', function () {
            const releaseStaleAdminScrollLock = () => {
                const hasOpenDetail = document.body.classList.contains('admin-detail-open');

                if (hasOpenDetail) {
                    return;
                }

                document.body.classList.remove('overflow-hidden');
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
            };

            releaseStaleAdminScrollLock();
            window.addEventListener('pageshow', releaseStaleAdminScrollLock);
            window.addEventListener('resize', releaseStaleAdminScrollLock);

            const sidebarTargets = [
                ['wiboost-admin-sidebar-nav', 'wiboost-admin-sidebar-scroll'],
                ['wiboost-admin-mobile-sidebar-nav', 'wiboost-admin-mobile-sidebar-scroll'],
            ];
            const restoreAdminSidebarScroll = () => {
                sidebarTargets.forEach(([id, key]) => {
                    const nav = document.getElementById(id);
                    const saved = Number(window.localStorage.getItem(key) || 0);

                    if (nav && Number.isFinite(saved)) {
                        nav.scrollTop = saved;
                    }
                });
            };
            const saveAdminSidebarScroll = () => {
                sidebarTargets.forEach(([id, key]) => {
                    const nav = document.getElementById(id);

                    if (nav) {
                        window.localStorage.setItem(key, String(nav.scrollTop || 0));
                    }
                });
            };

            try {
                window.wiboostRestoreAdminSidebarScroll = restoreAdminSidebarScroll;
                window.wiboostSaveAdminSidebarScroll = saveAdminSidebarScroll;
                restoreAdminSidebarScroll();
                window.requestAnimationFrame(restoreAdminSidebarScroll);
                window.setTimeout(restoreAdminSidebarScroll, 100);
                window.setTimeout(restoreAdminSidebarScroll, 300);
                window.addEventListener('pageshow', restoreAdminSidebarScroll);
                window.addEventListener('beforeunload', saveAdminSidebarScroll);
                sidebarTargets.forEach(([id]) => {
                    const nav = document.getElementById(id);

                    if (nav) {
                        nav.addEventListener('scroll', saveAdminSidebarScroll, { passive: true });
                    }
                });
                document.querySelectorAll('[data-admin-sidebar-link]').forEach((link) => {
                    link.addEventListener('click', saveAdminSidebarScroll, { capture: true });
                });
            } catch (error) {}

            document.addEventListener('click', function (event) {
                const backLink = event.target.closest('a[data-wiboost-back], a[aria-label="Kembali"]');

                if (!backLink || event.defaultPrevented) {
                    return;
                }

                backLink.classList.add('is-backing');
            }, true);

            document.addEventListener('click', function (event) {
                const link = event.target.closest('a[href]');

                if (!link || event.defaultPrevented || link.target === '_blank' || link.hasAttribute('download')) {
                    return;
                }

                try {
                    const url = new URL(link.href, window.location.origin);

                    if (url.origin !== window.location.origin || !url.pathname.startsWith('/admin/')) {
                        return;
                    }

                    const isEditorLink = url.pathname.endsWith('/create')
                        || /\/edit$/.test(url.pathname)
                        || /^\/admin\/products\/[^/]+\/credentials$/.test(url.pathname);

                    if (!isEditorLink || url.searchParams.has('return_to')) {
                        return;
                    }

                    url.searchParams.set('return_to', window.location.href);
                    link.href = url.href;
                } catch (error) {}
            }, true);

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

            const resetThemeMenu = (dropdown) => {
                const menu = dropdown.querySelector('.admin-theme-select-menu') || dropdown._wiboostThemeMenu;

                if (!menu) {
                    return;
                }

                if (dropdown._wiboostThemeMenuParent && menu.parentElement !== dropdown._wiboostThemeMenuParent) {
                    if (dropdown._wiboostThemeMenuNext && dropdown._wiboostThemeMenuNext.parentElement === dropdown._wiboostThemeMenuParent) {
                        dropdown._wiboostThemeMenuParent.insertBefore(menu, dropdown._wiboostThemeMenuNext);
                    } else {
                        dropdown._wiboostThemeMenuParent.appendChild(menu);
                    }
                }

                menu.style.position = '';
                menu.style.left = '';
                menu.style.right = '';
                menu.style.top = '';
                menu.style.bottom = '';
                menu.style.width = '';
                menu.style.minWidth = '';
                menu.style.maxHeight = '';
                menu.style.zIndex = '';
                menu.style.display = '';
                menu.classList.remove('is-floating-open');
            };

            const positionThemeMenu = (dropdown) => {
                const menu = dropdown.querySelector('.admin-theme-select-menu') || dropdown._wiboostThemeMenu;

                if (!menu) {
                    return;
                }

                if (!dropdown._wiboostThemeMenuParent) {
                    dropdown._wiboostThemeMenuParent = menu.parentElement;
                    dropdown._wiboostThemeMenuNext = menu.nextSibling;
                    dropdown._wiboostThemeMenu = menu;
                }

                if (menu.parentElement !== document.body) {
                    document.body.appendChild(menu);
                }

                const rect = dropdown.getBoundingClientRect();
                const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
                const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
                const gap = 8;
                const maxHeight = 256;
                menu.style.display = 'block';
                menu.classList.add('is-floating-open');
                const desiredHeight = Math.min(menu.scrollHeight || maxHeight, maxHeight);
                const openUp = viewportHeight - rect.bottom < Math.min(desiredHeight, 160) && rect.top > desiredHeight;
                const availableHeight = openUp
                    ? Math.max(120, rect.top - (gap * 2))
                    : Math.max(120, viewportHeight - rect.bottom - (gap * 2));
                const width = Math.max(rect.width, 152);
                const left = Math.min(Math.max(gap, rect.left), Math.max(gap, viewportWidth - width - gap));

                menu.style.position = 'fixed';
                menu.style.left = `${left}px`;
                menu.style.right = 'auto';
                menu.style.width = `${width}px`;
                menu.style.minWidth = `${width}px`;
                menu.style.maxHeight = `${Math.min(maxHeight, availableHeight)}px`;
                menu.style.bottom = 'auto';
                menu.style.top = openUp
                    ? `${Math.max(gap, rect.top - Math.min(desiredHeight, availableHeight) - gap)}px`
                    : `${Math.min(viewportHeight - gap, rect.bottom + gap)}px`;
                menu.style.zIndex = '100000';
            };

            const closeThemeDropdowns = (except = null) => {
                document.querySelectorAll('[data-admin-theme-select][data-input-name].is-open').forEach((dropdown) => {
                    if (dropdown !== except) {
                        dropdown.classList.remove('is-open');
                        resetThemeMenu(dropdown);
                    }
                });
            };

            document.querySelectorAll('[data-admin-theme-select]').forEach((dropdown) => {
                if (!dropdown.dataset.inputName) {
                    return;
                }

                if (dropdown.dataset.adminThemeSelectReady === 'true') {
                    return;
                }

                dropdown.dataset.adminThemeSelectReady = 'true';
                const button = dropdown.querySelector('.admin-theme-select-button');
                const label = dropdown.querySelector('[data-admin-theme-select-label]');
                const inputName = dropdown.dataset.inputName;
                const form = dropdown.closest('form');
                const input = form?.querySelector(`input[type="hidden"][name="${inputName}"]`);

                button?.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    const willOpen = ! dropdown.classList.contains('is-open');
                    closeThemeDropdowns(dropdown);
                    dropdown.classList.toggle('is-open', willOpen);
                    if (willOpen) {
                        window.requestAnimationFrame(() => positionThemeMenu(dropdown));
                    } else {
                        resetThemeMenu(dropdown);
                    }
                });

                dropdown.querySelectorAll('[data-value]').forEach((option) => {
                    option.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();

                        if (input) {
                            input.value = option.dataset.value ?? '';
                        }

                        if (label) {
                            label.textContent = option.dataset.label || option.textContent.trim();
                        }

                        dropdown.querySelectorAll('[data-value]').forEach((item) => item.classList.remove('is-selected'));
                        option.classList.add('is-selected');
                        dropdown.classList.remove('is-open');
                        resetThemeMenu(dropdown);

                        if (dropdown.dataset.submitOnChange === 'true' && form) {
                            form.submit();
                        }
                    });
                });
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('[data-admin-theme-select][data-input-name]') && !event.target.closest('.admin-theme-select-menu.is-floating-open')) {
                    closeThemeDropdowns();
                }
            });
            window.addEventListener('resize', () => {
                document.querySelectorAll('[data-admin-theme-select][data-input-name].is-open').forEach(positionThemeMenu);
            });
            window.addEventListener('scroll', () => {
                document.querySelectorAll('[data-admin-theme-select][data-input-name].is-open').forEach(positionThemeMenu);
            }, true);
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

            const returnTo = new URLSearchParams(window.location.search).get('return_to');
            if (returnTo && !form.querySelector('input[name="return_to"]')) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'return_to';
                input.value = returnTo;
                form.appendChild(input);
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
    @include('admin.partials.detail-modal-assets')
    @include('partials.wiboost-input-guards')
    @include('partials.page-loading')
    @include('partials.hut-ri-decoration')
    @include('partials.tv-safe')
</body>
</html>
