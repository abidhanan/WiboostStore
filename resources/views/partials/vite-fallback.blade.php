@php
    $wiboostFallbackCss = null;
    $wiboostCssAssetHref = null;
    $wiboostFallbackBehavior = null;
    $wiboostManifestPath = public_path('build/manifest.json');
    $wiboostFallbackMode = env('WIBOOST_INLINE_VITE_FALLBACK', 'auto');
    $wiboostFallbackMode = is_bool($wiboostFallbackMode)
        ? ($wiboostFallbackMode ? 'true' : 'false')
        : strtolower(trim((string) $wiboostFallbackMode));

    $wiboostNeedsAuthFallback = request()->routeIs('login', 'register', 'admin.login', 'password.*', 'verification.*')
        || request()->is('login', 'register', 'forgot-password', 'reset-password*', 'admin/login');
    $wiboostNeedsImmediateFallback = request()->is('user*')
        || request()->is('admin*')
        || request()->is('profile*')
        || $wiboostNeedsAuthFallback;
    $wiboostFallbackBehavior = match ($wiboostFallbackMode) {
        '1', 'true', 'yes', 'on', 'immediate' => 'immediate',
        'safety', 'safe', 'lazy' => 'safety',
        '0', 'false', 'no', 'off' => null,
        default => $wiboostNeedsImmediateFallback ? 'immediate' : null,
    };

    if ($wiboostFallbackBehavior && is_file($wiboostManifestPath)) {
        static $wiboostCachedFallbackCss = null;
        static $wiboostCachedCssAssetHref = null;

        if ($wiboostCachedFallbackCss === null) {
            $wiboostManifest = json_decode((string) file_get_contents($wiboostManifestPath), true) ?: [];
            $wiboostCssFile = $wiboostManifest['resources/css/app.css']['file'] ?? null;
            $wiboostCssPath = $wiboostCssFile ? public_path('build/' . $wiboostCssFile) : null;
            $wiboostCachedCssAssetHref = $wiboostCssFile ? asset('build/' . $wiboostCssFile) : null;
            $wiboostCachedFallbackCss = ($wiboostCssPath && is_file($wiboostCssPath))
                ? file_get_contents($wiboostCssPath)
                : '';
        }

        $wiboostFallbackCss = $wiboostCachedFallbackCss;
        $wiboostCssAssetHref = $wiboostCachedCssAssetHref;
    }
@endphp

@if($wiboostFallbackCss)
    <style id="wiboost-vite-fallback-css" data-wiboost-vite-fallback media="{{ $wiboostFallbackBehavior === 'safety' ? 'not all' : 'all' }}">
        {!! $wiboostFallbackCss !!}
    </style>
    @if($wiboostFallbackBehavior === 'safety')
        <script>
            (() => {
                const fallback = document.getElementById('wiboost-vite-fallback-css');
                const expectedHref = @json($wiboostCssAssetHref);
                if (!fallback || !expectedHref) return;

                let active = false;
                const activate = () => {
                    if (active) return;
                    active = true;
                    fallback.setAttribute('media', 'all');
                    document.documentElement.dataset.wiboostCssFallback = 'active';
                };

                const expectedPath = new URL(expectedHref, window.location.href).pathname;
                const stylesheet = Array.from(document.querySelectorAll('link[rel="stylesheet"]')).find((link) => {
                    try {
                        return new URL(link.href, window.location.href).pathname === expectedPath;
                    } catch (error) {
                        return false;
                    }
                });

                if (!stylesheet) {
                    requestAnimationFrame(activate);
                    return;
                }

                let loaded = false;
                stylesheet.addEventListener('load', () => { loaded = true; }, { once: true });
                stylesheet.addEventListener('error', activate, { once: true });

                setTimeout(() => {
                    if (loaded) return;
                    const loadedByBrowser = Array.from(document.styleSheets).some((sheet) => {
                        try {
                            return sheet.href && new URL(sheet.href).pathname === expectedPath;
                        } catch (error) {
                            return false;
                        }
                    });

                    if (!loadedByBrowser) {
                        activate();
                    }
                }, 700);
            })();
        </script>
    @endif
@endif
