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
        'points', // <-- TAMBAHAN KOLOM POIN
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
        if (! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Mail::raw(
                "Halo {$this->short_name},\n\nSelamat bergabung menjadi member Wiboost Store!\n\nMembership kamu sudah aktif. Nikmati produk dan layanan dengan harga yang lebih murah serta bebas biaya admin untuk transaksi.\n\nTerima kasih,\nWiboost Store",
                function ($message) {
                    $message->to($this->email, $this->name)
                        ->subject('Selamat Bergabung Menjadi Member Wiboost Store');
                }
            );
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function sendResellerJoinedEmail(): void
    {
        if (! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Mail::raw(
                "Halo {$this->short_name},\n\nSelamat bergabung menjadi reseller Wiboost Store!\n\nDashboard reseller kamu sudah aktif. Kamu bisa mulai promosikan produk dan mendapatkan komisi dari transaksi sukses.\n\nTerima kasih,\nWiboost Store",
                function ($message) {
                    $message->to($this->email, $this->name)
                        ->subject('Selamat Bergabung Menjadi Reseller Wiboost Store');
                }
            );
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

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
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
