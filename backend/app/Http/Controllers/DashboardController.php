<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Leave;
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

        // Night shift: attendance_date is the date the shift started on
        $shiftDate = Attendance::shiftDateFor()->toDateString();

        $presentToday = Attendance::whereDate('attendance_date', $shiftDate)
            ->whereIn('status', ['Present', 'Late'])
            ->count();

        $absentToday = Attendance::whereDate('attendance_date', $shiftDate)
            ->where('status', 'Absent')
            ->count();

        $pendingLeaves = Leave::where('status', 'Pending')
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

        $recentLeaves = Leave::with(['employee', 'leaveType'])
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

        // Database independent (works on SQLite and MySQL)
        $upcomingBirthdays = Employee::whereNotNull('date_of_birth')
            ->get()
            ->sortBy(function ($employee) use ($today) {

                $next = $employee->date_of_birth
                    ->copy()
                    ->year($today->year);

                if ($next->lt($today)) {
                    $next->addYear();
                }

                return $next->timestamp;
            })
            ->take(5)
            ->values();

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

    /**
     * Income (BDT) and expense per month for the last $months months, oldest first.
     * Grouped in PHP so it behaves the same on SQLite and MariaDB.
     *
     * @return array<int, array{label: string, income: float, expense: float}>
     */
    private function monthlyChart(int $months): array
    {
        $start = today()->startOfMonth()->subMonthsNoOverflow($months - 1);

        $income = Income::whereDate('income_date', '>=', $start)->get(['income_date', 'amount_bdt'])
            ->groupBy(fn ($i) => $i->income_date->format('Y-m'))
            ->map(fn ($g) => (float) $g->sum('amount_bdt'));

        $expense = Expense::whereDate('expense_date', '>=', $start)->get(['expense_date', 'amount'])
            ->groupBy(fn ($e) => $e->expense_date->format('Y-m'))
            ->map(fn ($g) => (float) $g->sum('amount'));

        $rows = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonthsNoOverflow($i);
            $key = $month->format('Y-m');

            $rows[] = [
                'label' => $month->format('M'),
                'income' => (float) ($income[$key] ?? 0),
                'expense' => (float) ($expense[$key] ?? 0),
            ];
        }

        return $rows;
    }
}
