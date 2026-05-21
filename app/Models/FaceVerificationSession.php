<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceVerificationSession extends Model
{
    protected $fillable = [
        'session_id',
        'status',
        'confidence',
        'match_score',
        'matched_face_id',
        'fail_reason',
    ];

    protected $casts = [
        'confidence' => 'float',
        'match_score' => 'float',
    ];
}
