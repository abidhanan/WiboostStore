<?php

namespace App\Models;

use App\Notifications\WiboostResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 1;

    public const ROLE_BUYER = 2;

    public const ROLE_RESELLER = 3;

    public const ROLE_FINANCE = 4;

    public const ROLE_LEGACY_BUYER = 5;

    public const ROLE_WAREHOUSE = 6;

    public const ROLE_MARKETING = 7;

    public const ROLE_CUSTOMER_SERVICE = 8;

    public const ADMIN_ROLE_IDS = [
        self::ROLE_SUPER_ADMIN,
        self::ROLE_FINANCE,
        self::ROLE_WAREHOUSE,
        self::ROLE_MARKETING,
        self::ROLE_CUSTOMER_SERVICE,
    ];

    public const BUYER_ROLE_IDS = [
        self::ROLE_BUYER,
        self::ROLE_LEGACY_BUYER,
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'google_id',
        'whatsapp',
        'password',
        'role_id',
        'balance',
        'points',
        'point_reward_stage',
        'point_reward_period',
        'profile_photo_path',
        'google_avatar',
        'reseller_payout_method',
        'reseller_payout_account',
        'reseller_payout_name',
        'reseller_joined_at',
        'membership_started_at',
        'membership_expires_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'role_id' => 'integer',
        'balance' => 'decimal:2',
        'points' => 'integer',
        'point_reward_stage' => 'integer',
        'reseller_joined_at' => 'datetime',
        'membership_started_at' => 'datetime',
        'membership_expires_at' => 'datetime',
    ];

    public function isAdmin()
    {
        return in_array((int) $this->role_id, self::ADMIN_ROLE_IDS, true);
    }

    public function isSuperAdmin(): bool
    {
        return (int) $this->role_id === self::ROLE_SUPER_ADMIN;
    }

    public function isBuyer()
    {
        return in_array((int) $this->role_id, self::BUYER_ROLE_IDS, true);
    }

    public function isReseller(): bool
    {
        return (int) $this->role_id === self::ROLE_RESELLER && ! $this->isMember();
    }

    public function hasResellerCommissionAccess(): bool
    {
        return (int) $this->role_id === self::ROLE_RESELLER
            || $this->reseller_joined_at !== null
            || filled($this->reseller_payout_method)
            || filled($this->reseller_payout_account)
            || filled($this->reseller_payout_name)
            || $this->resellerCommissions()->where('type', 'income')->exists();
    }

    public function scopeWithResellerCommissionAccess($query)
    {
        return $query->where(function ($query) {
            $query->where('role_id', self::ROLE_RESELLER)
                ->orWhereNotNull('reseller_joined_at')
                ->orWhere(function ($payoutQuery) {
                    $payoutQuery->whereNotNull('reseller_payout_account')
                        ->where('reseller_payout_account', '!=', '');
                })
                ->orWhere(function ($payoutQuery) {
                    $payoutQuery->whereNotNull('reseller_payout_name')
                        ->where('reseller_payout_name', '!=', '');
                })
                ->orWhereHas('resellerCommissions', fn ($commissionQuery) => $commissionQuery->where('type', 'income'));
        });
    }

    public function isMember(): bool
    {
        return in_array((int) $this->role_id, [self::ROLE_BUYER, self::ROLE_RESELLER, self::ROLE_LEGACY_BUYER], true)
            && $this->membership_expires_at !== null
            && $this->membership_expires_at->isFuture();
    }

    public static function baseRoleLabels(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_BUYER => 'Buyer',
            self::ROLE_RESELLER => 'Reseller',
            self::ROLE_FINANCE => 'Keuangan',
            self::ROLE_LEGACY_BUYER => 'Buyer',
            self::ROLE_WAREHOUSE => 'Gudang',
            self::ROLE_MARKETING => 'Marketing',
            self::ROLE_CUSTOMER_SERVICE => 'Customer Service',
        ];
    }

    public function baseRoleLabel(): string
    {
        return self::baseRoleLabels()[(int) $this->role_id] ?? 'Buyer';
    }

    public function effectiveRoleLabel(): string
    {
        return $this->isMember() ? 'Member' : $this->baseRoleLabel();
    }

    public function syncExpiredMembershipRole(): bool
    {
        if ($this->membership_expires_at === null || $this->membership_expires_at->isFuture()) {
            return false;
        }

        $targetRoleId = (int) $this->role_id;

        if (! $this->isAdmin()) {
            $targetRoleId = $this->hasResellerCommissionAccess() ? self::ROLE_RESELLER : self::ROLE_BUYER;
        }

        if ((int) $this->role_id === $targetRoleId) {
            return false;
        }

        $this->forceFill([
            'role_id' => $targetRoleId,
        ])->saveQuietly();

        $this->refresh();

        return true;
    }

    public function activateMembership(int $days = 30): void
    {
        $base = $this->isMember() ? $this->membership_expires_at->copy() : now();
        $hasResellerAccess = $this->hasResellerCommissionAccess();
        $roleId = $hasResellerAccess
            ? self::ROLE_RESELLER
            : (in_array((int) $this->role_id, [self::ROLE_BUYER, self::ROLE_LEGACY_BUYER], true) ? (int) $this->role_id : self::ROLE_BUYER);

        $updates = [
            'role_id' => $roleId,
            'membership_started_at' => $this->membership_started_at ?: now(),
            'membership_expires_at' => $base->addDays($days),
        ];

        if ($hasResellerAccess && $this->reseller_joined_at === null) {
            $updates['reseller_joined_at'] = now();
        }

        $this->forceFill($updates)->save();

        $this->sendMembershipJoinedEmail();
    }

    public function sendMembershipJoinedEmail(): void
    {
        $this->sendAccountStatusEmail(
            subject: 'Selamat Bergabung Menjadi Member Wiboost Store',
            headline: 'Membership Aktif!',
            badge: 'Member Wiboost',
            intro: 'Membership kamu sudah aktif. Nikmati produk dan layanan dengan harga yang jauh lebih murah serta bebas biaya admin untuk transaksi.',
            accentColor: '#8b5cf6',
            actionLabel: 'Buka Dashboard Member',
            actionUrl: route('user.membership.index'),
            rows: [
                'Status Akun' => 'Member',
                'Durasi Membership' => '30 Hari',
                'Benefit Utama' => 'Harga lebih murah dan bebas biaya admin',
            ],
        );
    }

    public function sendResellerJoinedEmail(): void
    {
        $this->sendAccountStatusEmail(
            subject: 'Selamat Bergabung Menjadi Reseller Wiboost Store',
            headline: 'Reseller Aktif!',
            badge: 'Reseller Wiboost',
            intro: 'Dashboard reseller kamu sudah aktif. Kamu bisa mulai promosikan produk dan mendapatkan komisi dari setiap transaksi sukses.',
            accentColor: '#f2b544',
            actionLabel: 'Buka Dashboard Reseller',
            actionUrl: route('user.reseller.index'),
            rows: [
                'Status Akun' => 'Reseller',
                'Komisi' => 'Masuk otomatis dari transaksi sukses',
                'Penarikan Minimal' => 'Rp 20.000',
            ],
        );
    }

    protected function sendAccountStatusEmail(
        string $subject,
        string $headline,
        string $badge,
        string $intro,
        string $accentColor,
        string $actionLabel,
        string $actionUrl,
        array $rows
    ): void {
        if (! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $safe = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $logoPath = public_path('images/wiboost-logo-nav.png');
        $logoUrl = '__WIBOOST_INLINE_LOGO__';
        $rowHtml = '';

        foreach ($rows as $label => $value) {
            $rowHtml .= '
                <tr>
                    <td style="padding:0 0 10px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="width:100%;border-collapse:separate;border-spacing:0;border:2px solid #dbeafe;border-radius:18px;background:#ffffff;">
                            <tr>
                                <td style="padding:14px 16px;">
                                    <p style="margin:0 0 5px;font-size:10px;line-height:1.3;font-weight:900;letter-spacing:1.6px;text-transform:uppercase;color:#5a76c8;">'.$safe($label).'</p>
                                    <p style="margin:0;font-size:16px;line-height:1.45;font-weight:900;color:#2b3a67;word-break:break-word;">'.$safe($value).'</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>';
        }

        $html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>'.$safe($subject).'</title>
</head>
<body style="margin:0;padding:0;background:#eff6ff;font-family:Arial,Helvetica,sans-serif;color:#2b3a67;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#eff6ff" style="width:100%;border-collapse:collapse;background:#eff6ff;">
    <tr>
        <td align="center" style="padding:22px 10px;">
            <table role="presentation" width="640" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="width:100%;max-width:640px;border-collapse:separate;border-spacing:0;background:#ffffff;border:4px solid #dbeafe;border-radius:30px;overflow:hidden;box-shadow:0 22px 60px rgba(90,118,200,.18);">
                <tr>
                    <td align="center" bgcolor="#dbeafe" style="padding:30px 22px;background:#dbeafe;background-image:linear-gradient(135deg,#bfdbfe 0%,#dbeafe 55%,#eff6ff 100%);text-align:center;">
                        <img src="'.$safe($logoUrl).'" alt="Logo Wiboost Store" width="72" height="72" style="display:block;width:72px;height:72px;margin:0 auto 12px;border:0;border-radius:999px;object-fit:contain;">
                        <p style="margin:0;font-size:24px;line-height:1.15;font-weight:900;color:#2b3a67;text-align:center;">Wiboost <span style="color:#5a76c8;">Store</span></p>
                        <p style="display:inline-block;margin:22px auto 0;padding:8px 18px;border-radius:999px;background:#ffffff;color:'.$safe($accentColor).';font-size:11px;line-height:1.3;font-weight:900;letter-spacing:1.8px;text-transform:uppercase;box-shadow:0 8px 20px rgba(90,118,200,.14);">'.$safe($badge).'</p>
                        <h1 style="margin:18px 0 10px;font-size:30px;line-height:1.12;font-weight:900;color:#2b3a67;text-align:center;">'.$safe($headline).'</h1>
                        <p style="margin:0 auto;max-width:520px;font-size:15px;line-height:1.65;font-weight:700;color:#5a76c8;text-align:center;">Halo '.$safe($this->short_name).', '.$safe($intro).'</p>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#f8fbff" style="padding:22px;background:#f8fbff;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:separate;border-spacing:0;">
                            '.$rowHtml.'
                        </table>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin-top:18px;border-collapse:collapse;">
                            <tr>
                                <td align="center" style="text-align:center;">
                                    <a href="'.$safe($actionUrl).'" target="_blank" style="display:inline-block;min-width:210px;border:3px solid #ffffff;border-radius:999px;background:#5a76c8;color:#ffffff;font-size:14px;line-height:1.2;font-weight:900;text-align:center;text-decoration:none;padding:15px 24px;box-shadow:0 14px 28px rgba(90,118,200,.25);">'.$safe($actionLabel).'</a>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:24px 0 2px;text-align:center;font-size:11px;line-height:1.6;font-weight:700;color:#5a76c8;">Email ini otomatis dikirim oleh sistem Wiboost Store.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>';

        try {
            Mail::send([], [], function ($message) use ($subject, $html, $logoPath) {
                    $inlineLogoUrl = file_exists($logoPath)
                        ? $message->embed($logoPath)
                        : secure_url('images/wiboost-logo-nav.png').'?v=20260723-mail';

                    $message->to($this->email, $this->name)
                        ->subject($subject)
                        ->html(str_replace('__WIBOOST_INLINE_LOGO__', $inlineLogoUrl, $html));
            });
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function resellerCommissions()
    {
        return $this->hasMany(ResellerCommission::class);
    }

    public function resellerWithdrawals()
    {
        return $this->hasMany(ResellerCommission::class)->where('type', 'withdrawal');
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_path) {
            return Storage::disk('public')->url($this->profile_photo_path);
        }

        if ($this->google_avatar) {
            return $this->google_avatar;
        }

        return self::defaultProfilePhotoUrl();
    }

    public static function defaultProfilePhotoUrl(): string
    {
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 192" role="img" aria-label="Foto profil default">
  <rect width="192" height="192" rx="96" fill="#ffffff"/>
  <circle cx="96" cy="76" r="28" fill="none" stroke="#94a3b8" stroke-width="12"/>
  <path d="M49 153c5.7-29 24.5-45 47-45s41.3 16 47 45" fill="none" stroke="#94a3b8" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function getShortNameAttribute(): string
    {
        $name = trim((string) $this->name);

        if ($name === '') {
            return 'User';
        }

        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return implode(' ', array_slice($parts, 0, 2)) ?: 'User';
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new WiboostResetPasswordNotification($token));
    }
}
