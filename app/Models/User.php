<?php

namespace App\Models;

use App\Notifications\WiboostResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'points' => 'integer',
    ];

    public function isAdmin()
    {
        return $this->role_id === 1; 
    }

    public function isBuyer()
    {
        return $this->role_id === 2; 
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new WiboostResetPasswordNotification($token));
    }
}
