@php
    $wiboostIconFiles = [
        '16' => public_path('images/wiboost-icon-16.png'),
        '32' => public_path('images/wiboost-icon-32.png'),
        '48' => public_path('images/wiboost-icon-48.png'),
        '64' => public_path('images/wiboost-icon-64.png'),
        '180' => public_path('images/wiboost-icon-180.png'),
    ];
    $wiboostInlineIcons = [];

    foreach ($wiboostIconFiles as $size => $path) {
        $wiboostInlineIcons[$size] = is_file($path)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($path))
            : asset("images/wiboost-icon-{$size}.png").'?v=20260719-inline-round';
    }
@endphp

<link rel="icon" type="image/png" sizes="64x64" href="{{ $wiboostInlineIcons['64'] }}">
<link rel="icon" type="image/png" sizes="48x48" href="{{ $wiboostInlineIcons['48'] }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ $wiboostInlineIcons['32'] }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ $wiboostInlineIcons['16'] }}">
<link rel="shortcut icon" type="image/png" href="{{ $wiboostInlineIcons['64'] }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ $wiboostInlineIcons['180'] }}">
<meta property="og:image" content="{{ asset('images/wiboost-icon-512.png') }}?v=20260719-inline-round">
<meta property="og:image:width" content="512">
<meta property="og:image:height" content="512">
<meta name="twitter:image" content="{{ asset('images/wiboost-icon-512.png') }}?v=20260719-inline-round">
<meta name="theme-color" content="#a6ccfd">
