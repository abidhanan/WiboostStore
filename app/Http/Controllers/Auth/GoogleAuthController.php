<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\WiboostInputRules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

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
        // Kalau user sudah login (mis. halaman callback dibuka ulang / kode ditukar 2x),
        // jangan menukar kode lagi -> mencegah error "invalid_grant".
        if (Auth::check() && ! (Auth::user()?->isAdmin() ?? false)) {
            return redirect()->route('user.dashboard');
        }

        if ($request->filled('error')) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Login Google dibatalkan. Silakan coba lagi.']);
        }

        if (! $request->filled('code')) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Login Google tidak lengkap. Silakan tekan tombol Google lagi.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $exception) {
            try {
                $googleUser = Socialite::driver('google')->stateless()->user();
            } catch (\Throwable $fallbackException) {
                $this->reportIfNotBenign($fallbackException);

                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'Sesi login Google kedaluwarsa. Silakan tekan tombol Google lagi.']);
            }
        } catch (\Throwable $exception) {
            $this->reportIfNotBenign($exception);

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Sesi login Google kedaluwarsa. Silakan tekan tombol Google lagi.']);
        }

        $email = $this->normalizeEmail((string) $googleUser->getEmail());

        if ($email === '') {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Akun Google tidak mengirim alamat email.']);
        }

        $googleId = trim((string) $googleUser->getId());
        $this->ensureBaseRoles();

        $user = $this->findGoogleUser($googleId, $email);

        if ($user?->isAdmin()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Akun admin hanya bisa login melalui halaman login admin.']);
        }

        $created = false;

        if (! $user) {
            try {
                $user = User::create([
                    'name' => $this->safeName($googleUser->getName(), $email),
                    'email' => $email,
                    'google_id' => $googleId !== '' ? $googleId : null,
                    'google_avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(48)),
                    'role_id' => User::ROLE_BUYER,
                ]);
                $created = true;
            } catch (QueryException $exception) {
                if (! $this->isDuplicateGoogleAccount($exception)) {
                    throw $exception;
                }

                $user = $this->findGoogleUser($googleId, $email);

                if (! $user) {
                    throw $exception;
                }
            }
        }

        if ($user?->isAdmin()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Akun admin hanya bisa login melalui halaman login admin.']);
        }

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

        if ($created) {
            event(new Registered($user));
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');
        $request->session()->forget('wiboost_display_seen_dashboard');
        $request->session()->put('wiboost_show_displays_after_login', true);

        return redirect()->route('user.dashboard');
    }

    protected function reportIfNotBenign(\Throwable $exception): void
    {
        // "invalid_grant" = kode otorisasi kedaluwarsa/sudah dipakai, dan state tidak valid = sesi kedaluwarsa.
        // Keduanya alur normal (user tekan ulang / callback dibuka lagi), bukan bug -> tidak perlu dilaporkan.
        $benign = $exception instanceof InvalidStateException
            || str_contains(strtolower($exception->getMessage()), 'invalid_grant');

        if (! $benign) {
            report($exception);
        }
    }

    private function normalizeEmail(string $email): string
    {
        $email = preg_replace('/[\p{Z}\s\p{C}]+/u', '', $email) ?? $email;

        return Str::lower(trim($email));
    }

    private function findGoogleUser(string $googleId, string $email): ?User
    {
        if ($googleId !== '') {
            $user = User::query()->where('google_id', $googleId)->first();

            if ($user) {
                return $user;
            }
        }

        return User::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->orWhereRaw("LOWER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(email), ' ', ''), CHAR(9), ''), CHAR(10), ''), CHAR(13), '')) = ?", [$email])
            ->first();
    }

    private function isDuplicateGoogleAccount(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'Duplicate entry')
            || str_contains($message, 'users_email_unique')
            || str_contains($message, 'users_google_id_unique');
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
