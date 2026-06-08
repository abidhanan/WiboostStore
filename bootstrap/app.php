<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Services\DiscordWebhookService;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // Mendaftarkan alias 'role' untuk middleware CheckRole
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('admin/*') ? '/admin/login' : '/login'
        );

        // Mengizinkan Midtrans mengirim Webhook tanpa terkena blokir CSRF Token
        $middleware->validateCsrfTokens(except: [
            '/midtrans/callback',
            '/tripay/callback',
            '/xoftware/callback',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (\Throwable $exception) {
            if ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() < 500) {
                return;
            }

            try {
                $request = app()->bound('request') ? request() : null;

                app(DiscordWebhookService::class)->sendSystemAlert(
                    'Error website terdeteksi',
                    'Laravel menangkap error dari aplikasi. Cek detailnya supaya bisa cepat ditangani.',
                    [
                        ['name' => 'Error', 'value' => get_class($exception), 'inline' => true],
                        ['name' => 'Pesan', 'value' => mb_strimwidth($exception->getMessage() ?: '-', 0, 900, '...'), 'inline' => false],
                        ['name' => 'Lokasi', 'value' => basename($exception->getFile()) . ':' . $exception->getLine(), 'inline' => true],
                        ['name' => 'URL', 'value' => $request instanceof Request ? mb_strimwidth($request->fullUrl(), 0, 900, '...') : 'CLI / Scheduler', 'inline' => false],
                    ],
                    16738680
                );
            } catch (\Throwable) {
                // Jangan biarkan kegagalan Discord mengganggu proses pelaporan error utama.
            }
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
