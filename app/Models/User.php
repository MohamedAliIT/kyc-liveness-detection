<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Mass assignable
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * Hidden attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    /**
     * 🔗 KYC relation (ONE user → ONE KYC)
     */
    public function kycProfile(): HasOne
    {
        return $this->hasOne(KycProfile::class);
    }

    /**
     * 🛡️ Convenience helpers
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function hasActiveKyc(): bool
    {
        return $this->kycProfile?->status === 'active';
    }
}
