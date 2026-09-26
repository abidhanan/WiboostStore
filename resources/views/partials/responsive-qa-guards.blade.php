<script>
    (function () {
        try {
            var coarse = window.matchMedia('(pointer: coarse) and (hover: none)');
            var apply = function () {
                var forcedDesktopViewport = window.innerWidth >= 1024 && coarse.matches;
                document.documentElement.classList.toggle('wiboost-force-mobile', forcedDesktopViewport);
                document.documentElement.classList.toggle('wiboost-touch-viewport', coarse.matches);
            };

            apply();
            window.addEventListener('resize', apply, { passive: true });
            window.addEventListener('orientationchange', apply);
            window.addEventListener('pageshow', apply);

            if (coarse.addEventListener) {
                coarse.addEventListener('change', apply);
            }
        } catch (error) {}
    })();
</script>
<style data-wiboost-responsive-qa-guards>
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    html,
    body {
        max-width: 100%;
        overflow-x: hidden;
        text-size-adjust: 100%;
        -webkit-text-size-adjust: 100%;
    }

    body.wiboost-compact-ui,
    body.wiboost-admin-shell,
    body.wiboost-user-shell,
    body.wiboost-welcome-page {
        min-width: 0;
        overflow-x: hidden;
    }

    body.wiboost-compact-ui main,
    body.wiboost-compact-ui section,
    body.wiboost-compact-ui article,
    body.wiboost-compact-ui form,
    body.wiboost-compact-ui header,
    body.wiboost-compact-ui footer,
    body.wiboost-compact-ui nav,
    body.wiboost-compact-ui aside,
    body.wiboost-compact-ui [class*="grid"],
    body.wiboost-compact-ui [class*="flex"] {
        min-width: 0;
    }

    body.wiboost-compact-ui img,
    body.wiboost-compact-ui video,
    body.wiboost-compact-ui canvas,
    body.wiboost-compact-ui svg {
        max-width: 100%;
    }

    body.wiboost-compact-ui button,
    body.wiboost-compact-ui input,
    body.wiboost-compact-ui textarea,
    body.wiboost-compact-ui select {
        max-width: 100%;
    }

    body.wiboost-compact-ui .table-scroll,
    body.wiboost-admin-shell .table-scroll,
    body.wiboost-user-shell .table-scroll {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow-x: auto;
        overflow-y: visible;
        overscroll-behavior-x: contain;
        -webkit-overflow-scrolling: touch;
        scrollbar-gutter: stable;
    }

    body.wiboost-compact-ui .table-scroll table {
        max-width: none;
    }

    body.wiboost-compact-ui td,
    body.wiboost-compact-ui th {
        vertical-align: middle;
    }

    body.wiboost-compact-ui .truncate {
        min-width: 0;
    }

    body.wiboost-admin-shell main nav[role="navigation"],
    body.wiboost-user-shell main nav[role="navigation"] {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    body.wiboost-admin-shell main nav[role="navigation"]::-webkit-scrollbar,
    body.wiboost-user-shell main nav[role="navigation"]::-webkit-scrollbar {
        display: none;
    }

    body.wiboost-admin-shell main nav[role="navigation"] > div,
    body.wiboost-user-shell main nav[role="navigation"] > div {
        min-width: max-content;
        flex-wrap: nowrap !important;
    }

    body.wiboost-admin-shell .admin-detail-modal,
    body.wiboost-user-shell .history-modal-shell {
        padding: max(0.75rem, env(safe-area-inset-top)) max(0.75rem, env(safe-area-inset-right)) max(0.75rem, env(safe-area-inset-bottom)) max(0.75rem, env(safe-area-inset-left));
    }

    html.wiboost-force-mobile body.wiboost-admin-shell {
        display: block !important;
        height: auto !important;
        min-height: 100dvh !important;
        max-height: none !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        touch-action: pan-y !important;
    }

    html.wiboost-force-mobile body.wiboost-admin-shell .wiboost-admin-sidebar,
    html.wiboost-force-mobile body.wiboost-admin-shell .wiboost-admin-navbar-toggle {
        display: none !important;
    }

    html.wiboost-force-mobile body.wiboost-admin-shell .wiboost-admin-mobile-toggle {
        display: flex !important;
    }

    html.wiboost-force-mobile body.wiboost-admin-shell .wiboost-admin-navbar {
        left: 0 !important;
        right: 0 !important;
        top: 0 !important;
        width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    html.wiboost-force-mobile body.wiboost-admin-shell .wiboost-admin-navbar > div,
    html.wiboost-force-mobile body.wiboost-admin-shell .wiboost-admin-navbar-card {
        width: 100% !important;
        max-width: 100% !important;
        border-radius: 0 !important;
    }

    html.wiboost-force-mobile body.wiboost-admin-shell .wiboost-admin-content {
        display: block !important;
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        padding-top: 6rem !important;
    }

    html.wiboost-force-mobile body.wiboost-admin-shell main {
        display: block !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }

    html.wiboost-force-mobile body.wiboost-user-shell .wiboost-user-sidebar,
    html.wiboost-force-mobile body.wiboost-user-shell .wiboost-navbar-sidebar-toggle {
        display: none !important;
    }

    html.wiboost-force-mobile body.wiboost-user-shell .wiboost-user-navbar {
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
    }

    html.wiboost-force-mobile body.wiboost-user-shell main,
    html.wiboost-force-mobile body.wiboost-user-shell footer {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    html.wiboost-touch-viewport body.wiboost-welcome-page .welcome-category-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }

    @media (max-width: 767px) {
        body.wiboost-compact-ui .rounded-\[2\.5rem\] {
            border-radius: 1.6rem !important;
        }

        body.wiboost-compact-ui .rounded-\[2rem\] {
            border-radius: 1.45rem !important;
        }

        body.wiboost-admin-shell main,
        body.wiboost-user-shell main {
            min-height: 0 !important;
            overflow: visible !important;
        }

        body.wiboost-admin-shell h1,
        body.wiboost-admin-shell h2,
        body.wiboost-user-shell h1,
        body.wiboost-user-shell h2 {
            overflow-wrap: anywhere;
        }

        body.wiboost-admin-shell .table-scroll,
        body.wiboost-user-shell .table-scroll {
            margin-left: -0.25rem;
            margin-right: -0.25rem;
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
    }

    @media (min-width: 1536px) {
        body.wiboost-admin-shell .wiboost-admin-content,
        body.wiboost-user-shell main,
        body.wiboost-user-shell footer > div,
        body.wiboost-welcome-page main,
        body.wiboost-welcome-page footer > div,
        .wiboost-public-guest-main,
        .wiboost-public-guest-footer > div {
            padding-left: max(2.5rem, env(safe-area-inset-left, 0px)) !important;
            padding-right: max(2.5rem, env(safe-area-inset-right, 0px)) !important;
        }
    }

    @media (min-width: 1920px) {
        body.wiboost-admin-shell .wiboost-admin-content,
        body.wiboost-user-shell main,
        body.wiboost-user-shell footer > div,
        body.wiboost-welcome-page main,
        body.wiboost-welcome-page footer > div,
        .wiboost-public-guest-main,
        .wiboost-public-guest-footer > div {
            padding-left: max(4rem, env(safe-area-inset-left, 0px)) !important;
            padding-right: max(4rem, env(safe-area-inset-right, 0px)) !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        body.wiboost-compact-ui *,
        body.wiboost-compact-ui *::before,
        body.wiboost-compact-ui *::after {
            scroll-behavior: auto !important;
        }
    }
</style>
