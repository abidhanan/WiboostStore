<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpAdmin extends Model
{
    protected $fillable = [
        'name',
        'whatsapp',
        'status',
    ];

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'Aktif' : 'Nonaktif';
    }
}
