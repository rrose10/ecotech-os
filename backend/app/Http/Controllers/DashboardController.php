<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        /*
        |--------------------------------------------------------------------------
        | HR Summary
        |--------------------------------------------------------------------------
        */

        $employees = Employee::count();

        $departments = Department::count();

        $presentToday = Attendance::whereDate('date', $today)
            ->where('status', 'Present')
            ->count();

        $absentToday = Attendance::whereDate('date', $today)
            ->where('status', 'Absent')
            ->count();

        $pendingLeaves = LeaveRequest::where('status', 'Pending')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Expense Summary
        |--------------------------------------------------------------------------
        */

        $todayExpense = Expense::whereDate('expense_date', $today)
            ->sum('amount');

        $thisMonthExpense = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        $thisYearExpense = Expense::whereYear('expense_date', now()->year)
            ->sum('amount');

        $totalExpenseCategories = ExpenseCategory::count();

        /*
        |--------------------------------------------------------------------------
        | Income Summary
        |--------------------------------------------------------------------------
        */

        $todayIncomeBDT = Income::whereDate('income_date', $today)
            ->sum('amount_bdt');

        $todayIncomeUSD = Income::whereDate('income_date', $today)
            ->sum('amount_usd');

        $thisMonthIncomeBDT = Income::whereMonth('income_date', now()->month)
            ->whereYear('income_date', now()->year)
            ->sum('amount_bdt');

        $thisMonthIncomeUSD = Income::whereMonth('income_date', now()->month)
            ->whereYear('income_date', now()->year)
            ->sum('amount_usd');

        $thisYearIncomeBDT = Income::whereYear('income_date', now()->year)
            ->sum('amount_bdt');

        $thisYearIncomeUSD = Income::whereYear('income_date', now()->year)
            ->sum('amount_usd');

        $totalIncomeCategories = IncomeCategory::count();

        /*
        |--------------------------------------------------------------------------
        | Recent Employees
        |--------------------------------------------------------------------------
        */

        $recentEmployees = Employee::with('department')
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Pending Leave Requests
        |--------------------------------------------------------------------------
        */

        $recentLeaves = LeaveRequest::with(['employee', 'leaveType'])
            ->where('status', 'Pending')
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Latest Expenses
        |--------------------------------------------------------------------------
        */

        $latestExpenses = Expense::with([
                'expenseCategory',
                'creator'
            ])
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Latest Incomes
        |--------------------------------------------------------------------------
        */

        $latestIncomes = Income::with([
                'incomeCategory',
                'creator'
            ])
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Upcoming Birthdays
        |--------------------------------------------------------------------------
        */

        $upcomingBirthdays = Employee::whereNotNull('date_of_birth')
            ->orderByRaw("strftime('%m-%d', date_of_birth)")
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'employees',
            'departments',
            'presentToday',
            'absentToday',
            'pendingLeaves',

            'todayExpense',
            'thisMonthExpense',
            'thisYearExpense',
            'totalExpenseCategories',

            'todayIncomeBDT',
            'todayIncomeUSD',
            'thisMonthIncomeBDT',
            'thisMonthIncomeUSD',
            'thisYearIncomeBDT',
            'thisYearIncomeUSD',
            'totalIncomeCategories',

            'recentEmployees',
            'recentLeaves',
            'latestExpenses',
            'latestIncomes',
            'upcomingBirthdays'
        ));
    }
}