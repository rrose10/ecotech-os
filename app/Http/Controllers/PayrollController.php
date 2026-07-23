<?php

namespace App\Http\Controllers;

use App\Models\AdvanceSalary;
use App\Models\BankAccount;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payroll;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeLoan;

class PayrollController extends Controller
{
    /**
     * Display a listing of payrolls.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $payrolls = Payroll::with([
                'employee',
                'bankAccount',
                'expense',
            ])
            ->when($search, function ($query) use ($search) {

                $query->whereHas('employee', function ($q) use ($search) {

                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");

                });

            })
            ->latest()
            ->paginate(10);

        return view('payrolls.index', compact(
            'payrolls',
            'search'
        ));
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
     * Calculate payroll amounts (AJAX)
     */
    public function calculate(Request $request)
    {
        $employee = Employee::find($request->employee_id);

        if (!$employee) {
            return response()->json([
                'basic_salary'   => 0,
                'gross_salary'   => 0,
                'advance_amount' => 0,
                'loan_amount'    => 0,
                'net_salary'     => 0,
            ]);
        }

        $basicSalary = (float) $employee->basic_salary;
        $bonus = (float) ($request->bonus ?? 0);

        $grossSalary = $basicSalary + $bonus;

        $advanceAmount = AdvanceSalary::where('employee_id', $employee->id)
            ->where('deduction_month', $request->month)
            ->where('deduction_year', $request->year)
            ->where('status', 'Pending')
            ->whereNull('payroll_id')
            ->sum('amount');

        // Future Loan Module
        $loanAmount = EmployeeLoan::where('employee_id', $employee->id)
            ->where('status', 'Active')
            ->sum('monthly_installment');

        $netSalary = max(
            0,
            $grossSalary - $advanceAmount - $loanAmount
        );

        return response()->json([
            'basic_salary'   => number_format($basicSalary, 2, '.', ''),
            'gross_salary'   => number_format($grossSalary, 2, '.', ''),
            'advance_amount' => number_format($advanceAmount, 2, '.', ''),
            'loan_amount'    => number_format($loanAmount, 2, '.', ''),
            'net_salary'     => number_format($netSalary, 2, '.', ''),
        ]);
    }

    /**
     * Store payroll.
     */
    public function store(Request $request)
    
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

    // Prevent duplicate payroll
    $exists = Payroll::where('employee_id', $request->employee_id)
        ->where('month', $request->month)
        ->where('year', $request->year)
        ->exists();

    if ($exists) {
        return back()
            ->withInput()
            ->withErrors([
                'employee_id' => 'Payroll already exists for this employee in the selected month and year.',
            ]);
    }

    DB::beginTransaction();

    try {

        $employee = Employee::findOrFail($request->employee_id);

        $basicSalary = (float) $employee->basic_salary;
        $bonus = (float) ($request->bonus ?? 0);

        // Gross Salary
        $grossSalary = $basicSalary + $bonus;

        // Pending Advance Salary
        $advanceAmount = AdvanceSalary::where('employee_id', $employee->id)
            ->where('deduction_month', $request->month)
            ->where('deduction_year', $request->year)
            ->where('status', 'Pending')
            ->whereNull('payroll_id')
            ->sum('amount');

        // Future Loan Module
        $loanAmount = EmployeeLoan::where('employee_id', $employee->id)
            ->where('status', 'Active')
            ->sum('monthly_installment');

        // Net Salary
        $netSalary = max(
            0,
            $grossSalary - $advanceAmount - $loanAmount
        );

        $payroll = Payroll::create([
            'employee_id'      => $employee->id,
            'bank_account_id'  => $request->bank_account_id,
            'month'            => $request->month,
            'year'             => $request->year,
            'basic_salary'     => $basicSalary,
            'bonus'            => $bonus,
            'advance_amount'   => $advanceAmount,
            'loan_amount'      => $loanAmount,
            'total_salary'     => $grossSalary,
            'net_salary'       => $netSalary,
            'payment_date'     => $request->payment_date,
            'status'           => $request->status,
            'remarks'          => $request->remarks,
            'created_by'       => Auth::id(),
        ]);
        /*
        |--------------------------------------------------------------------------
        | Create Expense & Deduct Bank Balance
        |--------------------------------------------------------------------------
        */

        if ($request->status === 'Paid') {

            $salaryCategory = ExpenseCategory::where('name', 'Salary')->firstOrFail();

            $bankAccount = BankAccount::findOrFail($request->bank_account_id);

            if ($bankAccount->current_balance < $netSalary) {
                throw new \Exception('Insufficient bank balance.');
            }

            Expense::create([
                'expense_category_id' => $salaryCategory->id,
                'bank_account_id'     => $bankAccount->id,
                'payroll_id'          => $payroll->id,
                'title'               => 'Salary - ' . $employee->name,
                'amount'              => $netSalary,
                'expense_date'        => $request->payment_date ?? now(),
                'payment_method'      => $bankAccount->type === 'Cash'
                    ? 'Cash'
                    : 'Bank',
                'remarks'             => $request->remarks,
                'created_by'          => Auth::id(),
            ]);

            // Deduct bank balance
            $bankAccount->decrement(
                'current_balance',
                $netSalary
            );

            // Mark advance salaries as deducted
            AdvanceSalary::where('employee_id', $employee->id)
                ->where('deduction_month', $request->month)
                ->where('deduction_year', $request->year)
                ->where('status', 'Pending')
                ->whereNull('payroll_id')
                ->update([
                    'status'     => 'Deducted',
                    'payroll_id' => $payroll->id,
                ]);


                // Update employee loan
            EmployeeLoan::where('employee_id', $employee->id)
                ->where('status', 'Active')
                ->get()
                ->each(function ($loan) {

                    

                    $loan->paid_installments += 1;
                    $loan->remaining_balance -= $loan->monthly_installment;

                    if ($loan->remaining_balance <= 0) {
                        $loan->remaining_balance = 0;
                        $loan->status = 'Closed';
                    }

                    
                    

                    $loan->save();
                    });
        }

                   

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            'Payroll',
            'Created',
            'Created payroll for: ' . $employee->name,
            null,
            $payroll->fresh()->toArray()
        );

        DB::commit();

        return redirect()
            ->route('payrolls.index')
            ->with('success', 'Payroll Added Successfully.');

        } catch (\Exception $e) {  

        DB::rollBack();

        return back()
            ->withInput()
            ->withErrors([
                'error' => $e->getMessage(),
            ]);
    }
}
    /**
     * Display payroll.
     */
    public function show(Payroll $payroll)
    {
        $payroll->load([
            'employee',
            'bankAccount',
            'expense',
            'advanceSalaries',
        ]);

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
                'employee_id' => 'Payroll already exists for this employee in the selected month and year.',
            ]);
    }

    DB::beginTransaction();

    try {

        $oldData = $payroll->toArray();

        /*
        |--------------------------------------------------------------------------
        | Restore Previous Advance Salary
        |--------------------------------------------------------------------------
        */

        AdvanceSalary::where('payroll_id', $payroll->id)
            ->update([
                'status' => 'Pending',
                'payroll_id' => null,
            ]);

        $employee = Employee::findOrFail($request->employee_id);

        $basicSalary = (float) $employee->basic_salary;
        $bonus = (float) ($request->bonus ?? 0);

        $grossSalary = $basicSalary + $bonus;

        $advanceAmount = AdvanceSalary::where('employee_id', $employee->id)
            ->where('deduction_month', $request->month)
            ->where('deduction_year', $request->year)
            ->where('status', 'Pending')
            ->whereNull('payroll_id')
            ->sum('amount');

        

        $loanAmount = EmployeeLoan::where('employee_id', $employee->id)
            ->where('status', 'Active')
            ->where('start_year', '<=', $request->year)
            ->where(function ($query) use ($request) {
                $query->where('start_year', '<', $request->year)
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_year', $request->year)
                            ->where('start_month', '<=', $request->month);
                    });
            })
            ->sum('monthly_installment');

        $netSalary = max(
            0,
            $grossSalary - $advanceAmount - $loanAmount
        );

        $oldStatus = $payroll->status;
        $oldNetSalary = $payroll->net_salary;
        $oldBankId = $payroll->bank_account_id;

        $oldExpense = $payroll->expense;

        $payroll->update([
            'employee_id'      => $employee->id,
            'bank_account_id'  => $request->bank_account_id,
            'month'            => $request->month,
            'year'             => $request->year,
            'basic_salary'     => $basicSalary,
            'bonus'            => $bonus,
            'advance_amount'   => $advanceAmount,
            'loan_amount'      => $loanAmount,
            'total_salary'     => $grossSalary,
            'net_salary'       => $netSalary,
            'payment_date'     => $request->payment_date,
            'status'           => $request->status,
            'remarks'          => $request->remarks,
        ]);

        $salaryCategory = ExpenseCategory::where('name', 'Salary')->firstOrFail();

        $newBank = BankAccount::findOrFail($request->bank_account_id);
                /*
        |--------------------------------------------------------------------------
        | Pending -> Paid
        |--------------------------------------------------------------------------
        */
        if ($oldStatus === 'Pending' && $request->status === 'Paid') {

            

            if ($newBank->current_balance < $netSalary) {
                throw new \Exception('Insufficient bank balance.');
            }

            $payroll->expense()->create([
                'expense_category_id' => $salaryCategory->id,
                'bank_account_id'     => $newBank->id,
                'title'               => 'Salary - ' . $employee->name,
                'amount'              => $netSalary,
                'expense_date'        => $request->payment_date ?? now(),
                'payment_method'      => $newBank->type === 'Cash'
                    ? 'Cash'
                    : 'Bank',
                'remarks'             => $request->remarks,
                'created_by'          => Auth::id(),
            ]);

            $newBank->decrement(
                'current_balance',
                $netSalary
            );

            AdvanceSalary::where('employee_id', $employee->id)
                ->where('deduction_month', $request->month)
                ->where('deduction_year', $request->year)
                ->where('status', 'Pending')
                ->whereNull('payroll_id')
                ->update([
                    'status'     => 'Deducted',
                    'payroll_id' => $payroll->id,
                ]);

                

                // Update employee loan


            // Update employee loan

            EmployeeLoan::where('employee_id', $employee->id)
                ->where('status', 'Active')
                ->where('start_year', '<=', $request->year)
                ->where(function ($query) use ($request) {

                    $query->where('start_year', '<', $request->year)
                        ->orWhere(function ($q) use ($request) {

                            $q->where('start_year', $request->year)
                                ->where('start_month', '<=', $request->month);

                        });

                })
                ->get()
                ->each(function ($loan) {

                    $loan->paid_installments = ($loan->paid_installments ?? 0) + 1;

                    $loan->remaining_balance = max(
                        0,
                        $loan->remaining_balance - $loan->monthly_installment
                    );

                    if ($loan->remaining_balance <= 0) {

                        $loan->remaining_balance = 0;
                        $loan->status = 'Closed';

                    }

                    $loan->save();

                });
        }

        /*
        |--------------------------------------------------------------------------
        | Paid -> Pending
        |--------------------------------------------------------------------------
        */
        elseif ($oldStatus === 'Paid' && $request->status === 'Pending') {

            if ($oldExpense) {

                BankAccount::find($oldBankId)
                    ?->increment('current_balance', $oldNetSalary);

                $oldExpense->delete();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Paid -> Paid
        |--------------------------------------------------------------------------
        */
        elseif ($oldStatus === 'Paid' && $request->status === 'Paid') {

            $oldBank = BankAccount::findOrFail($oldBankId);

            $oldBank->increment(
                'current_balance',
                $oldNetSalary
            );

            if ($newBank->current_balance < $netSalary) {
                throw new \Exception('Insufficient bank balance.');
            }

            $newBank->decrement(
                'current_balance',
                $netSalary
            );

            if ($oldExpense) {

                $oldExpense->update([
                    'expense_category_id' => $salaryCategory->id,
                    'bank_account_id'     => $newBank->id,
                    'title'               => 'Salary - ' . $employee->name,
                    'amount'              => $netSalary,
                    'expense_date'        => $request->payment_date ?? now(),
                    'payment_method'      => $newBank->type === 'Cash'
                        ? 'Cash'
                        : 'Bank',
                    'remarks'             => $request->remarks,
                ]);
            }

            AdvanceSalary::where('employee_id', $employee->id)
                ->where('deduction_month', $request->month)
                ->where('deduction_year', $request->year)
                ->where('status', 'Pending')
                ->whereNull('payroll_id')
                ->update([
                    'status'     => 'Deducted',
                    'payroll_id' => $payroll->id,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            'Payroll',
            'Updated',
            'Updated payroll for: ' . $employee->name,
            $oldData,
            $payroll->fresh()->toArray()
        );

        DB::commit();

        return redirect()
            ->route('payrolls.index')
            ->with(
                'success',
                'Payroll Updated Successfully.'
            );

    } catch (\Exception $e) {

    DB::rollBack();

    dd($e->getMessage());
    }
}
    /**
     * Delete payroll.
     */
    public function destroy(Payroll $payroll)
    {
        DB::beginTransaction();

        try {

            $payroll->load([
                'employee',
                'expense.bankAccount',
                'advanceSalaries',
            ]);

            ActivityLogger::log(
                'Payroll',
                'Deleted',
                'Deleted payroll for: ' . $payroll->employee->name,
                $payroll->toArray(),
                null
            );

            /*
            |--------------------------------------------------------------------------
            | Restore Advance Salary
            |--------------------------------------------------------------------------
            */

            if ($payroll->status === 'Paid') {

                AdvanceSalary::where('payroll_id', $payroll->id)
                    ->update([
                        'status'     => 'Pending',
                        'payroll_id' => null,
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Restore Bank Balance
            |--------------------------------------------------------------------------
            */

            if ($payroll->status === 'Paid' && $payroll->expense) {

                $bank = $payroll->expense->bankAccount;

                if ($bank) {
                    $bank->increment(
                        'current_balance',
                        $payroll->expense->amount
                    );
                }

                $payroll->expense->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Payroll
            |--------------------------------------------------------------------------
            */

            $payroll->delete();

            DB::commit();

            return redirect()
                ->route('payrolls.index')
                ->with(
                    'success',
                    'Payroll Deleted Successfully.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }
    }
}