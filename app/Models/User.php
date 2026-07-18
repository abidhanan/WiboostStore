<?php

namespace App\Models;

use App\Notifications\WiboostResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        'whatsapp',
        'password',
        'role_id',
        'balance',
        'points', // <-- TAMBAHAN KOLOM POIN
        'point_reward_stage',
        'point_reward_period',
        'profile_photo_path',
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
            $targetRoleId = $this->reseller_joined_at ? 3 : 2;
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
        $roleId = in_array((int) $this->role_id, [self::ROLE_BUYER, self::ROLE_RESELLER], true) ? (int) $this->role_id : self::ROLE_BUYER;

        $this->forceFill([
            'role_id' => $roleId,
            'membership_started_at' => $this->membership_started_at ?: now(),
            'membership_expires_at' => $base->addDays($days),
        ])->save();
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
