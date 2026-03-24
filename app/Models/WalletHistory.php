<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletHistory extends Model
{
    protected $fillable = [
        'user_id', 
        'type', 
        'amount', 
        'description', 
        'invoice_number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}