<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses percobaan autentikasi.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Validasi kredensial email & password
        $request->authenticate();

        // 2. Buat sesi baru untuk keamanan
        $request->session()->regenerate();

        // 3. LOGIKA REDIRECT BERDASARKAN ROLE ID
        $role = $request->user()->role_id;

        if (in_array($role, [1, 2, 3, 4])) {
            // Jika Manajemen (Super Admin, Admin, Office, Stok)
            return redirect()->intended(route('admin.dashboard'));
        }

        // Jika Pelanggan Biasa (Role 5)
        return redirect()->intended(route('user.dashboard'));
    }

    /**
     * Menghancurkan sesi autentikasi (Logout).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}