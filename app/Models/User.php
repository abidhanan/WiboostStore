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
    ];

    public function isAdmin()
    {
        return (int) $this->role_id === 1; 
    }

    public function isBuyer()
    {
        return (int) $this->role_id === 2; 
    }

    public function isReseller(): bool
    {
        return (int) $this->role_id === 3;
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
