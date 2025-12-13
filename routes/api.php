<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\AttendanceController as ApiAttendanceController;
use App\Http\Controllers\Api\BiometricPunchController as ApiBiometricPunchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/login', [ApiAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);

    Route::get('/me', [ApiAttendanceController::class, 'me']);
    Route::post('/attendance/clock-in', [ApiAttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [ApiAttendanceController::class, 'clockOut']);
});

// Biometric devices (fingerprint scanners) call this endpoint after verification.
Route::middleware(['biometric.device'])->group(function () {
    Route::post('/biometric/punch', [ApiBiometricPunchController::class, 'punch']);
});
