<?php

namespace App\Http\Controllers;

use App\Models\KycProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\Kyc\Step1PersonalRequest;
use App\Http\Requests\Kyc\Step2IdRequest;
use App\Http\Requests\Kyc\Step3IncomeRequest;
use App\Http\Requests\Kyc\Step4PepRequest;
use App\Http\Requests\Kyc\Step5UboDeclarationRequest;

use Barryvdh\DomPDF\Facade\Pdf;

class KycWizardController extends Controller
{
    public function page()
    {
        $user = Auth::user();

        $kyc = KycProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'current_step' => 1,
                'status'       => KycProfile::STATUS_DRAFT,
            ]
        );

        return inertia('KYC/Wizard', [
            'kyc' => $kyc,
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'is_admin' => (bool) ($user->is_admin ?? false),
            ],
        ]);
    }

    public function saveStep(int $step, Request $request)
    {
        $user = Auth::user();
        $kyc  = KycProfile::where('user_id', $user->id)->firstOrFail();

        if (!$this->canEdit($kyc)) {
            abort(403, 'Editing not allowed in current state.');
        }

        if ($step < 1 || $step > 5) {
            abort(400, 'Invalid step.');
        }

        $validated = match ($step) {
            1 => app(Step1PersonalRequest::class)->validated(),
            2 => app(Step2IdRequest::class)->validated(),
            3 => app(Step3IncomeRequest::class)->validated(),
            4 => app(Step4PepRequest::class)->validated(),
            5 => app(Step5UboDeclarationRequest::class)->validated(),
        };

        $validated = $this->normalizeBooleans($validated);

        // IMPORTANT: Declaration is STEP 6 only.
        unset($validated['declaration_accepted']);

        $kyc->fill($validated);
        $kyc->current_step = max((int)$kyc->current_step, (int)$step);
        $kyc->save();

        return back()->with('success', 'Step saved successfully.');
    }

    public function submit(Request $request)
    {
        $user = Auth::user();
        $kyc  = KycProfile::where('user_id', $user->id)->firstOrFail();

        if (!$this->canEdit($kyc)) {
            abort(403, 'Form already submitted or locked.');
        }

        $request->validate([
            'declaration_accepted' => ['accepted'],
        ]);

        if ((int)$kyc->current_step < 5) {
            return back()->withErrors([
                'form' => 'Please complete all steps before submission.',
            ]);
        }

        $this->validateFinalFromDatabase($kyc);

        $kyc->update([
            'declaration_accepted'     => true,
            'declaration_accepted_at' => now(),
            'status'                  => KycProfile::STATUS_FACE_REQUIRED,
            'submitted_at'           => now(),
            'current_step'           => 6,
        ]);

        return redirect()
            ->route('kyc.page')
            ->with('success', 'KYC submitted. Please enroll your face to proceed.');
    }

    public function markFaceEnrolled(Request $request)
    {
        $user = Auth::user();
        $kyc  = KycProfile::where('user_id', $user->id)->firstOrFail();

        if ($kyc->status !== KycProfile::STATUS_FACE_REQUIRED) {
            abort(400, 'Invalid state transition.');
        }

        $kyc->update([
            'status' => KycProfile::STATUS_FACE_ENROLLED,
        ]);

        return redirect()
            ->route('kyc.page')
            ->with('success', 'Face enrolled. Please verify your face.');
    }

    public function markFaceVerified(Request $request)
    {
        $user = Auth::user();
        $kyc  = KycProfile::where('user_id', $user->id)->firstOrFail();

        if ($kyc->status !== KycProfile::STATUS_FACE_ENROLLED) {
            abort(400, 'Invalid state transition.');
        }

        $kyc->update([
            'status'      => KycProfile::STATUS_ACTIVE,
            'verified_at' => now(),
            'verified_by' => $user->id,
        ]);

        return redirect()
            ->route('kyc.page')
            ->with('success', 'Account activated successfully.');
    }

    private function canEdit(KycProfile $kyc): bool
    {
        return in_array($kyc->status, [
            KycProfile::STATUS_DRAFT,
            KycProfile::STATUS_REJECTED,
        ], true);
    }

    private function normalizeBooleans(array $data): array
    {
        if (array_key_exists('is_pep', $data) && !$data['is_pep']) {
            $data['pep_details_ar'] = null;
            $data['pep_details_en'] = null;
        }

        if (array_key_exists('acting_on_behalf', $data) && !$data['acting_on_behalf']) {
            $data['ubo_details_ar'] = null;
            $data['ubo_details_en'] = null;
        }

        return $data;
    }

    private function validateFinalFromDatabase(KycProfile $kyc): void
    {
        Validator::make($kyc->toArray(), [
            // STEP 1
            'full_name_ar' => 'required|string|max:255',
            'full_name_en' => 'required|string|max:255',
            'date_of_birth'=> 'required|date|before:today',

            'nationality_ar' => 'required|string|max:120',
            'nationality_en' => 'required|string|max:120',

            'occupation_ar' => 'required|string|max:180',
            'occupation_en' => 'required|string|max:180',

            'employer_or_business_ar' => 'required|string|max:255',
            'employer_or_business_en' => 'required|string|max:255',

            'contact_number' => 'required|string|max:50',
            'email_address'  => 'required|email',

            'residential_address_ar' => 'required|string|max:2000',
            'residential_address_en' => 'required|string|max:2000',

            // STEP 2
            'id_number' => 'required|string|max:120',
            'issuing_authority_ar' => 'required|string|max:180',
            'issuing_authority_en' => 'required|string|max:180',
            'id_issue_date'  => 'required|date',
            'id_expiry_date'=> 'required|date|after:id_issue_date',

            // STEP 3
            'source_of_income_ar' => 'required|string|max:255',
            'source_of_income_en' => 'required|string|max:255',
            'income_range'        => 'required|string|max:60',

            'purpose_of_relationship_ar' => 'required|string|max:255',
            'purpose_of_relationship_en' => 'required|string|max:255',

            // STEP 4
            'is_pep' => 'required|boolean',
            'pep_details_ar' => 'required_if:is_pep,1|string|max:2000',
            'pep_details_en' => 'required_if:is_pep,1|string|max:2000',

            // STEP 5
            'acting_on_behalf' => 'required|boolean',
            'ubo_details_ar' => 'required_if:acting_on_behalf,1|string|max:2000',
            'ubo_details_en' => 'required_if:acting_on_behalf,1|string|max:2000',
        ])->validate();
    }

    public function print()
    {
        $user = Auth::user();
        $kyc  = KycProfile::where('user_id', $user->id)->firstOrFail();

        $pdf = Pdf::loadView('pdf.kyc-form', [
            'kyc'  => $kyc,
            'user' => $user,
            'date' => now()->format('Y-m-d'),
        ])->setPaper('A4');

        return $pdf->download('KYC-Form-' . $user->id . '.pdf');
    }
}
