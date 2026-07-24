@php
    $wiboostFallbackCss = null;
    $wiboostManifestPath = public_path('build/manifest.json');
    $shouldInlineViteFallback = filter_var(env('WIBOOST_INLINE_VITE_FALLBACK', true), FILTER_VALIDATE_BOOLEAN);

    if ($shouldInlineViteFallback && is_file($wiboostManifestPath)) {
        static $wiboostCachedFallbackCss = null;

        if ($wiboostCachedFallbackCss === null) {
            $wiboostManifest = json_decode((string) file_get_contents($wiboostManifestPath), true) ?: [];
            $wiboostCssFile = $wiboostManifest['resources/css/app.css']['file'] ?? null;
            $wiboostCssPath = $wiboostCssFile ? public_path('build/' . $wiboostCssFile) : null;
            $wiboostCachedFallbackCss = ($wiboostCssPath && is_file($wiboostCssPath))
                ? file_get_contents($wiboostCssPath)
                : '';
        }

        $wiboostFallbackCss = $wiboostCachedFallbackCss;
    }
@endphp

@if($wiboostFallbackCss)
    <style data-wiboost-vite-fallback>
        {!! $wiboostFallbackCss !!}
    </style>
@endif
