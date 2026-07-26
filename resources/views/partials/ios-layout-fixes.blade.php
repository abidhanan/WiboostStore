<style data-wiboost-ios-layout-fixes>
    html {
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    img,
    video,
    canvas {
        max-width: 100%;
        height: auto;
    }

    svg {
        max-width: 100%;
    }

    button,
    input,
    textarea,
    select {
        font: inherit;
        max-width: 100%;
    }

    @supports (-webkit-touch-callout: none) {
        html,
        body {
            overflow-x: hidden !important;
        }

        body.wiboost-user-shell,
        body.wiboost-welcome-page {
            min-height: 100svh;
        }

        body.wiboost-user-shell main,
        body.wiboost-welcome-page main,
        body.wiboost-user-shell footer,
        body.wiboost-welcome-page footer,
        body.wiboost-user-shell section,
        body.wiboost-welcome-page section,
        body.wiboost-user-shell article,
        body.wiboost-welcome-page article {
            min-width: 0;
            max-width: 100%;
        }

        .wiboost-user-navbar,
        body.wiboost-welcome-page > nav.fixed {
            left: 0;
            right: 0;
            width: auto !important;
            max-width: 100vw;
            padding-left: max(0.75rem, env(safe-area-inset-left)) !important;
            padding-right: max(0.75rem, env(safe-area-inset-right)) !important;
        }

        .wiboost-user-navbar {
            padding-top: max(0.75rem, env(safe-area-inset-top)) !important;
        }

        body.wiboost-welcome-page > nav.fixed {
            top: max(1rem, env(safe-area-inset-top)) !important;
        }

        .wiboost-user-navbar > div,
        body.wiboost-welcome-page > nav.fixed > div,
        .wiboost-user-navbar-card,
        .welcome-category-card,
        .wiboost-product-square,
        .history-order-card,
        .legal-card,
        .report-record-section,
        [data-report-record-card] {
            min-width: 0;
            max-width: 100%;
        }

        .wiboost-user-navbar-card,
        .wiboost-user-navbar > div > div,
        body.wiboost-welcome-page > nav.fixed > div > div {
            overflow: hidden;
        }

        .wiboost-user-navbar-brand,
        .wiboost-user-navbar a,
        .wiboost-user-navbar div,
        .wiboost-user-navbar-actions,
        body.wiboost-welcome-page > nav.fixed a,
        body.wiboost-welcome-page > nav.fixed div {
            min-width: 0;
        }

        .wiboost-user-navbar-title,
        .wiboost-user-navbar p,
        body.wiboost-welcome-page > nav.fixed p {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .wiboost-user-navbar-balance {
            min-width: 0 !important;
            max-width: 6.6rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .wiboost-service-trigger,
        .wiboost-service-option,
        .wiboost-service-option-label,
        .report-record-title,
        .report-record-meta,
        .history-order-card h3,
        .history-order-card h4,
        .wiboost-compact-card-title,
        .welcome-category-title {
            max-width: 100%;
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .wiboost-service-trigger-text {
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wiboost-service-select-arrow {
            flex: 0 0 auto;
        }

        .wiboost-whatsapp-float {
            right: max(1rem, env(safe-area-inset-right)) !important;
            bottom: max(1rem, env(safe-area-inset-bottom)) !important;
        }
    }

    @media (max-width: 430px) {
        .wiboost-user-navbar-title,
        body.wiboost-welcome-page > nav.fixed p {
            font-size: clamp(0.82rem, 4vw, 1rem) !important;
            line-height: 1 !important;
            white-space: nowrap !important;
        }

        .wiboost-user-navbar-balance {
            font-size: clamp(0.58rem, 2.55vw, 0.72rem) !important;
            line-height: 1 !important;
            padding-left: 0.55rem !important;
            padding-right: 0.55rem !important;
        }

        .welcome-hero-title {
            font-size: clamp(1.8rem, 10vw, 2.8rem) !important;
            white-space: nowrap !important;
        }

        .wiboost-service-trigger {
            gap: 0.6rem;
        }

        .wiboost-service-option-label,
        .report-record-title,
        .history-order-card h3,
        .history-order-card h4,
        .wiboost-compact-card-title,
        .welcome-category-title {
            line-height: 1.18 !important;
        }
    }
</style>
