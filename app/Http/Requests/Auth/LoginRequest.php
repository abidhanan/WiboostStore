<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => $this->normalizeEmail((string) $this->input('email')),
            ]);
        }
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->normalizeEmail((string) $this->input('email'));
        $password = (string) $this->input('password');

        foreach ($this->passwordCandidates($password) as $passwordCandidate) {
            if (Auth::attempt(['email' => $email, 'password' => $passwordCandidate], $this->boolean('remember'))
                || $this->attemptStoredPassword($email, $passwordCandidate)) {
                RateLimiter::clear($this->throttleKey());

                return;
            }
        }

        $user = $this->findUserByNormalizedEmail($email);

        RateLimiter::hit($this->throttleKey());

        if ($user && filled($user->google_id)) {
            throw ValidationException::withMessages([
                'email' => 'Akun ini terhubung dengan Google. Silakan masuk memakai tombol Google atau reset kata sandi dulu.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau kata sandi belum sesuai.',
        ]);
    }

    protected function normalizeEmail(string $email): string
    {
        $email = preg_replace('/[\p{Z}\s\p{C}]+/u', '', $email) ?? $email;

        return Str::lower(trim($email));
    }

    protected function passwordCandidates(string $password): array
    {
        $edgeTrimmedPassword = preg_replace('/^[\p{Z}\s\p{C}]+|[\p{Z}\s\p{C}]+$/u', '', $password) ?? trim($password);
        $withoutInvisible = preg_replace('/\p{Cf}+/u', '', $password) ?? $password;
        $withoutInvisibleTrimmed = preg_replace('/^[\p{Z}\s\p{C}]+|[\p{Z}\s\p{C}]+$/u', '', $withoutInvisible) ?? trim($withoutInvisible);

        return array_values(array_unique([
            $password,
            $edgeTrimmedPassword,
            $withoutInvisible,
            $withoutInvisibleTrimmed,
        ]));
    }

    protected function findUserByNormalizedEmail(string $email): ?User
    {
        return User::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->orWhereRaw("LOWER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(email), ' ', ''), CHAR(9), ''), CHAR(10), ''), CHAR(13), '')) = ?", [$email])
            ->first();
    }

    protected function attemptStoredPassword(string $email, string $password): bool
    {
        $user = $this->findUserByNormalizedEmail($email);

        if (! $user) {
            return false;
        }

        $hashMatches = Hash::check($password, (string) $user->password);
        $legacyPlainMatches = ! $hashMatches && hash_equals((string) $user->password, $password);

        if (! $hashMatches && ! $legacyPlainMatches) {
            return false;
        }

        if ($legacyPlainMatches || Hash::needsRehash((string) $user->password)) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
        }

        Auth::login($user, $this->boolean('remember'));

        return true;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate($this->normalizeEmail((string) $this->input('email')).'|'.$this->ip());
    }
}
