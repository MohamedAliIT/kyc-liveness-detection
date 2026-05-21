<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorizedFace extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

