<?php

namespace App\Support;

use App\Models\User;
use Throwable;

class WiboostAdminContact
{
    public static function whatsappNumber(): ?string
    {
        $configuredNumber = self::normalizePhone(config('wiboost.admin_contact.whatsapp'));
        if ($configuredNumber !== null) {
            return $configuredNumber;
        }

        try {
            return User::query()
                ->where('role_id', 1)
                ->pluck('whatsapp')
                ->map(fn ($value) => self::normalizePhone($value))
                ->first(fn ($value) => ! empty($value));
        } catch (Throwable) {
            return null;
        }
    }

    public static function reportUrl(): ?string
    {
        $number = self::whatsappNumber();

        if ($number === null) {
            return null;
        }

        return 'https://wa.me/' . $number . '?text=' . rawurlencode(self::reportMessage());
    }

    public static function reportMessage(): string
    {
        $lines = [
            trim((string) config('wiboost.admin_contact.report_intro', 'Halo Admin Wiboost, saya ingin melaporkan kendala di website.')),
        ];

        if (auth()->check()) {
            $lines[] = 'Akun: ' . auth()->user()->name;
        }

        $lines[] = 'Halaman: ' . request()->fullUrl();
        $lines[] = 'Kendala saya:';

        return implode("\n", array_filter($lines));
    }

    protected static function normalizePhone($value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }
}
