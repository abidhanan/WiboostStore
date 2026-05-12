@php
    $wiboostFallbackCss = null;
    $wiboostManifestPath = public_path('build/manifest.json');

    if (is_file($wiboostManifestPath)) {
        $wiboostManifest = json_decode((string) file_get_contents($wiboostManifestPath), true) ?: [];
        $wiboostCssFile = $wiboostManifest['resources/css/app.css']['file'] ?? null;
        $wiboostCssPath = $wiboostCssFile ? public_path('build/' . $wiboostCssFile) : null;

        if ($wiboostCssPath && is_file($wiboostCssPath)) {
            $wiboostFallbackCss = file_get_contents($wiboostCssPath);
        }
    }
@endphp

@if($wiboostFallbackCss)
    <style data-wiboost-vite-fallback>
        {!! $wiboostFallbackCss !!}
    </style>
@endif
