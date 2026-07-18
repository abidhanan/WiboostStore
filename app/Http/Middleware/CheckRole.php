<?php

namespace App\Http\Middleware;

use App\Models\User;
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

        if (method_exists($user, 'syncExpiredMembershipRole') && $user->syncExpiredMembershipRole()) {
            $user = $user->fresh();
            Auth::setUser($user);
            $request->setUserResolver(fn () => $user);
        }

        // 2. Jika tidak ada role spesifik yang didefinisikan di route, biarkan masuk
        if (empty($roles)) {
            return $next($request);
        }

        $allowedRoles = [];
        foreach ($roles as $role) {
            $roleLower = str_replace(['_', '-'], ' ', strtolower(trim((string) $role)));
            
            if ($roleLower === 'admin') {
                $allowedRoles = array_merge($allowedRoles, User::ADMIN_ROLE_IDS);
            } elseif ($roleLower === 'super admin' || (string) $role === '1') {
                $allowedRoles[] = User::ROLE_SUPER_ADMIN;
            } elseif ($roleLower === 'keuangan' || $roleLower === 'kuangan' || $roleLower === 'finance' || (string) $role === '4') {
                $allowedRoles[] = User::ROLE_FINANCE;
            } elseif ($roleLower === 'gudang' || $roleLower === 'warehouse' || (string) $role === '6') {
                $allowedRoles[] = User::ROLE_WAREHOUSE;
            } elseif ($roleLower === 'marketing' || (string) $role === '7') {
                $allowedRoles[] = User::ROLE_MARKETING;
            } elseif ($roleLower === 'customer service' || $roleLower === 'cs' || (string) $role === '8') {
                $allowedRoles[] = User::ROLE_CUSTOMER_SERVICE;
            } elseif ($roleLower === 'buyer' || $roleLower === 'user' || (string) $role === '2' || (string) $role === '5') {
                $allowedRoles = array_merge($allowedRoles, User::BUYER_ROLE_IDS);
            } elseif ($roleLower === 'reseller' || (string) $role === '3') {
                $allowedRoles[] = User::ROLE_RESELLER;
            } else {
                $allowedRoles[] = (int) $role;
            }
        }

        $allowedRoles = array_values(array_unique(array_map('intval', $allowedRoles)));

        if (in_array((int) $user->role_id, $allowedRoles, true)) {
            return $next($request);
        }

        // 5. Jika tidak punya akses, tendang dengan error 403
        abort(403, 'ANDA TIDAK MEMILIKI IZIN UNTUK MENGAKSES HALAMAN INI.');
    }
}
