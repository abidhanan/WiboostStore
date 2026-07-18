<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminPageAccess
{
    public function handle(Request $request, Closure $next, ...$areas): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (method_exists($user, 'syncExpiredMembershipRole') && $user->syncExpiredMembershipRole()) {
            $user = $user->fresh();
            Auth::setUser($user);
            $request->setUserResolver(fn () => $user);
        }

        $roleId = (int) $user->role_id;

        if ($roleId === User::ROLE_SUPER_ADMIN) {
            return $next($request);
        }

        $accessMap = [
            User::ROLE_FINANCE => ['dashboard', 'commissions', 'users', 'transactions', 'deposits', 'withdrawals'],
            User::ROLE_WAREHOUSE => ['dashboard', 'users', 'categories', 'products', 'tutorials', 'transactions'],
            User::ROLE_MARKETING => ['dashboard', 'users', 'transactions', 'promos', 'displays'],
            User::ROLE_CUSTOMER_SERVICE => ['dashboard', 'commissions', 'users', 'transactions', 'deposits', 'reports'],
        ];

        $allowedAreas = $accessMap[$roleId] ?? [];
        $requestedAreas = $areas !== [] ? $areas : ['dashboard'];

        foreach ($requestedAreas as $area) {
            if (in_array($area, $allowedAreas, true)) {
                return $next($request);
            }
        }

        abort(403, 'ANDA TIDAK MEMILIKI IZIN UNTUK MENGAKSES HALAMAN ADMIN INI.');
    }
}
