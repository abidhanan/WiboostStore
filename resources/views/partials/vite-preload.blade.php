@php
    $wiboostManifestPath = public_path('build/manifest.json');
    $wiboostManifest = is_file($wiboostManifestPath)
        ? (json_decode((string) file_get_contents($wiboostManifestPath), true) ?: [])
        : [];
    $wiboostCssEntryKey = $wiboostCssEntry ?? 'resources/css/app.css';
    $wiboostCssFile = $wiboostManifest[$wiboostCssEntryKey]['file'] ?? null;
@endphp

@if($wiboostCssFile)
    <link rel="preload" as="style" href="{{ asset('build/' . $wiboostCssFile) }}">
@endif
