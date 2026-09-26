<div id="wiboost-page-loader" class="fixed inset-0 z-[9999] hidden items-center justify-center backdrop-blur-sm" style="background: rgba(15, 23, 42, 0.72);" aria-hidden="true">
    <style>
        @keyframes wiboost-loader-orbit {
            to { transform: rotate(360deg); }
        }

        #wiboost-page-loader.wiboost-page-loader-active {
            display: flex !important;
            pointer-events: auto !important;
        }

        #wiboost-page-loader:not(.wiboost-page-loader-active) {
            pointer-events: none !important;
        }

        .wiboost-loader-mark {
            align-items: center;
            display: flex;
            height: 5.75rem;
            justify-content: center;
            position: relative;
            width: 5.75rem;
        }

        .wiboost-loader-ring {
            animation: wiboost-loader-orbit 0.72s linear infinite !important;
            border: 5px solid rgba(255, 255, 255, 0.26);
            border-top-color: #ffffff;
            border-right-color: #ffffff;
            border-radius: 9999px;
            inset: 0;
            position: absolute;
            transform-origin: 50% 50%;
            will-change: transform;
        }

        .wiboost-loader-ring::after {
            border: 3px solid transparent;
            border-bottom-color: #e0fbfc;
            border-left-color: #e0fbfc;
            border-radius: 9999px;
            content: "";
            inset: 0.72rem;
            position: absolute;
        }

        .wiboost-loader-logo {
            border-radius: 9999px;
            height: 4.75rem;
            object-fit: contain;
            position: relative;
            width: 4.75rem;
            z-index: 1;
        }
    </style>
    <div class="text-center">
        <div class="wiboost-loader-mark mx-auto mb-4">
            <div class="wiboost-loader-ring"></div>
            <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" alt="Loading Wiboost Store" width="192" height="192" loading="eager" decoding="async" class="wiboost-loader-logo drop-shadow-lg">
        </div>
        <p class="text-sm font-black tracking-wide text-white">Memuat...</p>
    </div>
</div>

<script>
    (() => {
        const bootLoader = () => {
            const loader = document.getElementById('wiboost-page-loader');

            if (!loader || loader.dataset.ready === '1') {
                return;
            }

            loader.dataset.ready = '1';
            let loaderFrame = null;
            let loaderTimer = null;

            const activateLoader = () => {
                loaderTimer = null;

                if (loader.dataset.visible === '1') {
                    return;
                }

                loader.dataset.visible = '1';
                loader.setAttribute('aria-hidden', 'false');
                loader.style.pointerEvents = 'auto';
                loader.classList.remove('hidden');
                loader.classList.add('flex', 'wiboost-page-loader-active');

                if (loaderFrame) {
                    window.cancelAnimationFrame(loaderFrame);
                }

                loaderFrame = window.requestAnimationFrame(() => {
                    const ring = loader.querySelector('.wiboost-loader-ring');

                    if (ring) {
                        ring.style.animation = 'none';
                        ring.offsetHeight;
                        ring.style.animation = '';
                    }
                });
            };

            const showLoader = (delay = 180) => {
                if (loader.dataset.visible === '1' || loaderTimer) {
                    return;
                }

                loaderTimer = window.setTimeout(activateLoader, Math.max(0, delay));
            };

            const hideLoader = () => {
                if (loaderTimer) {
                    window.clearTimeout(loaderTimer);
                    loaderTimer = null;
                }

                if (loaderFrame) {
                    window.cancelAnimationFrame(loaderFrame);
                    loaderFrame = null;
                }

                loader.dataset.visible = '0';
                loader.setAttribute('aria-hidden', 'true');
                loader.style.pointerEvents = 'none';
                loader.classList.add('hidden');
                loader.classList.remove('flex', 'wiboost-page-loader-active');
            };

            window.wiboostShowPageLoader = showLoader;
            window.wiboostHidePageLoader = hideLoader;

            const shouldIgnoreModifiedClick = (event) => {
                return event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey;
            };

            document.addEventListener('click', (event) => {
                if (shouldIgnoreModifiedClick(event)) {
                    return;
                }

                const link = event.target.closest('a[href]');

                if (!link || link.target === '_blank' || link.hasAttribute('download') || link.dataset.noPageLoader === 'true' || link.hasAttribute('data-dashboard-week-nav')) {
                    return;
                }

                const rawHref = link.getAttribute('href') || '';
                const ignoredPrefixes = ['#', 'javascript:', 'mailto:', 'tel:'];

                if (ignoredPrefixes.some((prefix) => rawHref.toLowerCase().startsWith(prefix))) {
                    return;
                }

                let url;

                try {
                    url = new URL(link.href, window.location.href);
                } catch (error) {
                    return;
                }

                if (url.origin !== window.location.origin) {
                    return;
                }

                if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) {
                    return;
                }

                showLoader(180);
            }, true);

            document.addEventListener('submit', (event) => {
                const form = event.target;

                if (!(form instanceof HTMLFormElement) || form.dataset.noPageLoader === 'true') {
                    return;
                }

                window.setTimeout(() => {
                    if (!event.defaultPrevented) {
                        showLoader(180);
                    }
                }, 0);
            }, true);

            window.addEventListener('pageshow', hideLoader);
            window.addEventListener('load', hideLoader);
            window.addEventListener('focus', hideLoader);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    hideLoader();
                }
            });
            window.addEventListener('beforeunload', () => showLoader(240));
            window.addEventListener('pagehide', (event) => {
                if (!event.persisted) {
                    showLoader(240);
                }
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootLoader);
        } else {
            bootLoader();
        }
    })();
</script>
