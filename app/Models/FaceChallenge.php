<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class FaceChallenge extends Model
{
    protected $fillable = [
        'challenge_id',
        'actions',
        'time_limit',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'actions' => 'array',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        if (!$this->expires_at) return false;
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }
}
