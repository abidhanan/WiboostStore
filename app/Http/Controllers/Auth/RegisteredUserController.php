<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Menampilkan halaman registrasi.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Memproses data pendaftaran user baru.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi input dari form register (tambah whatsapp)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'whatsapp' => ['required', 'string', 'max:20'], // <-- Validasi WhatsApp
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Simpan user baru ke database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp, // <-- Simpan WhatsApp
            'password' => Hash::make($request->password),
        ]);

        // 3. Memicu event Registered
        event(new Registered($user));

        // 4. Langsung login otomatis setelah berhasil mendaftar
        Auth::login($user);

        // 5. REDIRECT CUSTOM: Arahkan ke Dasbor khusus pelanggan Wiboost Store
        return redirect()->intended(route('user.dashboard', absolute: false));
    }
}