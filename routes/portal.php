<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Portal\AttendanceController;
use App\Http\Controllers\Portal\SalaryController;
use App\Http\Controllers\Portal\NoticeController;

Route::middleware(['auth'])->group(function () {

    Route::get('/portal/dashboard', [DashboardController::class, 'index'])
        ->name('portal.dashboard');

    Route::get('/portal/profile', [ProfileController::class, 'index'])
        ->name('portal.profile');

    Route::get('/portal/attendance', [AttendanceController::class, 'index'])
        ->name('portal.attendance');

    Route::post('/portal/attendance/check-in', [AttendanceController::class, 'checkIn'])
        ->name('portal.attendance.checkin');

    Route::post('/portal/attendance/check-out', [AttendanceController::class, 'checkOut'])
        ->name('portal.attendance.checkout');

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

});