<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->forgetStaleViteHotFile();

        $explicitPublicUrl = rtrim((string) config('wiboost.configured_public_url', ''), '/');
        $publicUrl = $explicitPublicUrl !== ''
            ? $explicitPublicUrl
            : rtrim((string) config('app.url'), '/');

        if ($publicUrl !== '' && ($explicitPublicUrl !== '' || app()->environment('production') || app()->runningInConsole())) {
            URL::forceRootUrl($publicUrl);

            $scheme = parse_url($publicUrl, PHP_URL_SCHEME);
            if (is_string($scheme) && $scheme !== '') {
                URL::forceScheme($scheme);
            }
        }
    }

    protected function forgetStaleViteHotFile(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return;
        }

        $hotUrl = trim((string) file_get_contents($hotFile));
        $host = parse_url($hotUrl, PHP_URL_HOST);
        $port = parse_url($hotUrl, PHP_URL_PORT) ?: (parse_url($hotUrl, PHP_URL_SCHEME) === 'https' ? 443 : 80);

        if (! is_string($host) || $host === '' || ! is_numeric($port)) {
            @unlink($hotFile);

            return;
        }

        $connection = @fsockopen($host, (int) $port, $errorCode, $errorMessage, 0.15);

        if (is_resource($connection)) {
            fclose($connection);

            return;
        }

        @unlink($hotFile);
    }
}
