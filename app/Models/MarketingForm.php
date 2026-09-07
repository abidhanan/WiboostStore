<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingForm extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'fields',
        'benefit_enabled',
        'benefit_title',
        'benefit_description',
        'benefit_credentials',
        'benefit_url',
        'benefit_button_label',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'fields' => 'array',
        'benefit_credentials' => 'array',
        'benefit_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(MarketingFormSubmission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function publicUrl(): string
    {
        return route('public.forms.show', ['form' => $this->slug]);
    }

    public function fieldCount(): int
    {
        return count($this->fields ?? []);
    }
}
