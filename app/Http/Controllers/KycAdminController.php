<?php

namespace App\Http\Controllers;

use App\Models\KycProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class KycAdminController extends Controller
{
    /* =========================================================
     * LIST (Admin)
     * ========================================================= */
    public function index(Request $request)
    {
        $query = KycProfile::query()
            ->with('user')
            ->latest();

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        $kycs = $query
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('KYC/Admin/Index', [
            'kycs' => $kycs,
            'filters' => [
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    /* =========================================================
     * SHOW (Admin)
     * ========================================================= */
    public function show(KycProfile $kyc)
    {
        $kyc->load('user');

        return Inertia::render('KYC/Admin/Show', [
            'kyc' => $kyc,
        ]);
    }

    /* =========================================================
     * APPROVE
     * submitted → face_required
     * ========================================================= */
    public function verify(Request $request, KycProfile $kyc)
    {
        if ($kyc->status !== KycProfile::STATUS_SUBMITTED) {
            return back()->withErrors([
                'kyc' => 'Only submitted KYC can be approved.',
            ]);
        }

        $validated = $request->validate([
            'office_remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $kyc->forceFill([
            'status'            => KycProfile::STATUS_FACE_REQUIRED,
            'verified_by'      => auth()->id(),
            'verified_at'      => now(),
            'office_remarks'  => $validated['office_remarks'] ?? null,
            'rejection_reason'=> null,
        ])->save();

        return back()->with(
            'success',
            'KYC approved. User must complete face enrollment.'
        );
    }

    /* =========================================================
     * REJECT
     * submitted → rejected
     * ========================================================= */
    public function reject(Request $request, KycProfile $kyc)
    {
        if ($kyc->status !== KycProfile::STATUS_SUBMITTED) {
            return back()->withErrors([
                'kyc' => 'Only submitted KYC can be rejected.',
            ]);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $kyc->forceFill([
            'status'            => KycProfile::STATUS_REJECTED,
            'verified_by'      => auth()->id(),
            'verified_at'      => now(),
            'rejection_reason'=> $validated['rejection_reason'],
            'office_remarks'  => null,
        ])->save();

        return back()->with(
            'success',
            'KYC rejected successfully.'
        );
    }

    /* =========================================================
     * PRINT PDF
     * ========================================================= */
    public function print(KycProfile $kyc)
    {
        $kyc->load('user');

        $pdf = Pdf::loadView('pdf.kyc-form', [
            'kyc'  => $kyc,
            'user' => $kyc->user,
            'date' => now()->format('Y-m-d'),
        ])->setPaper('A4');

        return $pdf->download(
            'KYC-' . $kyc->id . '-' . optional($kyc->user)->email . '.pdf'
        );
    }
}
