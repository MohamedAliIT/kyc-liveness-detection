<?php

namespace App\Http\Requests\Kyc;

use Illuminate\Foundation\Http\FormRequest;

class Step4PepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // ======================
            // PEP FLAG
            // ======================
            'is_pep' => ['required', 'boolean'],

            // ======================
            // PEP DETAILS (Bilingual, required only if PEP = true)
            // ======================
            'pep_details_ar' => [
                'required_if:is_pep,1',
                'nullable',
                'string',
                'max:2000',
            ],
            'pep_details_en' => [
                'required_if:is_pep,1',
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'is_pep.required' => 'حقل PEP مطلوب / PEP flag is required.',
            'is_pep.boolean'  => 'قيمة PEP غير صحيحة.',

            'pep_details_ar.required_if' => 'تفاصيل PEP (عربي) مطلوبة عند اختيار نعم.',
            'pep_details_en.required_if' => 'PEP details (English) are required when PEP is Yes.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_pep' => $this->boolean('is_pep'),

            'pep_details_ar' => isset($this->pep_details_ar)
                ? trim((string) $this->pep_details_ar)
                : null,

            'pep_details_en' => isset($this->pep_details_en)
                ? trim((string) $this->pep_details_en)
                : null,
        ]);
    }
}
