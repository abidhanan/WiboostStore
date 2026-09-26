@php
    $seoDefaultDescription = 'Platform penyedia layanan suntik sosmed, top up game, paket data, dan aplikasi premium dengan proses cepat, aman, dan praktis.';
    $seoTitle = trim(strip_tags((string) ($title ?? 'Wiboost Store')));
    $seoDescription = \Illuminate\Support\Str::limit(
        \Illuminate\Support\Str::squish(strip_tags((string) ($description ?? $seoDefaultDescription))),
        160,
        ''
    );
    $seoRobots = trim((string) ($robots ?? 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'));
    $seoBaseUrl = rtrim((string) config('app.url', url('/')), '/');
    $seoPath = '/'.ltrim(request()->path(), '/');
    $seoCanonical = (string) ($canonical ?? ($seoBaseUrl.$seoPath));
    $seoImage = (string) ($image ?? asset('images/wiboost-metadata-logo.webp').'?v=20260921-metadata');
    $seoImageType = (string) ($imageType ?? 'image/webp');
    $seoImageWidth = (int) ($imageWidth ?? 1280);
    $seoImageHeight = (int) ($imageHeight ?? 1280);
    $seoImageAlt = trim(strip_tags((string) ($imageAlt ?? 'Logo Wiboost Store')));
    $seoNoIndex = str_contains(strtolower($seoRobots), 'noindex');
    $seoOrganizationId = $seoBaseUrl.'/#organization';
    $seoWebsiteId = $seoBaseUrl.'/#website';

    $seoSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => $seoOrganizationId,
                'name' => 'Wiboost Store',
                'url' => $seoBaseUrl.'/',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $seoImage,
                    'width' => $seoImageWidth,
                    'height' => $seoImageHeight,
                ],
                'sameAs' => [
                    'https://www.instagram.com/wiboost.store',
                    'https://www.tiktok.com/@wiboostore',
                    'https://youtube.com/@wiboostore',
                ],
            ],
            [
                '@type' => 'WebSite',
                '@id' => $seoWebsiteId,
                'url' => $seoBaseUrl.'/',
                'name' => 'Wiboost Store',
                'inLanguage' => 'id-ID',
                'publisher' => ['@id' => $seoOrganizationId],
            ],
            [
                '@type' => 'WebPage',
                '@id' => $seoCanonical.'#webpage',
                'url' => $seoCanonical,
                'name' => $seoTitle,
                'description' => $seoDescription,
                'inLanguage' => 'id-ID',
                'isPartOf' => ['@id' => $seoWebsiteId],
                'about' => ['@id' => $seoOrganizationId],
            ],
        ],
    ];
@endphp

<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="{{ $seoRobots }}">
<meta name="googlebot" content="{{ $seoRobots }}">
<meta name="author" content="Wiboost Store">
<meta name="application-name" content="Wiboost Store">
<meta name="apple-mobile-web-app-title" content="Wiboost Store">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#a6ccfd">
<meta name="color-scheme" content="light">
<meta name="format-detection" content="telephone=no">
<meta name="referrer" content="strict-origin-when-cross-origin">

<link rel="canonical" href="{{ $seoCanonical }}">
<link rel="alternate" hreflang="id-ID" href="{{ $seoCanonical }}">
<link rel="alternate" hreflang="x-default" href="{{ $seoCanonical }}">

<meta property="og:locale" content="id_ID">
<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:site_name" content="Wiboost Store">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoCanonical }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:secure_url" content="{{ $seoImage }}">
<meta property="og:image:type" content="{{ $seoImageType }}">
<meta property="og:image:width" content="{{ $seoImageWidth }}">
<meta property="og:image:height" content="{{ $seoImageHeight }}">
<meta property="og:image:alt" content="{{ $seoImageAlt }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
<meta name="twitter:image:alt" content="{{ $seoImageAlt }}">

@unless($seoNoIndex)
    <script type="application/ld+json">{!! json_encode($seoSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endunless
