<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycProfile extends Model
{

    public const STATUS_DRAFT          = 'draft';
    public const STATUS_SUBMITTED      = 'submitted';
    public const STATUS_VERIFIED       = 'verified';
    public const STATUS_FACE_REQUIRED  = 'face_required';
    public const STATUS_FACE_ENROLLED  = 'face_enrolled';
    public const STATUS_ACTIVE         = 'active';
    public const STATUS_REJECTED       = 'rejected';


    protected $fillable = [
        'user_id',
        'current_step',
        'status',
        'submitted_at',

        'full_name_ar',
        'full_name_en',
        'date_of_birth',

        'nationality_ar',
        'nationality_en',

        'occupation_ar',
        'occupation_en',

        'employer_or_business_ar',
        'employer_or_business_en',

        'contact_number',
        'email_address',

        'residential_address_ar',
        'residential_address_en',

        'id_number',
        'issuing_authority_ar',
        'issuing_authority_en',
        'id_issue_date',
        'id_expiry_date',

        'source_of_income_ar',
        'source_of_income_en',
        'income_range',

        'purpose_of_relationship_ar',
        'purpose_of_relationship_en',

        'is_pep',
        'pep_details_ar',
        'pep_details_en',

        'acting_on_behalf',
        'ubo_details_ar',
        'ubo_details_en',

        'declaration_accepted',
        'declaration_accepted_at',

        'verified_by',
        'verified_at',
        'office_remarks',
        'rejection_reason',
    ];

    protected $casts = [
        'is_pep' => 'boolean',
        'acting_on_behalf' => 'boolean',
        'declaration_accepted' => 'boolean',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'declaration_accepted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* 🔥 Helper ذكي يعيد القيمة حسب اللغة */
    public function getLang(string $field, string $lang): ?string
    {
        return $this->{$field . '_' . $lang};
    }

    public function requiresFace(): bool
    {
        return $this->status === self::STATUS_FACE_REQUIRED;
    }

    public function hasEnrolledFace(): bool
    {
        return $this->status === self::STATUS_FACE_ENROLLED;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function verifier()
    {
        return $this->belongsTo(\App\Models\User::class, 'verified_by');
    }


}
