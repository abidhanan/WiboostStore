<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('otp_admins')) {
            Schema::create('otp_admins', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80);
                $table->string('whatsapp', 40)->unique();
                $table->enum('status', ['active', 'inactive'])->default('active')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('site_settings') || DB::table('otp_admins')->exists()) {
            return;
        }

        $normalize = function ($value): ?string {
            $digits = preg_replace('/\D+/', '', (string) $value);

            if ($digits === '') {
                return null;
            }

            if (str_starts_with($digits, '0')) {
                return '62' . substr($digits, 1);
            }

            return str_starts_with($digits, '8') ? '62' . $digits : $digits;
        };

        $admins = json_decode((string) DB::table('site_settings')->where('key', 'admin_otp_admins')->value('value'), true);

        if (! is_array($admins) || $admins === []) {
            $numbers = json_decode((string) DB::table('site_settings')->where('key', 'admin_otp_whatsapps')->value('value'), true);
            $admins = collect(is_array($numbers) ? $numbers : [])
                ->map(fn ($number, $index) => ['name' => 'Admin OTP ' . ($index + 1), 'number' => $number])
                ->all();
        }

        $now = now();
        $rows = collect($admins)
            ->map(function ($admin, $index) use ($normalize, $now) {
                if (! is_array($admin)) {
                    return null;
                }

                $number = $normalize($admin['number'] ?? null);
                if ($number === null) {
                    return null;
                }

                $name = trim((string) ($admin['name'] ?? ''));

                return [
                    'name' => $name !== '' ? $name : 'Admin OTP ' . ($index + 1),
                    'whatsapp' => $number,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter()
            ->unique('whatsapp')
            ->values()
            ->all();

        if ($rows !== []) {
            DB::table('otp_admins')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_admins');
    }
};
