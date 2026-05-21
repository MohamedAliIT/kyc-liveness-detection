<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaceVerificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});

/*
|--------------------------------------------------------------------------
| Face Verification API
|--------------------------------------------------------------------------
| ملاحظة مهمة:
| - كل Routes محمية بـ auth:sanctum
| - React يجب أن يرسل credentials + CSRF
*/
Route::middleware('auth:sanctum')
    ->prefix('face')
    ->name('api.face.')
    ->group(function () {

        Route::post('/start', [FaceVerificationController::class, 'start'])
            ->name('start');

        Route::get('/challenge', [FaceVerificationController::class, 'challenge'])
            ->name('challenge');

        Route::post('/enroll', [FaceVerificationController::class, 'enroll'])
            ->name('enroll');

        Route::post('/verify', [FaceVerificationController::class, 'verify'])
            ->name('verify');
    });
