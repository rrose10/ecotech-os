<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeCategoryController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\BankAccountController;

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

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Super Admin & Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {

    // Departments
    Route::resource('departments', DepartmentController::class);

    // Employees
    Route::resource('employees', EmployeeController::class);

    // Attendance
    Route::resource('attendances', AttendanceController::class);

    // Leave
    Route::resource('leave-types', LeaveTypeController::class);
    Route::resource('leave-requests', LeaveRequestController::class);

    // Expenses
    Route::resource('expense-categories', ExpenseCategoryController::class);
    Route::resource('expenses', ExpenseController::class);

    // Income
    Route::resource('income-categories', IncomeCategoryController::class);
    Route::resource('incomes', IncomeController::class);

    // Payroll AJAX
    Route::get('/payrolls/employee/{employee}', [PayrollController::class, 'employeeSalary'])
        ->name('payrolls.employee.salary');

    // Payroll
    Route::resource('payrolls', PayrollController::class);

    Route::resource('bank-accounts', BankAccountController::class);
});

require __DIR__.'/auth.php';