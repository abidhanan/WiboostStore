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

        $this->forgetOrderCheckoutIntendedUrl($request);

        // 3. Arahkan user biasa langsung ke dashboard user.
        return redirect()->intended(route('user.dashboard', absolute: false));
    }

    protected function forgetOrderCheckoutIntendedUrl(Request $request): void
    {
        $intendedUrl = $request->session()->get('url.intended');

        if (! is_string($intendedUrl) || $intendedUrl === '') {
            return;
        }

        $path = parse_url($intendedUrl, PHP_URL_PATH);

        if (! is_string($path)) {
            return;
        }

        $normalizedPath = '/' . ltrim($path, '/');

        if (preg_match('#^/(order|checkout)(/|$)#', $normalizedPath) === 1
            || preg_match('#^/user/(order|checkout)(/|$)#', $normalizedPath) === 1) {
            $request->session()->forget('url.intended');
        }
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
