<?php

namespace App\Support;

class WiboostInputRules
{
    public const NAME_PATTERN = '/\A[\p{L}\s]+\z/u';

    public const NUMBER_PATTERN = '/\A[0-9]+\z/';

    public static function nameRules(int $max = 255): array
    {
        return ['required', 'string', 'max:' . $max, 'regex:' . self::NAME_PATTERN];
    }

    public static function nullableNameRules(int $max = 255): array
    {
        return ['nullable', 'string', 'max:' . $max, 'regex:' . self::NAME_PATTERN];
    }

    public static function numberRules(int $max = 40): array
    {
        return ['required', 'string', 'max:' . $max, 'regex:' . self::NUMBER_PATTERN];
    }

    public static function nullableNumberRules(int $max = 40): array
    {
        return ['nullable', 'string', 'max:' . $max, 'regex:' . self::NUMBER_PATTERN];
    }

    public static function validName(?string $value): bool
    {
        $value = trim((string) $value);

        return $value !== '' && preg_match(self::NAME_PATTERN, $value) === 1;
    }

    public static function validationMessages(): array
    {
        return [
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'reseller_payout_name.regex' => 'Nama pemilik e-wallet hanya boleh berisi huruf dan spasi.',
            'whatsapp.regex' => 'Nomor kontak hanya boleh berisi angka.',
            'checkout_whatsapp.regex' => 'Nomor WhatsApp hanya boleh berisi angka.',
            'reseller_payout_account.regex' => 'Nomor e-wallet hanya boleh berisi angka.',
            'admin_cs_whatsapp.regex' => 'Nomor Admin CS hanya boleh berisi angka.',
            'admin_otp_whatsapp.regex' => 'Nomor Admin OTP hanya boleh berisi angka.',
        ];
    }

    public static function invalidNameWarning(): string
    {
        return 'Nama akun kamu hanya boleh menggunakan huruf dan spasi. Ubah nama di Pengaturan Akun agar bisa melakukan transaksi.';
    }
}
