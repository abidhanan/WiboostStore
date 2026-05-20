<div id="wiboost-page-loader" class="fixed inset-0 z-[9999] hidden items-center justify-center backdrop-blur-sm" style="background: rgba(15, 23, 42, 0.72);">
    <style>
        @keyframes wiboost-loader-orbit {
            to { transform: rotate(360deg); }
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
            animation: wiboost-loader-orbit 1s linear infinite;
            border: 5px solid rgba(255, 255, 255, 0.26);
            border-top-color: #ffffff;
            border-right-color: #ffffff;
            border-radius: 9999px;
            inset: 0;
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
            <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426-full" alt="Loading Wiboost Store" class="wiboost-loader-logo drop-shadow-lg">
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
            let submitLoaderTimer = null;

            const showLoader = () => {
                loader.classList.remove('hidden');
                loader.classList.add('flex');
            };

            const hideLoader = () => {
                if (submitLoaderTimer) {
                    window.clearTimeout(submitLoaderTimer);
                    submitLoaderTimer = null;
                }

                loader.classList.add('hidden');
                loader.classList.remove('flex');
            };

            document.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');

                if (!link || link.target === '_blank' || link.hasAttribute('download') || link.dataset.noPageLoader === 'true') {
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

                showLoader();
            });

            document.addEventListener('submit', (event) => {
                const form = event.target;

                if (!(form instanceof HTMLFormElement) || form.dataset.noPageLoader === 'true') {
                    return;
                }

                window.setTimeout(() => {
                    if (!event.defaultPrevented) {
                        submitLoaderTimer = window.setTimeout(showLoader, 700);
                    }
                }, 0);
            });

            window.addEventListener('pageshow', hideLoader);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootLoader);
        } else {
            bootLoader();
        }
    })();
</script>
