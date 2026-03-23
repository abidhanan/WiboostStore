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
     * @param  mixed  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Pastikan user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 2. Jika tidak ada role spesifik yang didefinisikan di route, biarkan masuk
        if (empty($roles)) {
            return $next($request);
        }

        // 3. Konversi parameter role yang diminta menjadi ID Role sistem baru
        // Ini membuat middleware sangat fleksibel (bisa pakai angka atau teks)
        $allowedRoles = [];
        foreach ($roles as $role) {
            $roleLower = strtolower(trim($role));
            
            if ($roleLower === 'admin' || $role == 1) {
                $allowedRoles[] = 1; // Admin = 1
            } elseif ($roleLower === 'buyer' || $roleLower === 'user' || $role == 2) {
                $allowedRoles[] = 2; // Buyer = 2
            } elseif ($role == 5) {
                // Sisaan sistem lama: Jika route masih minta role 5, kita arahkan ke 2 (Buyer)
                $allowedRoles[] = 2; 
            } else {
                $allowedRoles[] = (int) $role;
            }
        }

        // 4. Cek apakah role_id user ada di dalam daftar role yang diizinkan
        if (in_array($user->role_id, $allowedRoles)) {
            return $next($request);
        }

        // 5. Jika tidak punya akses, tendang dengan error 403
        abort(403, 'ANDA TIDAK MEMILIKI IZIN UNTUK MENGAKSES HALAMAN INI.');
    }
}