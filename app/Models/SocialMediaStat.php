<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialMediaStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'month',
        'year',
        'total_posts',
        'total_followers',
        'reach',
        'profile_visits',
        'bio_link_clicks',
        'top_post_url',
        'low_post_url',
        'updated_by',
    ];

    protected $casts = [
        'total_posts' => 'integer',
        'month' => 'integer',
        'year' => 'integer',
        'total_followers' => 'integer',
        'reach' => 'integer',
        'profile_visits' => 'integer',
        'bio_link_clicks' => 'integer',
    ];

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
