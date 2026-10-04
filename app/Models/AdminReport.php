<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AdminReport extends Model
{
    protected $fillable = [
        'user_id',
        'report_hash',
        'category_key',
        'category_label',
        'issue',
        'record_id',
        'related_invoice',
        'related_title',
        'related_snapshot',
        'proof_path',
        'proof_original_name',
        'whatsapp_message',
        'status',
    ];

    protected $casts = [
        'related_snapshot' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'closed' ? 'Sudah Ditangani' : 'Belum Ditangani';
    }

    public function getProofUrlAttribute(): ?string
    {
        if (! $this->proof_path) {
            return null;
        }

        return Storage::disk('public')->url($this->proof_path);
    }
}
