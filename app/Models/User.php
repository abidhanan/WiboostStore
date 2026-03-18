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
        'whatsapp', // <-- Tambahan kolom WhatsApp
        'password',
        'role_id',
        'balance',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Cek apakah user adalah Super Admin
    public function isSuperAdmin()
    {
        return $this->role_id === 1; 
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