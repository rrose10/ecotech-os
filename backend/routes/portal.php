<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Portal\AttendanceController;
use App\Http\Controllers\Portal\SalaryController;
use App\Http\Controllers\Portal\NoticeController;
use App\Http\Controllers\Portal\LeaveController;
use App\Http\Controllers\Portal\PayslipController;

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/portal/dashboard', [DashboardController::class, 'index'])
        ->name('portal.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/portal/profile', [ProfileController::class, 'index'])
        ->name('portal.profile');

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    Route::get('/portal/attendance', [AttendanceController::class, 'index'])
        ->name('portal.attendance');

    // Check In
    Route::post('/portal/attendance/check-in', [AttendanceController::class, 'checkIn'])
        ->name('portal.attendance.checkin');

    // Start Dinner Break
    Route::post('/portal/attendance/break-start', [AttendanceController::class, 'startBreak'])
        ->name('portal.attendance.break.start');

    // End Dinner Break
    Route::post('/portal/attendance/break-end', [AttendanceController::class, 'endBreak'])
        ->name('portal.attendance.break.end');

    // Check Out
    Route::post('/portal/attendance/check-out', [AttendanceController::class, 'checkOut'])
        ->name('portal.attendance.checkout');

    /*
    |--------------------------------------------------------------------------
    | Payroll
    |--------------------------------------------------------------------------
    */

    Route::get('/portal/salary', [SalaryController::class, 'index'])
        ->name('portal.salary');

    /*
    |--------------------------------------------------------------------------
    | Notices
    |--------------------------------------------------------------------------
    */

    Route::get('/portal/notices', [NoticeController::class, 'index'])
        ->name('portal.notices');

    Route::get('/portal/notices/{notice}', [NoticeController::class, 'show'])
        ->name('portal.notices.show');

    /*
    |--------------------------------------------------------------------------
    | Leaves
    |--------------------------------------------------------------------------
    */

    Route::get('/portal/leaves', [LeaveController::class, 'index'])
        ->name('portal.leaves');

    Route::post('/portal/leaves', [LeaveController::class, 'store'])
        ->name('portal.leaves.store');

    Route::delete('/portal/leaves/{leave}', [LeaveController::class, 'cancel'])
        ->name('portal.leaves.cancel');

    /*
    |--------------------------------------------------------------------------
    | Payslip (printable, save as PDF from the browser)
    |--------------------------------------------------------------------------
    */

    Route::get('/portal/salary/{payroll}/payslip', [PayslipController::class, 'show'])
        ->name('portal.payslip');

});
