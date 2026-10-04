<?php

namespace App\Support;

use App\Models\SiteSetting;
use App\Models\OtpAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WiboostAdminContact
{
    private const DEFAULT_ADMIN_WHATSAPP_NUMBER = '0895611244000';
    private const CS_NAME_SETTING = 'admin_cs_name';
    private const CS_WHATSAPP_SETTING = 'admin_cs_whatsapp';
    private const OTP_WHATSAPP_SETTING = 'admin_otp_whatsapp';
    private const OTP_WHATSAPP_NUMBERS_SETTING = 'admin_otp_whatsapps';
    private const OTP_ADMINS_SETTING = 'admin_otp_admins';

    public static function whatsappNumber(): ?string
    {
        return self::csWhatsappNumber();
    }

    public static function csWhatsappNumber(): ?string
    {
        return self::resolveConfiguredNumber(self::CS_WHATSAPP_SETTING);
    }

    public static function csName(): ?string
    {
        try {
            $name = trim((string) SiteSetting::valueFor(self::CS_NAME_SETTING));

            return $name !== '' ? $name : null;
        } catch (Throwable) {
            return null;
        }
    }

    public static function otpWhatsappNumber(): ?string
    {
        return self::otpWhatsappNumbers()[0] ?? self::csWhatsappNumber();
    }

    public static function otpWhatsappNumbers(): array
    {
        return collect(self::otpAdmins())->pluck('number')->all();
    }

    public static function otpAdmins(): array
    {
        try {
            if (Schema::hasTable('otp_admins') && OtpAdmin::query()->exists()) {
                return OtpAdmin::query()
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get(['name', 'whatsapp'])
                    ->map(fn (OtpAdmin $admin) => [
                        'name' => $admin->name,
                        'number' => $admin->whatsapp,
                    ])
                    ->all();
            }
        } catch (Throwable) {
            // Fall back to settings while the OTP table is unavailable.
        }

        try {
            $savedAdmins = json_decode((string) SiteSetting::valueFor(self::OTP_ADMINS_SETTING), true);

            if (is_array($savedAdmins)) {
                $admins = collect($savedAdmins)
                    ->map(function ($admin, $index) {
                        if (! is_array($admin)) {
                            return null;
                        }

                        $number = self::normalizePhone($admin['number'] ?? null);
                        if ($number === null) {
                            return null;
                        }

                        $name = trim((string) ($admin['name'] ?? ''));

                        return [
                            'name' => $name !== '' ? $name : 'Admin OTP ' . ($index + 1),
                            'number' => $number,
                        ];
                    })
                    ->filter()
                    ->unique('number')
                    ->values()
                    ->all();

                if ($admins !== []) {
                    return $admins;
                }
            }

            $savedNumbers = json_decode((string) SiteSetting::valueFor(self::OTP_WHATSAPP_NUMBERS_SETTING), true);

            if (is_array($savedNumbers)) {
                $numbers = collect($savedNumbers)
                    ->map(fn ($number) => self::normalizePhone($number))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if ($numbers !== []) {
                    return collect($numbers)
                        ->map(fn ($number, $index) => [
                            'name' => 'Admin OTP ' . ($index + 1),
                            'number' => $number,
                        ])
                        ->all();
                }
            }
        } catch (Throwable) {
            // The settings table may not exist before migration runs.
        }

        $legacyNumber = self::resolveConfiguredNumber(self::OTP_WHATSAPP_SETTING);

        return $legacyNumber !== null
            ? [['name' => 'Admin OTP', 'number' => $legacyNumber]]
            : [];
    }

    public static function otpAdminNameForNumber(?string $number): ?string
    {
        $normalizedNumber = self::normalizePhone($number);

        if ($normalizedNumber === null) {
            return null;
        }

        return collect(self::otpAdmins())->firstWhere('number', $normalizedNumber)['name'] ?? null;
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

        $configuredNumber = self::normalizePhone(config('wiboost.admin_contact.whatsapp'));
        if ($configuredNumber !== null) {
            return $configuredNumber;
        }

        try {
            $adminNumber = User::query()
                ->where('role_id', 1)
                ->pluck('whatsapp')
                ->map(fn ($value) => self::normalizePhone($value))
                ->first(fn ($value) => ! empty($value));

            if ($adminNumber !== null) {
                return $adminNumber;
            }
        } catch (Throwable) {
            // The users table may not exist before migration runs.
        }

        return self::normalizePhone(self::DEFAULT_ADMIN_WHATSAPP_NUMBER);
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

    public static function otpWhatsappUrlForNumber(?string $number, string $message): ?string
    {
        return self::whatsappUrlForNumber(self::normalizePhone($number) ?? self::otpWhatsappNumber(), $message);
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

    public static function normalizePhone($value): ?string
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
