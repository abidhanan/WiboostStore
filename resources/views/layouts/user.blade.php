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
        :root {
            --wiboost-user-topbar-height: 6.25rem;
            --wiboost-user-mobile-navbar-height: 4.75rem;
        }

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

        body.wiboost-user-shell .wiboost-user-navbar {
            padding-left: 0 !important;
            padding-right: 0 !important;
            padding-top: 0 !important;
        }

        body.wiboost-user-shell .wiboost-user-navbar > div {
            width: 100% !important;
            max-width: none !important;
        }

        body.wiboost-user-shell .wiboost-user-navbar-card {
            min-height: 5.25rem;
            border-width: 0 0 4px 0 !important;
            border-radius: 0 !important;
            transition: none !important;
        }

        body.wiboost-user-shell .wiboost-user-navbar-card:hover {
            border-color: #ffffff !important;
            box-shadow: 0 20px 45px rgba(189, 224, 254, 0.4) !important;
        }

        body.wiboost-user-shell .wiboost-user-navbar-card .wiboost-user-navbar-brand:hover img {
            transform: none !important;
        }

        body.wiboost-user-shell .wiboost-user-navbar-actions {
            padding-right: 0.75rem;
        }

        body.wiboost-user-shell .wiboost-user-navbar-brand {
            padding-left: 0.75rem;
        }

        body.wiboost-user-shell button.wiboost-user-navbar-brand {
            padding: 0 !important;
        }

        body.wiboost-user-shell button.wiboost-user-navbar-brand svg,
        body.wiboost-user-shell .wiboost-profile-menu-icon svg {
            display: block;
            flex: 0 0 auto;
        }

        @media (min-width: 1024px) {
            body.wiboost-user-shell {
                padding-top: 0 !important;
            }

            body.wiboost-user-shell .wiboost-user-sidebar {
                display: block !important;
                top: 0 !important;
                bottom: 0 !important;
                left: 0 !important;
                width: 18rem !important;
                max-height: 100vh !important;
                border-radius: 0 !important;
                transition: width 0.28s ease, opacity 0.28s ease, transform 0.28s ease;
            }

            body.wiboost-user-shell .wiboost-user-sidebar > div {
                height: 100% !important;
                min-height: 0;
                overflow: hidden;
                border-width: 0 4px 0 0 !important;
                border-radius: 0 !important;
            }

            body.wiboost-user-shell .wiboost-user-sidebar > div > div:first-child {
                min-height: var(--wiboost-user-topbar-height) !important;
                display: flex;
                align-items: center;
            }

            body.wiboost-user-shell .wiboost-user-sidebar nav {
                min-height: 0;
            }

            body.wiboost-user-shell .wiboost-user-navbar {
                left: 18rem !important;
                right: 0 !important;
                top: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                padding-top: 0 !important;
                border-radius: 0 !important;
                transition: left 0.28s ease;
            }

            body.wiboost-user-shell .wiboost-user-navbar > div {
                width: 100% !important;
                max-width: 100% !important;
                border-radius: 0 !important;
                transition: max-width 0.28s ease;
            }

            body.wiboost-user-shell .wiboost-user-navbar-card {
                min-height: var(--wiboost-user-topbar-height);
                border-width: 0 0 4px 0 !important;
                border-radius: 0 !important;
                justify-content: space-between !important;
                overflow: visible !important;
                padding-top: 0.65rem !important;
                padding-bottom: 0.65rem !important;
            }

            body.wiboost-user-shell .wiboost-user-navbar-brand {
                display: none !important;
            }

            body.wiboost-user-shell main,
            body.wiboost-user-shell footer {
                margin-left: 18rem !important;
                max-width: calc(100vw - 18rem) !important;
                width: calc(100vw - 18rem) !important;
                transition: margin-left 0.28s ease, max-width 0.28s ease, width 0.28s ease;
            }

            body.wiboost-user-shell main {
                padding-left: 2rem !important;
                padding-right: 2rem !important;
                padding-top: 2.5rem !important;
            }

            body.wiboost-user-shell main > .wiboost-font {
                padding-top: 0 !important;
                margin-top: 0 !important;
            }

            body.wiboost-user-shell footer > div {
                max-width: min(1280px, calc(100vw - 20rem)) !important;
                transition: max-width 0.28s ease;
            }

            body.wiboost-user-shell main > .wiboost-font {
                max-width: 100% !important;
            }

            body.wiboost-user-shell main > [class*="max-w-"],
            body.wiboost-user-shell main > .wiboost-font > [class*="max-w-"] {
                max-width: 100% !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-sidebar {
                width: 5.5rem !important;
                opacity: 1;
                pointer-events: auto;
                transform: none;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-navbar {
                left: 5.5rem !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                right: 0 !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-navbar > div,
            body.wiboost-user-shell.wiboost-user-sidebar-collapsed footer > div {
                max-width: 100% !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-navbar-brand {
                display: none !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-navbar-card {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                justify-content: space-between !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed main,
            body.wiboost-user-shell.wiboost-user-sidebar-collapsed footer {
                margin-left: 5.5rem !important;
                max-width: calc(100vw - 5.5rem) !important;
                width: calc(100vw - 5.5rem) !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed footer > div {
                max-width: min(1280px, calc(100vw - 7.5rem)) !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-sidebar > div > div:first-child {
                display: grid !important;
                place-items: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-sidebar > div > div:first-child a {
                width: 100% !important;
                justify-content: center !important;
                gap: 0 !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-sidebar > div > div:first-child img {
                margin-left: auto !important;
                margin-right: auto !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-sidebar nav a {
                justify-content: center !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-sidebar nav {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-sidebar > div > div:last-child {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
                padding-bottom: 0 !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-sidebar nav a {
                height: 3.75rem;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-sidebar-brand-text,
            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-sidebar-link-label,
            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-sidebar-logout-label,
            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-sidebar-profile-text {
                display: none !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-sidebar > div > div:last-child a {
                height: 3.75rem;
                width: 3.75rem;
                padding: 0 !important;
                justify-content: center !important;
            }

            .wiboost-navbar-sidebar-toggle svg {
                transition: transform 0.28s ease;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-navbar-sidebar-toggle svg {
                transform: rotate(180deg);
            }

            body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-sidebar,
            body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-sidebar > div,
            body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar,
            body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar > div,
            body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .wiboost-user-navbar-card,
            body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar [class*="rounded-"].wiboost-user-navbar-card,
            body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar-card.wiboost-user-navbar-card {
                border-radius: 0 !important;
            }

            body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .wiboost-user-navbar-card {
                border-width: 0 0 4px 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            body.wiboost-compact-ui.wiboost-user-shell .wiboost-navbar-sidebar-toggle,
            body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar-metric,
            body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-desktop-profile {
                display: flex !important;
            }

            body.wiboost-user-shell .wiboost-user-profile-chip,
            body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar-actions > .wiboost-user-profile-chip {
                display: none !important;
            }

            body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar-actions {
                margin-left: auto !important;
                padding-right: 2rem;
            }
        }

        .wiboost-navbar-sidebar-toggle,
        .wiboost-user-desktop-profile {
            display: none;
        }

        .wiboost-user-navbar-metric {
            display: flex;
        }

        .wiboost-user-navbar-metric-value {
            max-width: none;
            overflow: visible;
            text-overflow: clip;
            white-space: nowrap;
        }

        .wiboost-navbar-icon-metric {
            display: inline-flex;
            height: 1.35rem;
            width: 1.35rem;
            flex: 0 0 1.35rem;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 480px) {
            .wiboost-user-navbar-card {
                gap: 0.45rem;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
                min-height: var(--wiboost-user-mobile-navbar-height);
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
                gap: 0.3rem !important;
            }

            .wiboost-user-profile-chip {
                max-width: min(11rem, 43vw);
                padding: 0.4rem 0.55rem 0.4rem 0.4rem !important;
            }

            .wiboost-user-profile-name {
                max-width: 6.5rem;
                font-size: clamp(0.66rem, 2.45vw, 0.82rem) !important;
                letter-spacing: -0.01em;
                overflow: visible !important;
                text-overflow: clip !important;
                white-space: nowrap !important;
            }

            .wiboost-user-navbar-metric {
                gap: 0.25rem !important;
                padding: 0.48rem 0.6rem !important;
                font-size: 0.68rem !important;
                line-height: 1 !important;
            }

            .wiboost-user-navbar-actions a[aria-label="Saldo"] {
                min-width: 6.6rem;
            }

            .wiboost-user-navbar-actions a[aria-label="Poin"] {
                min-width: 3.75rem;
            }

            .wiboost-navbar-icon-metric {
                height: 1.05rem;
                width: 1.05rem;
                flex-basis: 1.05rem;
            }

            .wiboost-navbar-icon-metric svg {
                height: 0.78rem !important;
                width: 0.78rem !important;
            }

            .wiboost-user-navbar-metric-value {
                max-width: none !important;
                overflow: visible !important;
                text-overflow: clip !important;
                white-space: nowrap;
            }
        }

        @media (max-width: 1023px) {
            .wiboost-user-navbar-title {
                display: none !important;
            }

            .wiboost-user-navbar-brand {
                flex: 0 0 auto !important;
            }

            button.wiboost-user-navbar-brand {
                flex: 0 0 3rem !important;
                width: 3rem !important;
                min-width: 3rem !important;
            }

            .wiboost-user-navbar-actions .wiboost-user-navbar-metric {
                order: 1;
            }

            .wiboost-user-navbar-actions .wiboost-user-profile-chip,
            .wiboost-user-navbar-actions > .relative {
                order: 2;
            }

            .wiboost-user-mobile-menu {
                top: 0 !important;
            }

            .wiboost-user-mobile-menu-panel {
                border-radius: 0 !important;
                height: 100dvh !important;
                max-height: 100dvh !important;
                overflow: hidden !important;
                padding-bottom: max(0.75rem, env(safe-area-inset-bottom)) !important;
                padding-top: 1.25rem !important;
            }

            @supports not (height: 100dvh) {
                .wiboost-user-mobile-menu-panel {
                    height: 100vh !important;
                    max-height: 100vh !important;
                }
            }

            .wiboost-mobile-sidebar-stack {
                display: grid !important;
                grid-template-rows: auto minmax(0, 1fr) clamp(5.75rem, 12svh, 6.75rem) !important;
                height: 100% !important;
                min-height: 0 !important;
                row-gap: 0.65rem !important;
            }

            .wiboost-mobile-sidebar-header {
                border-bottom: 4px dashed #d7ecff !important;
                padding-bottom: 1rem !important;
            }

            .wiboost-mobile-sidebar-nav {
                min-height: 0 !important;
                overflow: hidden !important;
                padding-bottom: 0.15rem !important;
            }

            .wiboost-mobile-sidebar-footer {
                align-items: center !important;
                border-top: 4px dashed #d7ecff !important;
                display: flex !important;
                margin-bottom: 0 !important;
                min-height: clamp(5.75rem, 12svh, 6.75rem) !important;
                padding: 0.75rem 0 0 !important;
            }

            .wiboost-mobile-sidebar-footer > .wiboost-sidebar-profile-link {
                flex: 1 1 auto !important;
                min-width: 0 !important;
                margin: 0 !important;
                width: auto !important;
            }

            body.wiboost-user-shell main {
                padding-top: 1.25rem !important;
            }

            body.wiboost-user-shell main .wiboost-font > div.text-center:first-of-type,
            body.wiboost-user-shell main .wiboost-font > div.text-center:first-of-type h1,
            body.wiboost-user-shell main .wiboost-font > div.text-center:first-of-type h2 {
                text-align: left !important;
            }

            body.wiboost-user-shell main .wiboost-font > div.text-center:first-of-type h1.flex,
            body.wiboost-user-shell main .wiboost-font > div.text-center:first-of-type h2.flex {
                justify-content: flex-start !important;
            }
        }
    </style>
    @include('partials.ios-layout-fixes')
    <style>
        body.wiboost-user-shell .wiboost-user-navbar-card,
        body.wiboost-user-shell .wiboost-user-navbar > div > div {
            overflow: visible !important;
        }

        body.wiboost-user-shell .wiboost-user-navbar-actions {
            flex-wrap: nowrap !important;
        }

        body.wiboost-user-shell .wiboost-user-navbar-actions a[aria-label="Saldo"],
        body.wiboost-user-shell .wiboost-user-navbar-actions a[aria-label="Poin"] {
            width: auto !important;
            min-width: max-content !important;
            max-width: none !important;
            overflow: visible !important;
            white-space: nowrap !important;
        }

        body.wiboost-user-shell .wiboost-user-navbar-actions a[aria-label="Saldo"] .wiboost-user-navbar-metric-value,
        body.wiboost-user-shell .wiboost-user-navbar-actions a[aria-label="Poin"] .wiboost-user-navbar-metric-value {
            display: inline-block !important;
            width: auto !important;
            min-width: max-content !important;
            max-width: none !important;
            overflow: visible !important;
            text-overflow: clip !important;
            white-space: nowrap !important;
        }

        body.wiboost-user-shell .wiboost-user-sidebar > div > div:last-child {
            display: flex !important;
            align-items: center !important;
            height: var(--wiboost-user-topbar-height) !important;
            min-height: var(--wiboost-user-topbar-height) !important;
            padding: 0 1rem !important;
        }

        body.wiboost-user-shell .wiboost-sidebar-profile-link {
            flex: 1 1 auto;
            min-width: 0;
        }

        body.wiboost-user-shell .wiboost-sidebar-membership-link {
            flex: 0 0 3.5rem;
        }

        @media (min-width: 1024px) {
            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-user-sidebar > div > div:last-child {
                justify-content: center !important;
                height: var(--wiboost-user-topbar-height) !important;
                min-height: var(--wiboost-user-topbar-height) !important;
                padding: 0 0.75rem !important;
            }

            body.wiboost-user-shell.wiboost-user-sidebar-collapsed .wiboost-sidebar-membership-link {
                display: none !important;
            }
        }

        @media (max-width: 480px) {
            body.wiboost-user-shell .wiboost-user-navbar-actions {
                gap: 0.28rem !important;
            }

            body.wiboost-user-shell .wiboost-user-navbar-actions a[aria-label="Saldo"] {
                padding-inline: 0.62rem !important;
                font-size: clamp(0.62rem, 2.35vw, 0.68rem) !important;
            }

            body.wiboost-user-shell .wiboost-user-navbar-actions a[aria-label="Poin"] {
                padding-inline: 0.55rem !important;
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
                return localStorage.getItem('wiboost_desktop_sidebar_open') !== '0';
            } catch (error) {
                return true;
            }
        })()
    }"
    x-init="
        try {
            $watch('desktopSidebarOpen', value => localStorage.setItem('wiboost_desktop_sidebar_open', value ? '1' : '0'));
        } catch (error) {}
    "
    :class="{ 'wiboost-user-sidebar-collapsed': !desktopSidebarOpen }"
    class="wiboost-compact-ui wiboost-user-shell @yield('body_class') min-h-screen w-full max-w-full overflow-x-hidden bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white relative flex flex-col"
>
    <script>
        try {
            if (localStorage.getItem('wiboost_desktop_sidebar_open') === '0') {
                document.body.classList.add('wiboost-user-sidebar-collapsed');
            }
        } catch (error) {}
    </script>

    @php
        $currentUser = Auth::user();
        if ($currentUser && method_exists($currentUser, 'syncExpiredMembershipRole') && $currentUser->syncExpiredMembershipRole()) {
            $currentUser = $currentUser->fresh();
            Auth::setUser($currentUser);
        }
        $brandLogoUrl = asset('images/wiboost-logo-nav.png') . '?v=20260613-nav2';
        $defaultProfilePhotoUrl = \App\Models\User::defaultProfilePhotoUrl();
        $currentUserPhotoUrl = $currentUser?->profile_photo_url ?? $defaultProfilePhotoUrl;
        $currentUserDisplayName = trim((string) ($currentUser?->name ?? 'User'));
        $currentUserNameParts = preg_split('/\s+/', $currentUserDisplayName, -1, PREG_SPLIT_NO_EMPTY);
        $currentUserShortName = trim(implode(' ', array_slice($currentUserNameParts ?: ['User'], 0, 2))) ?: 'User';
        $currentUserIsReseller = $currentUser?->isReseller() ?? false;
        $currentUserIsMember = $currentUser?->isMember() ?? false;
        $currentUserRoleLabel = $currentUserIsMember ? 'Member' : ($currentUserIsReseller ? 'Reseller' : 'Buyer');
        $currentUserProfileBorder = $currentUserIsMember ? '#8b5cf6' : ($currentUserIsReseller ? '#f4b740' : '#5a76c8');
        $currentUserRoleText = $currentUserIsMember ? '#8b5cf6' : ($currentUserIsReseller ? '#d99b16' : '#8faaf3');

        $mobileUserNav = [
            ['route' => 'user.dashboard', 'pattern' => 'user.dashboard', 'label' => 'Home', 'icon' => 'home'],
            ['route' => 'user.history', 'pattern' => 'user.history', 'label' => 'Riwayat', 'icon' => 'history'],
            ['route' => 'user.wallet.index', 'pattern' => 'user.wallet.*', 'label' => 'Saldo', 'icon' => 'wallet'],
            ['route' => 'user.points.index', 'pattern' => 'user.points.*', 'label' => 'Poin', 'icon' => 'points'],
            ['route' => 'user.reseller.index', 'pattern' => 'user.reseller.*', 'label' => 'Reseller', 'icon' => 'reseller'],
            ['route' => 'user.tutorials.index', 'pattern' => 'user.tutorials.*', 'label' => 'Tutorial', 'icon' => 'tutorial'],
        ];

        $userNavIconPaths = [
            'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5 9.5V21h14V9.5M9 21v-6h6v6" />',
            'wallet' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h15a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-10a2 2 0 0 1 2-2Zm0 0 12-3v3M17 13.5h.01" />',
            'points' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5 14.6 9l6 .8-4.35 4.15 1.05 5.85L12 17l-5.3 2.8 1.05-5.85L3.4 9.8l6-.8L12 3.5Z" />',
            'diamond' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.5 4.5h11L21 9l-9 11L3 9l3.5-4.5Z M3 9h18" />',
            'reseller' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.5 10.5V8A3.5 3.5 0 0 1 12 4.5 3.5 3.5 0 0 1 15.5 8v2.5M5.5 10.5h13l-1 9h-11l-1-9ZM9 14h6" />',
            'tutorial' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.5A2.5 2.5 0 0 1 7 3h11.5v16H7a2.5 2.5 0 0 0-2.5 2.5v-16Zm0 0v16M8.25 7.25h6.5M8.25 10.75h5M8.25 14.25h4" />',
            'history' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 3-6.7M3 4.5v5h5M12 7.5V12l3 2" />',
            'profile' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0" />',
            'edit' => '<path stroke-linecap="round" stroke-linejoin="round" d="m16.5 4.5 3 3M4 20h4l10.5-10.5a2.12 2.12 0 0 0-3-3L5 17v3Z" />',
            'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1" />',
            'admin' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l2.6 5.2 5.7.85-4.15 4.05.98 5.7L12 16.1 6.87 18.8l.98-5.7L3.7 9.05l5.7-.85L12 3Z" />',
        ];

        $activeDisplayPayload = collect();
        $shouldShowDisplayPopup = false;
    @endphp

    <aside class="wiboost-user-sidebar fixed inset-y-0 left-0 z-40 hidden w-72 rounded-none lg:block">
        <div class="flex h-full flex-col rounded-none border-r-4 border-white bg-white/90 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-xl">
            <div class="shrink-0 border-b-4 border-dashed border-[#f4f9ff] px-6 py-5">
                <a href="{{ route('user.dashboard') }}" class="group flex items-center gap-3">
                    <img src="{{ $brandLogoUrl }}" onerror="this.onerror=null;this.src='{{ asset('images/wiboost-logo.png') }}?v=20260426-full';" alt="Logo Wiboost Store" width="192" height="192" fetchpriority="high" loading="eager" decoding="async" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm transition-transform group-hover:scale-110">
                    <div class="wiboost-sidebar-brand-text min-w-0">
                        <p class="text-xl font-black leading-tight text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                    </div>
                </a>
            </div>

            <nav class="hide-scroll flex-1 space-y-2 overflow-y-auto overscroll-contain px-5 py-4">
                @foreach($mobileUserNav as $item)
                    <?php $isActiveUserNav = request()->routeIs($item['pattern']); ?>
                    <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-full px-6 py-3.5 text-sm font-black transition-all duration-300 {{ $isActiveUserNav ? 'border-2 border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 scale-[1.02]' : 'border-2 border-transparent text-[#5a76c8] hover:border-white hover:bg-[#f4f9ff] hover:scale-[1.02]' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $isActiveUserNav ? 'bg-white/20 text-white' : 'bg-white text-[#5a76c8] shadow-sm' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24">
                                {!! $userNavIconPaths[$item['icon']] ?? $userNavIconPaths['home'] !!}
                            </svg>
                        </span>
                        <span class="wiboost-sidebar-link-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="shrink-0 gap-3 border-t-4 border-dashed border-[#f4f9ff] p-4">
                <a href="{{ route('profile.edit') }}" class="wiboost-sidebar-profile-link flex items-center gap-3 rounded-[1.5rem] border-2 border-white bg-[#f4f9ff] px-4 py-3 text-[#2b3a67] shadow-sm transition hover:bg-[#e0fbfc]">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 bg-white ring-2 ring-white" style="border-color: {{ $currentUserProfileBorder }};">
                        <img src="{{ $currentUserPhotoUrl }}" onerror="this.onerror=null;this.src='{{ $defaultProfilePhotoUrl }}';" alt="Foto profil {{ $currentUserShortName }}" class="h-full w-full object-cover">
                    </span>
                    <span class="wiboost-sidebar-profile-text min-w-0">
                        <span class="block truncate text-sm font-black">{{ $currentUserShortName }}</span>
                        <span class="block text-[10px] font-black uppercase tracking-widest" style="color: {{ $currentUserRoleText }};">{{ $currentUserRoleLabel }}</span>
                    </span>
                </a>
                <a href="{{ route('user.membership.index') }}" aria-label="Membership" title="Membership" class="wiboost-sidebar-membership-link flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.35rem] border-2 border-white transition active:scale-95" style="background-color: #8b5cf6; color: #ffffff; box-shadow: 0 18px 30px rgba(139, 92, 246, 0.32); {{ request()->routeIs('user.membership.*') ? 'outline: 4px solid #ddd6fe;' : '' }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24" aria-hidden="true">
                        {!! $userNavIconPaths['diamond'] !!}
                    </svg>
                </a>
            </div>
        </div>
    </aside>

    <div class="wiboost-user-navbar pointer-events-none fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-6 lg:px-0 lg:pt-0">
        <div class="pointer-events-auto mx-auto max-w-6xl lg:max-w-none">
            <div class="wiboost-user-navbar-card flex items-center justify-between border-4 border-white bg-white/90 px-4 py-3 shadow-xl shadow-[#bde0fe]/40 backdrop-blur-md sm:px-6 lg:border-x-0 lg:border-t-0 transition-all hover:shadow-[#bde0fe]/60 hover:border-[#e0fbfc]">
                <button type="button" @click="mobileNavOpen = true; profileMenuOpen = false" aria-label="Buka menu" class="wiboost-user-navbar-brand flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition active:scale-95 lg:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>

                <button
                    type="button"
                    @click="desktopSidebarOpen = !desktopSidebarOpen; try { localStorage.setItem('wiboost_desktop_sidebar_open', desktopSidebarOpen ? '1' : '0') } catch (error) {}"
                    :aria-expanded="desktopSidebarOpen.toString()"
                    aria-label="Buka tutup sidebar"
                    class="wiboost-navbar-sidebar-toggle h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition hover:bg-[#e0fbfc] active:scale-95"
                >
                    <svg class="h-6 w-6" :class="desktopSidebarOpen ? 'rotate-0' : 'rotate-180'" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 5-7 7 7 7" />
                    </svg>
                </button>

                <div class="wiboost-user-navbar-actions flex min-w-0 items-center gap-2">
                    <a href="{{ route('user.wallet.index') }}" aria-label="Saldo" class="wiboost-user-navbar-metric shrink-0 items-center gap-2 rounded-full border-2 border-white bg-[#f4f9ff] px-4 py-2.5 text-sm font-black text-[#5a76c8] shadow-sm transition hover:bg-[#e0fbfc]">
                        <span class="wiboost-navbar-icon-metric rounded-full bg-white/80 text-[#5a76c8]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24" aria-hidden="true">
                                {!! $userNavIconPaths['wallet'] !!}
                            </svg>
                        </span>
                        <span class="wiboost-user-navbar-metric-value whitespace-nowrap">Rp {{ number_format((float) ($currentUser?->balance ?? 0), 0, ',', '.') }}</span>
                    </a>

                    <a href="{{ route('user.points.index') }}" aria-label="Poin" class="wiboost-user-navbar-metric shrink-0 items-center gap-2 rounded-full border-2 border-white bg-[#f4f9ff] px-4 py-2.5 text-sm font-black text-[#5a76c8] shadow-sm transition hover:bg-[#e0fbfc]">
                        <span class="wiboost-navbar-icon-metric rounded-full bg-white/80 text-[#5a76c8]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24" aria-hidden="true">
                                {!! $userNavIconPaths['points'] !!}
                            </svg>
                        </span>
                        <span class="wiboost-user-navbar-metric-value whitespace-nowrap">{{ number_format((int) ($currentUser?->points ?? 0), 0, ',', '.') }}</span>
                    </a>

                    <div class="relative" @click.outside="profileMenuOpen = false">
                        <button type="button" @click="profileMenuOpen = !profileMenuOpen; mobileNavOpen = false" :aria-expanded="profileMenuOpen.toString()" aria-label="Menu profil" class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 bg-white shadow-sm ring-2 ring-white transition active:scale-95 sm:h-12 sm:w-12" style="border-color: {{ $currentUserProfileBorder }};">
                            <img src="{{ $currentUserPhotoUrl }}" onerror="this.onerror=null;this.src='{{ $defaultProfilePhotoUrl }}';" alt="Foto profil {{ $currentUserShortName }}" class="h-full w-full object-cover">
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
                                <span class="wiboost-profile-menu-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#f4f9ff]">
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">{!! $userNavIconPaths['profile'] !!}</svg>
                                </span>
                                <span class="whitespace-nowrap">Profil</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 whitespace-nowrap rounded-[1.1rem] px-4 py-3 text-left text-sm font-black text-[#ff6b6b] transition hover:bg-[#fff3f3]">
                                    <span class="wiboost-profile-menu-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#ffe5e5]">
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">{!! $userNavIconPaths['logout'] !!}</svg>
                                    </span>
                                    <span class="whitespace-nowrap">Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Menu Cepat Mobile --}}
    <div x-cloak x-show="mobileNavOpen" class="wiboost-user-mobile-menu fixed inset-0 z-[70] lg:hidden" aria-modal="true" role="dialog">
        <div x-show="mobileNavOpen" x-transition.opacity class="absolute inset-0 bg-[#2b3a67]/55 backdrop-blur-sm" @click="mobileNavOpen = false"></div>
        
        <div x-show="mobileNavOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="-translate-x-full opacity-0"
             class="wiboost-user-mobile-menu-panel absolute inset-y-0 left-0 flex w-[min(82vw,20rem)] flex-col border-r-4 border-white bg-white/95 px-5 py-5 shadow-2xl shadow-[#2b3a67]/30 backdrop-blur-xl">
            
            <div class="wiboost-mobile-sidebar-stack flex min-h-0 flex-1 flex-col">
                <div class="wiboost-mobile-sidebar-header flex shrink-0 items-center gap-3 border-b-4 border-dashed border-[#f4f9ff] pb-4">
                    <img src="{{ $brandLogoUrl }}" onerror="this.onerror=null;this.src='{{ asset('images/wiboost-logo.png') }}?v=20260426-full';" alt="Logo Wiboost Store" width="192" height="192" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm">
                    <p class="text-xl font-black leading-tight text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                </div>
                
                {{-- List Vertical Minimalis --}}
                <div class="wiboost-mobile-sidebar-nav hide-scroll flex min-h-0 flex-1 flex-col gap-2 py-1">
                    @foreach($mobileUserNav as $item)
                        <a href="{{ route($item['route']) }}" class="flex items-center rounded-[1.5rem] border-2 {{ request()->routeIs($item['pattern']) ? 'border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/30' : 'border-transparent bg-[#f8fbff] text-[#5a76c8] hover:border-white hover:bg-[#e0fbfc]' }} px-5 py-3 font-black transition-all">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs($item['pattern']) ? 'bg-white/20 shadow-inner' : 'bg-white shadow-sm border border-[#f0f5ff]' }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24">
                                        {!! $userNavIconPaths[$item['icon']] ?? $userNavIconPaths['home'] !!}
                                    </svg>
                                </div>
                                <span class="text-sm uppercase tracking-widest">{{ $item['label'] }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="wiboost-mobile-sidebar-footer shrink-0 gap-3 border-t-4 border-dashed border-[#f4f9ff] p-4">
                    <a href="{{ route('profile.edit') }}" class="wiboost-sidebar-profile-link flex items-center gap-3 rounded-[1.5rem] border-2 border-white bg-[#f4f9ff] px-4 py-3 text-[#2b3a67] shadow-sm transition hover:bg-[#e0fbfc]">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 bg-white ring-2 ring-white" style="border-color: {{ $currentUserProfileBorder }};">
                            <img src="{{ $currentUserPhotoUrl }}" onerror="this.onerror=null;this.src='{{ $defaultProfilePhotoUrl }}';" alt="Foto profil {{ $currentUserShortName }}" class="h-full w-full object-cover">
                        </span>
                        <span class="wiboost-sidebar-profile-text min-w-0">
                            <span class="block truncate text-sm font-black text-[#2b3a67]">{{ $currentUserShortName }}</span>
                            <span class="block text-[10px] font-black uppercase tracking-widest" style="color: {{ $currentUserRoleText }};">{{ $currentUserRoleLabel }}</span>
                        </span>
                    </a>
                    <a href="{{ route('user.membership.index') }}" aria-label="Membership" title="Membership" class="wiboost-sidebar-membership-link flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.35rem] border-2 border-white transition active:scale-95" style="background-color: #8b5cf6; color: #ffffff; box-shadow: 0 18px 30px rgba(139, 92, 246, 0.32); {{ request()->routeIs('user.membership.*') ? 'outline: 4px solid #ddd6fe;' : '' }}">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24" aria-hidden="true">
                            {!! $userNavIconPaths['diamond'] !!}
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="h-24 sm:h-28 lg:hidden"></div>

    <main class="flex-1 mx-auto w-full max-w-6xl px-4 pb-20 sm:px-6 lg:px-8 md:pb-16 relative z-10">
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

    @include('partials.user-display-popup')

    @if(false)
        @php
            $firstDisplay = $activeDisplayPayload->first() ?? [];
            $firstDisplayHasImage = filled($firstDisplay['image'] ?? '');
            $firstDisplayTheme = $firstDisplay['theme'] ?? 'blue';
            $firstDisplayBackground = match ($firstDisplayTheme) {
                'teal' => 'linear-gradient(135deg, #4bc6b9, #3ba398)',
                'orange' => 'linear-gradient(135deg, #fbbf24, #d97706)',
                'rose' => 'linear-gradient(135deg, #fb7185, #e11d48)',
                default => 'linear-gradient(135deg, #8faaf3, #5a76c8)',
            };
            $firstDisplayLink = $firstDisplay['link'] ?? '';
            $firstDisplayImageClass = $firstDisplayHasImage ? '' : 'hidden';
            $firstDisplayShadeClass = $firstDisplayHasImage ? 'hidden' : '';
            $firstDisplayEmoji = $firstDisplayHasImage ? '' : ($firstDisplay['emoji'] ?? '');
            $firstDisplayBadgeClass = (! $firstDisplayHasImage && filled($firstDisplay['badge'] ?? '')) ? '' : 'hidden';
            $firstDisplayTitleClass = (! $firstDisplayHasImage && filled($firstDisplay['title'] ?? '')) ? '' : 'hidden';
            $firstDisplayDescriptionClass = (! $firstDisplayHasImage && filled($firstDisplay['description'] ?? '')) ? '' : 'hidden';
        @endphp
        <div id="wiboost-display-overlay" class="fixed inset-0 z-[120] flex items-center justify-center bg-[#17213e]/70 p-4 backdrop-blur-sm">
            <div id="wiboost-display-card" class="relative aspect-[4/5] w-[min(92vw,24rem)] max-h-[86vh] overflow-hidden rounded-[2rem] border-4 border-white bg-[#5a76c8] text-white shadow-2xl shadow-[#2b3a67]/40" style="background: {{ $firstDisplayBackground }}; aspect-ratio: 4 / 5;">
                <button id="wiboost-display-close" type="button" aria-label="Tutup display" class="absolute right-2 top-2 z-30 flex h-10 w-10 items-center justify-center rounded-full border-2 border-white bg-white text-[#ff4d4d] shadow-lg shadow-[#ff6b6b]/25 transition active:scale-95">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>

                <a id="wiboost-display-link" href="{{ $firstDisplayLink ?: '#' }}" class="relative block h-full w-full overflow-hidden">
                    <img id="wiboost-display-image" src="{{ $firstDisplayHasImage ? $firstDisplay['image'] : '' }}" alt="{{ $firstDisplay['title'] ?? 'Display Wiboost Store' }}" class="absolute inset-0 h-full w-full object-cover {{ $firstDisplayImageClass }}">
                    <div id="wiboost-display-shade" class="absolute inset-0 bg-gradient-to-br from-[#17213e]/75 via-[#17213e]/25 to-transparent {{ $firstDisplayShadeClass }}"></div>
                    <div id="wiboost-display-emoji" class="absolute right-6 top-16 text-6xl font-black text-white/25">{{ $firstDisplayEmoji }}</div>
                    <div class="absolute inset-x-0 bottom-0 z-20 p-6 sm:p-7">
                        <p id="wiboost-display-badge" class="mb-3 w-fit rounded-full border border-white/30 bg-white/20 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-white shadow-inner backdrop-blur-md {{ $firstDisplayBadgeClass }}">{{ $firstDisplay['badge'] ?? '' }}</p>
                        <h2 id="wiboost-display-title" class="text-2xl font-black leading-tight drop-shadow-md sm:text-3xl {{ $firstDisplayTitleClass }}">{{ $firstDisplay['title'] ?? '' }}</h2>
                        <p id="wiboost-display-description" class="mt-3 text-sm font-bold leading-relaxed text-white/90 drop-shadow-sm {{ $firstDisplayDescriptionClass }}">{{ $firstDisplay['description'] ?? '' }}</p>
                    </div>
                </a>
            </div>
        </div>

        <script>
            (() => {
                const displays = @json($activeDisplayPayload);
                if (!Array.isArray(displays) || displays.length === 0) {
                    return;
                }

                const queue = displays;
                if (queue.length === 0) {
                    return;
                }

                const themeMap = {
                    blue: 'linear-gradient(135deg, #8faaf3, #5a76c8)',
                    teal: 'linear-gradient(135deg, #4bc6b9, #3ba398)',
                    orange: 'linear-gradient(135deg, #fbbf24, #d97706)',
                    rose: 'linear-gradient(135deg, #fb7185, #e11d48)',
                };

                const overlay = document.getElementById('wiboost-display-overlay');
                const card = document.getElementById('wiboost-display-card');
                const closeButton = document.getElementById('wiboost-display-close');
                const link = document.getElementById('wiboost-display-link');
                const image = document.getElementById('wiboost-display-image');
                const shade = document.getElementById('wiboost-display-shade');
                const badge = document.getElementById('wiboost-display-badge');
                const title = document.getElementById('wiboost-display-title');
                const description = document.getElementById('wiboost-display-description');
                const emoji = document.getElementById('wiboost-display-emoji');
                let index = 0;

                const hideOverlay = () => {
                    overlay?.classList.add('hidden');
                    overlay?.classList.remove('flex');
                    document.body.style.overflow = '';
                };

                const showDisplay = () => {
                    const display = queue[index];
                    if (!display || !overlay || !card || !link) {
                        hideOverlay();
                        return;
                    }

                    card.style.background = themeMap[display.theme] || themeMap.blue;
                    const hasImage = Boolean(display.image);

                    title.textContent = hasImage ? '' : (display.title || '');
                    description.textContent = hasImage ? '' : (display.description || '');
                    title.classList.toggle('hidden', hasImage || !display.title);
                    description.classList.toggle('hidden', hasImage || !display.description);
                    shade?.classList.toggle('hidden', hasImage);
                    emoji.textContent = hasImage ? '' : (display.emoji || '');

                    if (!hasImage && display.badge) {
                        badge.textContent = display.badge;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }

                    if (hasImage) {
                        image.src = display.image;
                        image.alt = display.title || 'Display Wiboost Store';
                        image.classList.remove('hidden');
                    } else {
                        image.removeAttribute('src');
                        image.classList.add('hidden');
                    }

                    if (display.link) {
                        link.href = display.link;
                    } else {
                        link.removeAttribute('href');
                    }

                    overlay.classList.remove('hidden');
                    overlay.classList.add('flex');
                    document.body.style.overflow = 'hidden';
                };

                closeButton?.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    index += 1;
                    showDisplay();
                });

                link?.addEventListener('click', (event) => {
                    if (!queue[index]?.link) {
                        event.preventDefault();
                    }
                });

                showDisplay();
            })();
        </script>
    @endif

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
    @include('partials.wiboost-input-guards')
    <script>
        document.addEventListener('click', (event) => {
            const backLink = event.target.closest('a[data-wiboost-back], a[aria-label="Kembali"]');

            if (!backLink) {
                return;
            }

            if (window.history.length > 1) {
                event.preventDefault();
                if (window.wiboostBackDelayActive) {
                    return;
                }
                window.wiboostBackDelayActive = true;
                backLink.classList.add('is-backing', 'pointer-events-none');
                window.setTimeout(() => {
                    window.history.back();
                }, 340);
            }
        });

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
