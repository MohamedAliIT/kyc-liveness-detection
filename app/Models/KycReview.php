<?php

// app/Models/KycReview.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycReview extends Model
{
    protected $fillable = [
        'kyc_profile_id',
        'reviewed_by',
        'action',
        'notes',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(KycProfile::class, 'kyc_profile_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
