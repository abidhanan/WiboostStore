<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$requestHost = strtolower($_SERVER['HTTP_HOST'] ?? '');

$scannerPaths = [
    '/wp',
    '/old',
    '/blog',
    '/backup',
    '/wordpress',
    '/new',
];

if (in_array($requestPath, $scannerPaths, true)
    || (in_array($requestHost, ['mail.wiboostore.com', 'www.mail.wiboostore.com'], true) && $requestPath === '/en')) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: public, max-age=300, must-revalidate');
    exit;
}

if ($requestPath === '/admin' && in_array($requestMethod, ['GET', 'HEAD'], true)) {
    http_response_code(302);
    header('Location: /admin/login');
    exit;
}

if ($requestPath === '/' && $requestMethod === 'POST') {
    http_response_code(303);
    header('Location: /');
    exit;
}

if (in_array($requestMethod, ['GET', 'HEAD'], true)) {
    $publicTextFiles = [
        '/robots.txt' => [
            'file' => __DIR__.'/robots.txt',
            'type' => 'text/plain; charset=UTF-8',
            'fallback' => "User-agent: *\nAllow: /\n\nSitemap: https://wiboostore.com/sitemap.xml\n",
        ],
        '/sitemap.xml' => [
            'file' => __DIR__.'/sitemap.xml',
            'type' => 'application/xml; charset=UTF-8',
            'fallback' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n    <url><loc>https://wiboostore.com/</loc></url>\n</urlset>\n",
        ],
        '/llms.txt' => [
            'file' => __DIR__.'/llms.txt',
            'type' => 'text/plain; charset=UTF-8',
            'fallback' => "# Wiboost Store\n\nWiboost Store adalah platform layanan digital untuk SMM, top up game, paket data, dan aplikasi premium.\n\nWebsite: https://wiboostore.com\n",
        ],
        '/ads.txt' => [
            'file' => __DIR__.'/ads.txt',
            'type' => 'text/plain; charset=UTF-8',
            'fallback' => "# Wiboost Store does not publish an ads.txt inventory file.\n",
        ],
        '/.well-known/security.txt' => [
            'file' => __DIR__.'/.well-known/security.txt',
            'type' => 'text/plain; charset=UTF-8',
            'fallback' => "Contact: https://wiboostore.com/legal/contact\nPreferred-Languages: id,en\nCanonical: https://wiboostore.com/.well-known/security.txt\n",
        ],
        '/.well-known/assetlinks.json' => [
            'file' => __DIR__.'/.well-known/assetlinks.json',
            'type' => 'application/json; charset=UTF-8',
            'fallback' => "[]\n",
        ],
        '/.well-known/apple-app-site-association' => [
            'file' => __DIR__.'/.well-known/apple-app-site-association',
            'type' => 'application/json; charset=UTF-8',
            'fallback' => "{\"applinks\":{\"apps\":[],\"details\":[]},\"webcredentials\":{\"apps\":[]},\"appclips\":{\"apps\":[]}}\n",
        ],
    ];

    if (isset($publicTextFiles[$requestPath])) {
        $staticFile = $publicTextFiles[$requestPath];

        http_response_code(200);
        header('Content-Type: '.$staticFile['type']);
        header('Cache-Control: public, max-age=3600, must-revalidate');

        if ($requestMethod !== 'HEAD') {
            echo is_file($staticFile['file']) ? file_get_contents($staticFile['file']) : $staticFile['fallback'];
        }

        exit;
    }

    if ($requestPath === '/.well-known/openid-configuration') {
        http_response_code(404);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: public, max-age=3600, must-revalidate');

        if ($requestMethod !== 'HEAD') {
            echo "{\"error\":\"not_configured\",\"message\":\"Wiboost Store is not an OpenID Connect issuer.\"}\n";
        }

        exit;
    }

    if (preg_match('#^/apple-touch-icon(?:-[0-9]+x[0-9]+)?(?:-precomposed)?\.png$#', $requestPath)) {
        $iconPath = is_file(__DIR__.'/apple-touch-icon-240x240.png')
            ? __DIR__.'/apple-touch-icon-240x240.png'
            : __DIR__.'/images/wiboost-icon-512.png';

        if (is_file($iconPath)) {
            http_response_code(200);
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=31536000, immutable');

            if ($requestMethod !== 'HEAD') {
                readfile($iconPath);
            }

            exit;
        }
    }
}

$accountRoot = dirname(__DIR__);
$basePath = null;
$basePathCandidates = [
    realpath(__DIR__.'/..') ?: __DIR__.'/..',
    __DIR__,
    $accountRoot.'/WiboostStore',
    $accountRoot.'/wibooststore',
    $accountRoot.'/wiboost-store',
    $accountRoot.'/wiboost_store',
    $accountRoot.'/laravel',
    $accountRoot.'/app',
];

foreach ((array) glob($accountRoot.'/*/artisan') as $artisanPath) {
    $basePathCandidates[] = dirname($artisanPath);
}

$basePathCandidates = array_values(array_unique(array_filter(array_map(
    fn (string $path) => rtrim(str_replace('\\', '/', $path), '/'),
    $basePathCandidates
))));

foreach ($basePathCandidates as $candidate) {
    if (is_file($candidate.'/vendor/autoload.php') && is_file($candidate.'/bootstrap/app.php')) {
        $basePath = $candidate;
        break;
    }
}

if ($basePath === null) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Laravel root tidak ditemukan. Pastikan public_html/index.php mengarah ke folder Laravel yang memiliki artisan, vendor/autoload.php, dan bootstrap/app.php.';
    exit;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
