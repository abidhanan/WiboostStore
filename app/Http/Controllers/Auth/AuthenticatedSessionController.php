<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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

        if ($request->user()?->isAdmin()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun admin hanya bisa login melalui halaman login admin.',
            ]);
        }

        // 2. Buat sesi baru untuk keamanan
        $request->session()->regenerate();

        // 3. LOGIKA REDIRECT BERDASARKAN ROLE BARU (Hanya 2 Role)
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Menghancurkan sesi autentikasi (Logout).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
