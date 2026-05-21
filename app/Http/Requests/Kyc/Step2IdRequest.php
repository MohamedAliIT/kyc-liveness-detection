<?php

namespace App\Http\Requests\Kyc;

use Illuminate\Foundation\Http\FormRequest;

class Step2IdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // ======================
            // ID CORE
            // ======================
            'id_number' => ['required', 'string', 'max:120'],

            // ======================
            // ISSUING AUTHORITY (Bilingual)
            // ======================
            'issuing_authority_ar' => ['required', 'string', 'max:180'],
            'issuing_authority_en' => ['required', 'string', 'max:180'],

            // ======================
            // DATES
            // ======================
            'id_issue_date' => ['required', 'date', 'before_or_equal:today'],
            'id_expiry_date' => ['required', 'date', 'after:id_issue_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_number.required' => 'رقم الهوية مطلوب / ID number is required.',

            'issuing_authority_ar.required' => 'جهة الإصدار (عربي) مطلوبة.',
            'issuing_authority_en.required' => 'Issuing authority (English) is required.',

            'id_issue_date.required' => 'تاريخ الإصدار مطلوب / Issue date is required.',
            'id_issue_date.before_or_equal' => 'تاريخ الإصدار لا يمكن أن يكون في المستقبل.',

            'id_expiry_date.required' => 'تاريخ الانتهاء مطلوب / Expiry date is required.',
            'id_expiry_date.after' => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ الإصدار.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id_number' => trim((string) $this->id_number),
            'issuing_authority_ar' => trim((string) $this->issuing_authority_ar),
            'issuing_authority_en' => trim((string) $this->issuing_authority_en),
        ]);
    }
}
