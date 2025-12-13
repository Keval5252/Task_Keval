<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CustomEmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ShiftAssignmentController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Employee\AuthController as EmployeeAuthController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'loginIndex'])->name('login');
    Route::get('register', [AuthController::class, 'registerIndex']);

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

Route::prefix('employee')->name('employee.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [EmployeeAuthController::class, 'loginIndex'])->name('login');
        Route::post('login', [EmployeeAuthController::class, 'login'])->name('login.post');
    });

    Route::middleware(['auth', 'employee'])->group(function () {
        Route::get('/', [EmployeeAttendanceController::class, 'dashboard'])->name('dashboard');
        Route::get('attendance', [EmployeeAttendanceController::class, 'attendanceReport'])->name('attendance.index');

        Route::post('clock-in', [EmployeeAttendanceController::class, 'clockIn'])->name('clockin');
        Route::post('clock-out', [EmployeeAttendanceController::class, 'clockOut'])->name('clockout');

        Route::get('logout', [EmployeeAuthController::class, 'logout'])->name('logout');
    });
});


Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (CustomEmailVerificationRequest $request) {
    $request->fulfill();

    toastr()->success('Email successfully verified!');

    return redirect('/login');
})->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::get('/', [WelcomeController::class, 'index']);
    Route::get('logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('shifts', ShiftController::class)->except(['show']);
    Route::resource('employees', EmployeeController::class)->only(['index', 'create', 'store']);

    Route::get('assignments/create', [ShiftAssignmentController::class, 'create'])->name('assignments.create');
    Route::post('assignments', [ShiftAssignmentController::class, 'store'])->name('assignments.store');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
});
