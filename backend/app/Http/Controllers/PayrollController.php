<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Display a listing of payrolls.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $payrolls = Payroll::with('employee')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return view('payrolls.index', compact('payrolls', 'search'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
    $employees = Employee::where('status', true)
        ->orderBy('name')
        ->get();

    $bankAccounts = BankAccount::where('is_active', true)
        ->orderBy('account_name')
        ->get();

    return view('payrolls.create', compact(
        'employees',
        'bankAccounts'
    ));
    }

    /**
     * Store payroll.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'month'         => 'required|integer|between:1,12',
            'year'          => 'required|integer|min:2024|max:2100',
            'bonus'         => 'nullable|numeric|min:0',
            'payment_date'  => 'nullable|date',
            'status'        => 'required|in:Pending,Paid',
            'remarks'       => 'nullable|string',
        ]);

        // Prevent duplicate payroll
        $exists = Payroll::where('employee_id', $request->employee_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'employee_id' => 'Payroll already exists for this employee in the selected month and year.'
                ]);
        }

        $employee = Employee::findOrFail($request->employee_id);

        $basicSalary = $employee->basic_salary;
        $bonus = $request->bonus ?? 0;

        Payroll::create([
        'employee_id'      => $employee->id,
        'bank_account_id'  => $request->bank_account_id,
        'month'            => $request->month,
        'year'             => $request->year,
        'basic_salary'     => $basicSalary,
        'bonus'            => $bonus,
        'total_salary'     => $basicSalary + $bonus,
        'payment_date'     => $request->payment_date,
        'status'           => $request->status,
        'remarks'          => $request->remarks,
        'created_by'       => Auth::id(),
    ]);

        return redirect()
            ->route('payrolls.index')
            ->with('success', 'Payroll Added Successfully.');
    }

    /**
     * Display payroll.
     */
    public function show(Payroll $payroll)
    {
        return view('payrolls.show', compact('payroll'));
    }

    /**
     * Show edit form.
     */
    public function edit(Payroll $payroll)
    {
    $employees = Employee::where('status', true)
        ->orderBy('name')
        ->get();

    $bankAccounts = BankAccount::where('is_active', true)
        ->orderBy('account_name')
        ->get();

    return view('payrolls.edit', compact(
        'payroll',
        'employees',
        'bankAccounts'
    ));
    }

    /**
     * Update payroll.
     */
    public function update(Request $request, Payroll $payroll)
    {
        $request->validate([
        'employee_id'      => 'required|exists:employees,id',
        'bank_account_id'  => 'required|exists:bank_accounts,id',
        'month'            => 'required|integer|between:1,12',
        'year'             => 'required|integer|min:2024|max:2100',
        'bonus'            => 'nullable|numeric|min:0',
        'payment_date'     => 'nullable|date',
        'status'           => 'required|in:Pending,Paid',
        'remarks'          => 'nullable|string',
    ]);

        $exists = Payroll::where('employee_id', $request->employee_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->where('id', '!=', $payroll->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'employee_id' => 'Payroll already exists for this employee in the selected month and year.'
                ]);
        }

        $employee = Employee::findOrFail($request->employee_id);

        $basicSalary = $employee->basic_salary;
        $bonus = $request->bonus ?? 0;

        $payroll->update([
        'employee_id'      => $employee->id,
        'bank_account_id'  => $request->bank_account_id,
        'month'            => $request->month,
        'year'             => $request->year,
        'basic_salary'     => $basicSalary,
        'bonus'            => $bonus,
        'total_salary'     => $basicSalary + $bonus,
        'payment_date'     => $request->payment_date,
        'status'           => $request->status,
        'remarks'          => $request->remarks,
    ]);

        return redirect()
            ->route('payrolls.index')
            ->with('success', 'Payroll Updated Successfully.');
    }

    /**
     * Delete payroll.
     */
    public function destroy(Payroll $payroll)
    {
        $payroll->delete();

        return redirect()
            ->route('payrolls.index')
            ->with('success', 'Payroll Deleted Successfully.');
    }

    /**
     * Return employee basic salary (AJAX)
     */
    public function employeeSalary(Employee $employee)
    {
        return response()->json([
            'basic_salary' => $employee->basic_salary,
        ]);
    }
}