<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
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

        // Mengizinkan Midtrans mengirim Webhook tanpa terkena blokir CSRF Token
        $middleware->validateCsrfTokens(except: [
            '/midtrans/callback',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
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
