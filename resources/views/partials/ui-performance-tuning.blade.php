<style data-wiboost-ui-performance>
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
</style>
