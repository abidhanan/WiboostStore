<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'whatsapp',
        'password',
        'role_id',
        'balance',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Cek apakah user adalah Admin (Role 1)
    public function isAdmin()
    {
        return $this->role_id === 1; 
    }

    // Cek apakah user adalah Buyer (Role 2)
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
}