<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeCategoryController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdvanceSalaryController;
use App\Http\Controllers\EmployeeLoanController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    return redirect()->route('login');

});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Main Dashboard

    Route::get('/dashboard',
        [DashboardController::class, 'index']
    )->middleware('role:super_admin,admin')->name('dashboard');

    // Employee Dashboard

    Route::get('/employee/dashboard',
        [EmployeeDashboardController::class, 'index']
    )->name('employee.dashboard');

    // Profile

    Route::get('/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch('/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete('/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');

});

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:super_admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Activity Logs (Super Admin only)
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'activity-logs',
        ActivityLogController::class
    )->only(['index']);

});

/*
|--------------------------------------------------------------------------
| Management Routes (Super Admin + Admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Departments
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'departments',
        DepartmentController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'employees',
        EmployeeController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'attendances',
        AttendanceController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Leave Types
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'leave-types',
        LeaveTypeController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Leaves
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'leaves',
        LeaveController::class
    );

    Route::post(
        '/leaves/{leave}/approve',
        [LeaveController::class, 'approve']
    )->name('leaves.approve');

    Route::post(
        '/leaves/{leave}/reject',
        [LeaveController::class, 'reject']
    )->name('leaves.reject');

    /*
    |--------------------------------------------------------------------------
    | Leave Balance
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/leave-balances',
        [LeaveBalanceController::class, 'index']
    )->name('leave-balances.index');

    Route::get(
        '/leave-balances/{employee}',
        [LeaveBalanceController::class, 'show']
    )->name('leave-balances.show');

    /*
    |--------------------------------------------------------------------------
    | Expenses
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'expense-categories',
        ExpenseCategoryController::class
    );

    Route::resource(
        'expenses',
        ExpenseController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Income
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'income-categories',
        IncomeCategoryController::class
    );

    Route::resource(
        'incomes',
        IncomeController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Payroll
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/payrolls/employee/{employee}',
        [PayrollController::class, 'employeeSalary']
    )->name('payrolls.employee.salary');

    // AJAX Payroll Calculator
    Route::get(
        '/payrolls/calculate',
        [PayrollController::class, 'calculate']
    )->name('payrolls.calculate');

    Route::resource(
        'payrolls',
        PayrollController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Advance Salary
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'advance-salaries',
        AdvanceSalaryController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Employee Loans
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'employee-loans',
        EmployeeLoanController::class
    );

        /*
    |--------------------------------------------------------------------------
    | Bank Accounts
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'bank-accounts',
        BankAccountController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Notices
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'notices',
        NoticeController::class
    );

});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Employee Portal Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/portal.php';