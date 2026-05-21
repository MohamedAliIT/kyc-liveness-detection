<?php

namespace App\Http\Requests\Kyc;

use Illuminate\Foundation\Http\FormRequest;

class Step1PersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'full_name_ar' => ['required', 'string', 'max:255'],
            'full_name_en' => ['required', 'string', 'max:255'],

            'date_of_birth' => ['required', 'date', 'before:today'],

            'nationality_ar' => ['required', 'string', 'max:120'],
            'nationality_en' => ['required', 'string', 'max:120'],

            'occupation_ar' => ['required', 'string', 'max:180'],
            'occupation_en' => ['required', 'string', 'max:180'],

            'employer_or_business_ar' => ['required', 'string', 'max:255'],
            'employer_or_business_en' => ['required', 'string', 'max:255'],

            'contact_number' => ['required', 'string', 'max:50'],
            'email_address' => ['required', 'email'],

            'residential_address_ar' => ['required', 'string', 'max:2000'],
            'residential_address_en' => ['required', 'string', 'max:2000'],
        ];
    }

}
