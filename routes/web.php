<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;

Route::get('/', [DashboardController::class, 'index']);

Route::resource('departments', DepartmentController::class);

Route::resource('employees', EmployeeController::class);