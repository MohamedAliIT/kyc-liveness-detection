<?php

namespace App\Http\Requests\Kyc;

use Illuminate\Foundation\Http\FormRequest;

class Step5UboDeclarationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // ======================
            // UBO FLAG
            // ======================
            'acting_on_behalf' => ['required', 'boolean'],

            // ======================
            // UBO DETAILS (Bilingual, required only if acting_on_behalf = true)
            // ======================
            'ubo_details_ar' => [
                'required_if:acting_on_behalf,1',
                'nullable',
                'string',
                'max:2000',
            ],
            'ubo_details_en' => [
                'required_if:acting_on_behalf,1',
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'acting_on_behalf.required' => 'حقل المستفيد النهائي مطلوب / UBO flag is required.',
            'acting_on_behalf.boolean'  => 'قيمة المستفيد النهائي غير صحيحة.',

            'ubo_details_ar.required_if' =>
                'تفاصيل المستفيد النهائي (عربي) مطلوبة عند اختيار نعم.',
            'ubo_details_en.required_if' =>
                'UBO details (English) are required when Acting on behalf is Yes.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'acting_on_behalf' => $this->boolean('acting_on_behalf'),

            'ubo_details_ar' => isset($this->ubo_details_ar)
                ? trim((string) $this->ubo_details_ar)
                : null,

            'ubo_details_en' => isset($this->ubo_details_en)
                ? trim((string) $this->ubo_details_en)
                : null,
        ]);
    }
}
