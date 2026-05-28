<?php

namespace App\Support;

use App\Models\SiteSetting;
use App\Models\User;
use Throwable;

class WiboostAdminContact
{
    private const DEFAULT_ADMIN_WHATSAPP_NUMBER = '0895611244000';
    private const CS_WHATSAPP_SETTING = 'admin_cs_whatsapp';
    private const OTP_WHATSAPP_SETTING = 'admin_otp_whatsapp';

    public static function whatsappNumber(): ?string
    {
        return self::csWhatsappNumber();
    }

    public static function csWhatsappNumber(): ?string
    {
        return self::resolveConfiguredNumber(self::CS_WHATSAPP_SETTING);
    }

    public static function otpWhatsappNumber(): ?string
    {
        return self::resolveConfiguredNumber(self::OTP_WHATSAPP_SETTING) ?? self::csWhatsappNumber();
    }

    protected static function resolveConfiguredNumber(string $settingKey): ?string
    {
        try {
            $savedNumber = self::normalizePhone(SiteSetting::valueFor($settingKey));
            if ($savedNumber !== null) {
                return $savedNumber;
            }
        } catch (Throwable) {
            // The settings table may not exist before migration runs.
        }

        $configuredNumber = self::normalizePhone(
            config('wiboost.admin_contact.whatsapp') ?: self::DEFAULT_ADMIN_WHATSAPP_NUMBER
        );
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
        return self::whatsappUrl(self::reportMessage());
    }

    public static function whatsappUrl(string $message): ?string
    {
        return self::whatsappUrlForNumber(self::whatsappNumber(), $message);
    }

    public static function otpWhatsappUrl(string $message): ?string
    {
        return self::whatsappUrlForNumber(self::otpWhatsappNumber(), $message);
    }

    protected static function whatsappUrlForNumber(?string $number, string $message): ?string
    {
        if ($number === null) {
            return null;
        }

        return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
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
