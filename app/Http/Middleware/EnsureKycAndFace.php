<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\KycProfile;

class EnsureKycAndFace
{
    public function handle(Request $request, Closure $next, string $mode = 'active')
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $kyc = KycProfile::where('user_id', $user->id)->first();
        if (!$kyc) {
            return redirect()->route('kyc.page');
        }

        /*
         * Block incomplete KYC states
         */
        if (in_array($kyc->status, ['draft', 'submitted', 'rejected'], true)) {
            return redirect()->route('kyc.page');
        }

        /*
         * Flow control (NO challenge logic)
         *
         * verified        → can enroll face
         * face_enrolled   → can verify face
         * active          → full access
         */
        return match ($mode) {

            // Allow face enrollment after KYC approval
            'enroll' =>
            $kyc->status === 'verified'
                ? $next($request)
                : redirect()->route('dashboard'),

            // Allow face verification after enrollment
            'verify' =>
            $kyc->status === 'face_enrolled'
                ? $next($request)
                : redirect()->route('dashboard'),

            // Fully verified user
            'active' =>
            $kyc->status === 'active'
                ? $next($request)
                : abort(403),

            default => abort(403),
        };
    }
}
