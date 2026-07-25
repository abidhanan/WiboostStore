@php
    $wiboostManifestPath = public_path('build/manifest.json');
    $wiboostManifest = is_file($wiboostManifestPath)
        ? (json_decode((string) file_get_contents($wiboostManifestPath), true) ?: [])
        : [];
    $wiboostCssFile = $wiboostManifest['resources/css/app.css']['file'] ?? null;
@endphp

@if($wiboostCssFile)
    <link rel="preload" as="style" href="{{ asset('build/' . $wiboostCssFile) }}">
@endif
