<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  int|string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Ambil ID role dari user yang sedang login
        $userRoleId = Auth::user()->role_id;

        // Cek apakah ID role user tersebut ada di dalam daftar role yang diizinkan untuk halaman ini
        if (in_array($userRoleId, $roles)) {
            return $next($request);
        }

        // Jika tidak punya akses, tampilkan halaman error 403 (Forbidden)
        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}