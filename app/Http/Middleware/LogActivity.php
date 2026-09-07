<?php

namespace App\Http\Middleware;

use App\Services\IpGeolocationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Dijalankan SETELAH respons dikirim ke user (tidak menambah waktu tunggu user).
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            if ($this->shouldSkip($request)) {
                return;
            }

            $ip = $this->clientIp($request);

            DB::table('activity_logs')->insert([
                'user_id' => Auth::id(),
                'ip' => $ip,
                'method' => $request->method(),
                'url' => Str::limit($this->fullPath($request), 1900, ''),
                'route_name' => optional($request->route())->getName(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'created_at' => now(),
            ]);

            // Ambil geolokasi IP (dipanggil ke ip-api hanya untuk IP baru; sisanya dari cache DB).
            app(IpGeolocationService::class)->resolve($ip);

            $this->pruneOldLogsOncePerDay();
        } catch (\Throwable) {
            // Jangan pernah biarkan proses logging mengganggu request user.
        }
    }

    /**
     * IP asli pengunjung — utamakan header dari Cloudflare/proxy supaya geolokasi akurat
     * (bukan IP server/proxy).
     */
    protected function clientIp(Request $request): ?string
    {
        foreach (['CF-Connecting-IP', 'True-Client-IP', 'X-Real-IP'] as $header) {
            $value = trim((string) $request->header($header));
            if ($value !== '' && filter_var($value, FILTER_VALIDATE_IP) !== false) {
                return $value;
            }
        }

        $forwarded = (string) $request->header('X-Forwarded-For');
        if ($forwarded !== '') {
            $parts = array_map('trim', explode(',', $forwarded));
            // Utamakan IP publik pertama (client asli ada di paling kiri).
            foreach ($parts as $candidate) {
                if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $candidate;
                }
            }
            if (isset($parts[0]) && filter_var($parts[0], FILTER_VALIDATE_IP) !== false) {
                return $parts[0];
            }
        }

        return $request->ip();
    }

    protected function fullPath(Request $request): string
    {
        $path = $request->path();
        $query = $request->getQueryString();

        return $query ? ($path . '?' . $query) : $path;
    }

    protected function shouldSkip(Request $request): bool
    {
        if (! in_array($request->method(), ['GET', 'POST'], true)) {
            return true;
        }

        $path = ltrim($request->path(), '/');

        // Aset statis & endpoint sistem.
        if (preg_match('#\.(css|js|map|png|jpe?g|gif|svg|webp|ico|woff2?|ttf|eot|mp4|webm)$#i', $path)) {
            return true;
        }

        foreach (['build/', 'fonts/', 'images/', 'storage/', 'vendor/', 'favicon', 'robots.txt', 'up', 'admin/logs'] as $skip) {
            if ($path === $skip || str_starts_with($path, $skip)) {
                return true;
            }
        }

        // Polling status & webhook (frekuensi tinggi / server-to-server).
        if (preg_match('#/status$#', $path) || str_contains($path, 'callback')) {
            return true;
        }

        return false;
    }

    protected function pruneOldLogsOncePerDay(): void
    {
        if (! Cache::add('activity-log-prune', 1, now()->addDay())) {
            return;
        }

        DB::table('activity_logs')->where('created_at', '<', now()->subDays(90))->delete();
    }
}
