<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\WiboostInputRules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Login Google belum dikonfigurasi. Hubungi admin.']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Login Google gagal. Coba lagi.']);
        }

        $email = Str::lower(trim((string) $googleUser->getEmail()));

        if ($email === '') {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Akun Google tidak mengirim alamat email.']);
        }

        $googleId = trim((string) $googleUser->getId());
        $this->ensureBaseRoles();

        $user = $googleId !== ''
            ? User::query()->where('google_id', $googleId)->first()
            : null;

        if (! $user) {
            $user = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();
        }

        if ($user?->isAdmin()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Akun admin hanya bisa login melalui halaman login admin.']);
        }

        if (! $user) {
            $user = User::create([
                'name' => $this->safeName($googleUser->getName(), $email),
                'email' => $email,
                'google_id' => $googleId !== '' ? $googleId : null,
                'google_avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(48)),
                'role_id' => User::ROLE_BUYER,
            ]);

            event(new Registered($user));
        } else {
            $updates = [
                'google_avatar' => $googleUser->getAvatar() ?: $user->google_avatar,
            ];

            if ($googleId !== '' && blank($user->google_id)) {
                $updates['google_id'] = $googleId;
            }

            if (blank($user->email_verified_at)) {
                $updates['email_verified_at'] = now();
            }

            if (! WiboostInputRules::validName($user->name)) {
                $updates['name'] = $this->safeName($googleUser->getName(), $email);
            }

            $user->forceFill($updates)->save();
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');
        $request->session()->forget('wiboost_display_seen_dashboard');
        $request->session()->put('wiboost_show_displays_after_login', true);

        return redirect()->route('user.dashboard');
    }

    private function safeName(?string $name, string $email): string
    {
        $candidate = trim((string) ($name ?: Str::before($email, '@')));
        $candidate = preg_replace('/[^\p{L}\s]+/u', ' ', $candidate) ?: '';
        $candidate = trim(preg_replace('/\s+/u', ' ', $candidate) ?: '');

        if (! WiboostInputRules::validName($candidate)) {
            return 'Pengguna Google';
        }

        return Str::limit($candidate, 255, '');
    }

    private function ensureBaseRoles(): void
    {
        foreach ([
            User::ROLE_SUPER_ADMIN => 'Super Admin',
            User::ROLE_BUYER => 'Buyer',
            User::ROLE_RESELLER => 'Reseller',
            User::ROLE_FINANCE => 'Keuangan',
            User::ROLE_LEGACY_BUYER => 'Buyer',
            User::ROLE_WAREHOUSE => 'Gudang',
            User::ROLE_MARKETING => 'Marketing',
            User::ROLE_CUSTOMER_SERVICE => 'Customer Service',
        ] as $roleId => $roleName) {
            Role::query()->updateOrCreate(['id' => $roleId], ['name' => $roleName]);
        }
    }
}
