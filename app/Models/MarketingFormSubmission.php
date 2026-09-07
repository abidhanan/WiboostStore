<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingFormSubmission extends Model
{
    protected $fillable = [
        'marketing_form_id',
        'public_token',
        'answers',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(MarketingForm::class, 'marketing_form_id');
    }
}
