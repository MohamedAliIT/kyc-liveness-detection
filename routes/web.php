<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KycWizardController;
use App\Http\Controllers\KycAdminController;
use App\Http\Controllers\KycPrintController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'        => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Face Verification (User UI)
    |--------------------------------------------------------------------------
    */
    Route::prefix('face')->name('face.')->group(function () {

        // Face enrollment page (UI)
        Route::get('/enroll', fn () =>
        Inertia::render('Face/Enroll')
        )->name('enroll'); // face.enroll

        // Face verification page (UI)
        Route::get('/verify', fn () =>
        Inertia::render('Face/Verify')
        )->name('verify'); // face.verify

        // Called after successful AI face verification
        Route::post('/verified', [
            KycWizardController::class,
            'markFaceVerified'
        ])->name('verified'); // face.verified
    });

    /*
    |--------------------------------------------------------------------------
    | KYC – Client Flow
    |--------------------------------------------------------------------------
    */
    Route::prefix('kyc')->name('kyc.')->group(function () {

        // Wizard main page
        Route::get('/', [KycWizardController::class, 'page'])
            ->name('page');

        // Optional status endpoint (AJAX / polling)
        Route::get('/status', [KycWizardController::class, 'status'])
            ->name('status');

        // Save step (1 → 5)
        Route::post('/step/{step}', [KycWizardController::class, 'saveStep'])
            ->whereNumber('step')
            ->name('step.save');

        // Final submit (Step 6)
        Route::post('/submit', [KycWizardController::class, 'submit'])
            ->name('submit');
    });

    /*
    |--------------------------------------------------------------------------
    | KYC – Admin Review
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')
        ->middleware(['can:kyc.review'])
        ->name('admin.')
        ->group(function () {

            Route::prefix('kyc')->name('kyc.')->group(function () {

                // List all KYC
                Route::get('/', [KycAdminController::class, 'index'])
                    ->name('index');

                // View single KYC
                Route::get('/{kyc}', [KycAdminController::class, 'show'])
                    ->name('show');

                // Approve KYC → face_required
                Route::post('/{kyc}/verify', [KycAdminController::class, 'verify'])
                    ->name('verify');

                // Reject KYC
                Route::post('/{kyc}/reject', [KycAdminController::class, 'reject'])
                    ->name('reject');

                // Print PDF
                Route::get('/{kyc}/print', [KycPrintController::class, 'print'])
                    ->name('print');
            });
        });
});

require __DIR__ . '/auth.php';
