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

        if (in_array($roleId, User::ADMIN_ROLE_IDS, true)) {
            return $next($request);
        }

        abort(403, 'ANDA TIDAK MEMILIKI IZIN UNTUK MENGAKSES HALAMAN ADMIN INI.');
    }
}
