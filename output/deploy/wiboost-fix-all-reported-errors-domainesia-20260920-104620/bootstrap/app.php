<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use App\Services\DiscordWebhookService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $webMiddleware = [];

        foreach ([
            \App\Http\Middleware\CompressTextResponse::class,
            \App\Http\Middleware\LogActivity::class,
        ] as $middlewareClass) {
            if (class_exists($middlewareClass)) {
                $webMiddleware[] = $middlewareClass;
            }
        }

        if ($webMiddleware !== []) {
            $middleware->web(append: $webMiddleware);
        }
        
        // Mendaftarkan alias 'role' untuk middleware CheckRole
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'admin.access' => \App\Http\Middleware\AdminPageAccess::class,
        ]);

        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('admin/*') ? '/admin/login' : '/login'
        );

        // Mengizinkan Midtrans mengirim Webhook tanpa terkena blokir CSRF Token
        $middleware->validateCsrfTokens(except: [
            '/midtrans/callback',
            '/tripay/callback',
            '/xoftware/callback',
            '/client/top-money',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->stopIgnoring([
            AuthorizationException::class,
            HttpException::class,
            ModelNotFoundException::class,
        ]);

        $exceptions->dontReport([
            TokenMismatchException::class,
        ]);

        $shouldSkipDiscordReport = static function (\Throwable $exception, ?Request $request): bool {
            if ($exception instanceof TokenMismatchException) {
                return true;
            }

            if (! $exception instanceof HttpExceptionInterface) {
                return false;
            }

            $statusCode = $exception->getStatusCode();

            if (! in_array($statusCode, [404, 405], true)) {
                return false;
            }

            if (! $request instanceof Request) {
                return false;
            }

            $host = strtolower($request->getHost());
            $path = trim(str_replace('\\', '/', $request->path()), '/');
            $primaryHosts = ['wiboostore.com', 'www.wiboostore.com'];

            if (! in_array($host, $primaryHosts, true)) {
                return true;
            }

            $ignoredExactPaths = [
                'ads.txt',
                'app-ads.txt',
                'sellers.json',
                'dsrdelete.json',
                'meta.json',
                'null',
                'livewire/livewire.js',
                'comments/feed',
                'wp',
                'old',
                'blog',
                'backup',
                'wordpress',
                'new',
                'en',
                'wp-admin',
                'wp-login.php',
                'xmlrpc.php',
                'admin.php',
            ];

            if (in_array($path, $ignoredExactPaths, true)) {
                return true;
            }

            $ignoredPrefixes = [
                '.well-known/',
                'wp-',
                'wp/',
                'wp1/',
                'wp-json/',
                'wp-admin/',
                'wp-content/',
                'wp-includes/',
                'wordpress/',
                'blog/',
                'old/',
                'backup/',
                'new/',
                'cms/',
                'site/',
                'shop/',
                'test/',
                'web/',
                '2019/',
                '2020/',
                '2021/',
                'log/',
                'logs/',
                'config/',
                'secrets/',
                'var/run/secrets/',
            ];

            foreach ($ignoredPrefixes as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    return true;
                }
            }

            $scannerPatterns = [
                '/(?:%25)*%?2eenv/i',
                '/(?:^|\/)(?:\.env|env\.(?:js|json)|environment(?:\.prod)?\.ts)$/i',
                '/(?:^|\/)(?:aws[-._]|awsconfiguration|amplifyconfiguration)/i',
                '/^assets\/(?:config|environment)\.json$/i',
                '/(?:^|\/)(?:rclone\.conf|samconfig\.toml)$/i',
                '/\.(?:log|csv|map)$/i',
            ];

            foreach ($scannerPatterns as $pattern) {
                if (preg_match($pattern, $path) === 1) {
                    return true;
                }
            }

            return false;
        };

        $exceptions->report(function (\Throwable $exception) use ($shouldSkipDiscordReport) {
            $request = app()->bound('request') ? request() : null;

            try {
                $skipDiscordReport = $shouldSkipDiscordReport($exception, $request instanceof Request ? $request : null);
            } catch (\Throwable) {
                $skipDiscordReport = false;
            }

            if ($skipDiscordReport) {
                return false;
            }

            try {
                $statusCode = $exception instanceof HttpExceptionInterface
                    ? (string) $exception->getStatusCode()
                    : '500';

                app(DiscordWebhookService::class)->sendSystemAlert(
                    'Error website terdeteksi',
                    'Laravel menangkap error dari aplikasi. Cek detailnya supaya bisa cepat ditangani.',
                    [
                        ['name' => 'Error', 'value' => get_class($exception), 'inline' => true],
                        ['name' => 'Status', 'value' => $statusCode, 'inline' => true],
                        ['name' => 'Pesan', 'value' => mb_strimwidth($exception->getMessage() ?: '-', 0, 900, '...'), 'inline' => false],
                        ['name' => 'Lokasi', 'value' => basename($exception->getFile()) . ':' . $exception->getLine(), 'inline' => true],
                        ['name' => 'Method', 'value' => $request instanceof Request ? $request->method() : 'CLI', 'inline' => true],
                        ['name' => 'URL', 'value' => $request instanceof Request ? mb_strimwidth($request->fullUrl(), 0, 900, '...') : 'CLI / Scheduler', 'inline' => false],
                    ],
                    16738680
                );
            } catch (\Throwable) {
                // Jangan biarkan kegagalan Discord mengganggu proses pelaporan error utama.
            }
        });

        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            $message = 'Sesi halaman sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 419);
            }

            $redirect = $request->headers->has('referer')
                ? redirect()->back()
                : redirect()->route($request->is('admin/*') ? 'admin.login' : 'login');

            return $redirect
                ->withInput($request->except(['_token', 'password', 'password_confirmation']))
                ->with('status', $message);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($exception->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi halaman sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.',
                ], 419);
            }

            return redirect()
                ->route('login')
                ->with('status', 'Sesi login sudah kedaluwarsa. Silakan login ulang.');
        });
    })->create();
