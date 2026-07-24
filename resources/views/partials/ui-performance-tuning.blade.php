<script data-wiboost-performance-mode>
    (() => {
        const connection = navigator.connection || navigator.webkitConnection || navigator.mozConnection;
        const lowData = Boolean(connection?.saveData) || ['slow-2g', '2g'].includes(String(connection?.effectiveType || '').toLowerCase());
        const lowMemory = Number(navigator.deviceMemory || 8) <= 3;
        const lowCpu = Number(navigator.hardwareConcurrency || 8) <= 4;
        const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

        if (lowData || lowMemory || lowCpu || reducedMotion) {
            document.documentElement.classList.add('wiboost-lite-ui');
        }
    })();
</script>
<style data-wiboost-ui-performance>
    html.wiboost-lite-ui,
    html.wiboost-lite-ui * {
        scroll-behavior: auto !important;
    }

    html.wiboost-lite-ui .shadow-2xl,
    html.wiboost-lite-ui .shadow-xl,
    html.wiboost-lite-ui .shadow-lg {
        box-shadow: 0 8px 20px rgba(141, 174, 238, 0.16) !important;
    }

    html.wiboost-lite-ui .backdrop-blur,
    html.wiboost-lite-ui .backdrop-blur-sm,
    html.wiboost-lite-ui .backdrop-blur-md,
    html.wiboost-lite-ui .backdrop-blur-xl {
        -webkit-backdrop-filter: none !important;
        backdrop-filter: none !important;
    }

    html.wiboost-lite-ui .drop-shadow,
    html.wiboost-lite-ui .drop-shadow-sm,
    html.wiboost-lite-ui .drop-shadow-md,
    html.wiboost-lite-ui .drop-shadow-lg,
    html.wiboost-lite-ui .drop-shadow-xl {
        filter: none !important;
    }

    html.wiboost-lite-ui .animate-pulse,
    html.wiboost-lite-ui .animate-bounce,
    html.wiboost-lite-ui .animate-spin,
    html.wiboost-lite-ui .animate-float,
    html.wiboost-lite-ui .animate-float-delayed {
        animation: none !important;
    }

    html.wiboost-lite-ui .transition,
    html.wiboost-lite-ui .transition-all,
    html.wiboost-lite-ui .transition-transform,
    html.wiboost-lite-ui .transition-colors {
        transition-duration: 120ms !important;
    }

    .table-scroll,
    .hide-scroll,
    [class*="overflow-x-auto"],
    [class*="overflow-y-auto"] {
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 767px) {
        .table-scroll,
        [class*="overflow-x-auto"],
        [class*="overflow-y-auto"] {
            overscroll-behavior: contain;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }
    }

    a[data-wiboost-back],
    a[aria-label="Kembali"],
    button[aria-label="Kembali"],
    .wiboost-back-link {
        transition:
            transform 360ms cubic-bezier(.22, 1, .36, 1),
            background-color 220ms ease,
            border-color 220ms ease,
            box-shadow 320ms ease,
            opacity 220ms ease !important;
        will-change: transform;
    }

    a[data-wiboost-back]:hover,
    a[aria-label="Kembali"]:hover,
    button[aria-label="Kembali"]:hover,
    .wiboost-back-link:hover {
        transform: translateX(-2px) scale(1.04) !important;
    }

    a[data-wiboost-back]:active,
    a[aria-label="Kembali"]:active,
    button[aria-label="Kembali"]:active,
    .wiboost-back-link:active,
    .wiboost-back-link.is-backing,
    a[data-wiboost-back].is-backing,
    a[aria-label="Kembali"].is-backing {
        transform: translateX(-3px) scale(.98) !important;
        opacity: .9 !important;
        box-shadow: 0 10px 20px rgba(90, 118, 200, .2) !important;
    }

    body.wiboost-compact-ui {
        font-size: 15px;
    }

    @media (min-width: 1024px) {
        body.wiboost-compact-ui {
            font-size: 14px;
        }
    }

    body.wiboost-compact-ui .text-7xl { font-size: 3.6rem !important; line-height: 1.05 !important; }
    body.wiboost-compact-ui .text-6xl { font-size: 3.1rem !important; line-height: 1.05 !important; }
    body.wiboost-compact-ui .text-5xl { font-size: 2.45rem !important; line-height: 1.08 !important; }
    body.wiboost-compact-ui .text-4xl { font-size: 2rem !important; line-height: 1.12 !important; }
    body.wiboost-compact-ui .text-3xl { font-size: 1.55rem !important; line-height: 1.18 !important; }
    body.wiboost-compact-ui .text-2xl { font-size: 1.25rem !important; line-height: 1.22 !important; }
    body.wiboost-compact-ui .text-xl { font-size: 1.05rem !important; line-height: 1.3 !important; }
    body.wiboost-compact-ui .text-lg { font-size: 0.95rem !important; line-height: 1.35 !important; }

    body.wiboost-compact-ui [class*="rounded-[2.5rem]"] { border-radius: 1.75rem !important; }
    body.wiboost-compact-ui [class*="rounded-[2rem]"] { border-radius: 1.45rem !important; }
    body.wiboost-compact-ui [class*="rounded-[1.5rem]"] { border-radius: 1.15rem !important; }

    body.wiboost-compact-ui .p-10 { padding: 2rem !important; }
    body.wiboost-compact-ui .p-8 { padding: 1.5rem !important; }
    body.wiboost-compact-ui .p-6 { padding: 1.15rem !important; }
    body.wiboost-compact-ui .px-8 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }
    body.wiboost-compact-ui .px-6 { padding-left: 1.15rem !important; padding-right: 1.15rem !important; }
    body.wiboost-compact-ui .py-16 { padding-top: 3.25rem !important; padding-bottom: 3.25rem !important; }
    body.wiboost-compact-ui .py-12 { padding-top: 2.5rem !important; padding-bottom: 2.5rem !important; }
    body.wiboost-compact-ui .py-8 { padding-top: 1.5rem !important; padding-bottom: 1.5rem !important; }
    body.wiboost-compact-ui .gap-8 { gap: 1.5rem !important; }
    body.wiboost-compact-ui .gap-6 { gap: 1.1rem !important; }
    body.wiboost-compact-ui .h-20 { height: 4.25rem !important; }

    body.wiboost-compact-ui .shadow-2xl,
    body.wiboost-compact-ui .shadow-xl {
        box-shadow: 0 10px 24px rgba(141, 174, 238, 0.18) !important;
    }

    body.wiboost-compact-ui .backdrop-blur,
    body.wiboost-compact-ui .backdrop-blur-sm,
    body.wiboost-compact-ui .backdrop-blur-md,
    body.wiboost-compact-ui .backdrop-blur-xl {
        -webkit-backdrop-filter: none !important;
        backdrop-filter: none !important;
    }

    body.wiboost-compact-ui .animate-float,
    body.wiboost-compact-ui .animate-float-delayed {
        animation: none !important;
    }

    body.wiboost-compact-ui .transition-all {
        transition-property: color, background-color, border-color, opacity, box-shadow, transform !important;
        transition-duration: 150ms !important;
    }

    body.wiboost-compact-ui footer .border-t-2 > div {
        display: flex !important;
        flex-wrap: nowrap !important;
        justify-content: center !important;
        gap: 0.3rem !important;
        overflow: visible !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.wiboost-compact-ui footer .border-t-2 > div > a {
        flex: 0 1 auto !important;
        width: auto !important;
        min-width: 0 !important;
        max-width: none !important;
        padding: 0.34rem 0.48rem !important;
        font-size: 0.56rem !important;
        line-height: 1 !important;
        letter-spacing: 0 !important;
        white-space: nowrap !important;
    }

    @media (min-width: 380px) {
        body.wiboost-compact-ui footer .border-t-2 > div {
            gap: 0.36rem !important;
        }

        body.wiboost-compact-ui footer .border-t-2 > div > a {
            padding: 0.36rem 0.6rem !important;
            font-size: 0.62rem !important;
        }
    }

    @media (min-width: 768px) {
        body.wiboost-compact-ui footer > div > div:first-child > div:first-child {
            flex: 0 0 auto !important;
            min-width: max-content !important;
        }

        body.wiboost-compact-ui footer > div > div:first-child > div:first-child > p {
            width: max-content !important;
            max-width: none !important;
            white-space: nowrap !important;
        }

        body.wiboost-compact-ui footer .border-t-2 > div {
            justify-content: center !important;
            gap: 0.75rem !important;
        }

        body.wiboost-compact-ui footer .border-t-2 > div > a {
            flex: 0 0 auto !important;
            width: auto !important;
            min-width: 0 !important;
            padding: 0.45rem 1.1rem !important;
            font-size: 0.86rem !important;
            line-height: 1.1 !important;
        }
    }

    @media (min-width: 768px) {
        body.wiboost-compact-ui.wiboost-welcome-page,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer {
            font-size: 16px;
        }

        body.wiboost-compact-ui.wiboost-welcome-page .text-7xl,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .text-7xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .text-7xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .text-7xl {
            font-size: 4.5rem !important;
            line-height: 1 !important;
        }

        body.wiboost-compact-ui.wiboost-welcome-page .text-6xl,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .text-6xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .text-6xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .text-6xl {
            font-size: 3.75rem !important;
            line-height: 1 !important;
        }

        body.wiboost-compact-ui.wiboost-welcome-page .text-5xl,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .text-5xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .text-5xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .text-5xl {
            font-size: 3rem !important;
            line-height: 1 !important;
        }

        body.wiboost-compact-ui.wiboost-welcome-page .text-4xl,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .text-4xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .text-4xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .text-4xl {
            font-size: 2.25rem !important;
            line-height: 2.5rem !important;
        }

        body.wiboost-compact-ui.wiboost-welcome-page .text-3xl,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .text-3xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .text-3xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .text-3xl {
            font-size: 1.875rem !important;
            line-height: 2.25rem !important;
        }

        body.wiboost-compact-ui.wiboost-welcome-page .text-2xl,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .text-2xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .text-2xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .text-2xl {
            font-size: 1.5rem !important;
            line-height: 2rem !important;
        }

        body.wiboost-compact-ui.wiboost-welcome-page .text-xl,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .text-xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .text-xl,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .text-xl {
            font-size: 1.25rem !important;
            line-height: 1.75rem !important;
        }

        body.wiboost-compact-ui.wiboost-welcome-page .text-lg,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .text-lg,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .text-lg,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .text-lg {
            font-size: 1.125rem !important;
            line-height: 1.75rem !important;
        }

        body.wiboost-compact-ui.wiboost-welcome-page [class*="rounded-[2.5rem]"],
        body.wiboost-compact-ui.wiboost-user-dashboard-page main [class*="rounded-[2.5rem]"],
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar [class*="rounded-[2.5rem]"],
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer [class*="rounded-[2.5rem]"] {
            border-radius: 2.5rem !important;
        }

        body.wiboost-compact-ui.wiboost-welcome-page [class*="rounded-[2rem]"],
        body.wiboost-compact-ui.wiboost-user-dashboard-page main [class*="rounded-[2rem]"],
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar [class*="rounded-[2rem]"],
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer [class*="rounded-[2rem]"] {
            border-radius: 2rem !important;
        }

        body.wiboost-compact-ui.wiboost-welcome-page [class*="rounded-[1.5rem]"],
        body.wiboost-compact-ui.wiboost-user-dashboard-page main [class*="rounded-[1.5rem]"],
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar [class*="rounded-[1.5rem]"],
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer [class*="rounded-[1.5rem]"] {
            border-radius: 1.5rem !important;
        }

        body.wiboost-compact-ui.wiboost-welcome-page .p-10,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .p-10,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .p-10,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .p-10 { padding: 2.5rem !important; }

        body.wiboost-compact-ui.wiboost-welcome-page .p-8,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .p-8,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .p-8,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .p-8 { padding: 2rem !important; }

        body.wiboost-compact-ui.wiboost-welcome-page .p-6,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .p-6,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .p-6,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .p-6 { padding: 1.5rem !important; }

        body.wiboost-compact-ui.wiboost-welcome-page .px-8,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .px-8,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .px-8,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .px-8 { padding-left: 2rem !important; padding-right: 2rem !important; }

        body.wiboost-compact-ui.wiboost-welcome-page .px-6,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .px-6,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .px-6,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .px-6 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }

        body.wiboost-compact-ui.wiboost-welcome-page .py-16,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .py-16,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .py-16,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .py-16 { padding-top: 4rem !important; padding-bottom: 4rem !important; }

        body.wiboost-compact-ui.wiboost-welcome-page .py-12,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .py-12,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .py-12,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .py-12 { padding-top: 3rem !important; padding-bottom: 3rem !important; }

        body.wiboost-compact-ui.wiboost-welcome-page .py-8,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .py-8,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .py-8,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .py-8 { padding-top: 2rem !important; padding-bottom: 2rem !important; }

        body.wiboost-compact-ui.wiboost-welcome-page .gap-8,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .gap-8,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .gap-8,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .gap-8 { gap: 2rem !important; }

        body.wiboost-compact-ui.wiboost-welcome-page .gap-6,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .gap-6,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .gap-6,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .gap-6 { gap: 1.5rem !important; }

        body.wiboost-compact-ui.wiboost-welcome-page .h-20,
        body.wiboost-compact-ui.wiboost-user-dashboard-page main .h-20,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-navbar .h-20,
        body.wiboost-compact-ui.wiboost-user-shell .wiboost-user-footer .h-20 { height: 5rem !important; }
    }

    body.wiboost-compact-ui select:not([multiple]):not(.wiboost-service-native-select) {
        -webkit-appearance: none !important;
        appearance: none !important;
        background-color: #f4f9ff !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%235a76c8' stroke-width='3' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m6 9 6 6 6-6'/%3E%3C/svg%3E") !important;
        background-position: right 1rem center !important;
        background-repeat: no-repeat !important;
        background-size: 1rem !important;
        border: 4px solid #ffffff !important;
        border-radius: 1.25rem !important;
        box-shadow: inset 0 2px 8px rgba(90, 118, 200, 0.08), 0 10px 24px rgba(189, 224, 254, 0.2) !important;
        color: #2b3a67 !important;
        font-weight: 900 !important;
        outline: none !important;
        padding-right: 2.75rem !important;
    }

    body.wiboost-compact-ui select:not([multiple]):not(.wiboost-service-native-select):focus {
        border-color: #bde0fe !important;
        box-shadow: 0 0 0 4px rgba(189, 224, 254, 0.35), inset 0 2px 8px rgba(90, 118, 200, 0.08) !important;
    }
</style>
