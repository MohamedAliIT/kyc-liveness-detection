<?php

namespace App\Http\Requests\Kyc;

use Illuminate\Foundation\Http\FormRequest;

class Step3IncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // ======================
            // SOURCE OF INCOME (Bilingual)
            // ======================
            'source_of_income_ar' => ['required', 'string', 'max:255'],
            'source_of_income_en' => ['required', 'string', 'max:255'],

            // ======================
            // INCOME RANGE (Single Field)
            // ======================
            'income_range' => ['required', 'string', 'max:60'],

            // ======================
            // PURPOSE OF RELATIONSHIP (Bilingual)
            // ======================
            'purpose_of_relationship_ar' => ['required', 'string', 'max:255'],
            'purpose_of_relationship_en' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'source_of_income_ar.required' => 'مصدر الدخل (عربي) مطلوب.',
            'source_of_income_en.required' => 'Source of income (English) is required.',

            'income_range.required' => 'نطاق الدخل مطلوب / Income range is required.',

            'purpose_of_relationship_ar.required' => 'الغرض من العلاقة (عربي) مطلوب.',
            'purpose_of_relationship_en.required' => 'Purpose of relationship (English) is required.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'source_of_income_ar' => trim((string) $this->source_of_income_ar),
            'source_of_income_en' => trim((string) $this->source_of_income_en),

            'purpose_of_relationship_ar' => trim((string) $this->purpose_of_relationship_ar),
            'purpose_of_relationship_en' => trim((string) $this->purpose_of_relationship_en),

            'income_range' => trim((string) $this->income_range),
        ]);
    }
}
