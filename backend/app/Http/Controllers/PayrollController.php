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
     *
     * Keeps Payroll, Expense and BankAccount balance in sync for all
     * status transitions (Pending/Paid -> Pending/Paid).
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

            // Re-read the payroll inside the transaction with a lock
            $payroll = Payroll::whereKey($payroll->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldData       = $payroll->toArray();
            $oldStatus     = $payroll->status;
            $newStatus     = $request->status;
            $oldNetSalary  = round((float) $payroll->net_salary, 2);
            $oldLoanAmount = round((float) $payroll->loan_amount, 2);

            $newEmployeeId = (int) $request->employee_id;
            $newMonth      = (int) $request->month;
            $newYear       = (int) $request->year;

            $keysChanged = $newEmployeeId !== (int) $payroll->employee_id
                || $newMonth !== (int) $payroll->month
                || $newYear  !== (int) $payroll->year;

            /*
            |--------------------------------------------------------------------------
            | Existing linked Expense (source of truth for a Paid payroll)
            |--------------------------------------------------------------------------
            */

            $oldExpense = null;

            if ($oldStatus === 'Paid') {

                $expenses = Expense::where('payroll_id', $payroll->id)
                    ->lockForUpdate()
                    ->get();

                if ($expenses->count() > 1) {
                    throw new \Exception(
                        'Data inconsistency: this payroll has more than one linked expense. Please fix the expense records first.'
                    );
                }

                $oldExpense = $expenses->first();
            }

            /*
            |--------------------------------------------------------------------------
            | Loan installments are not tracked per payroll, so they cannot be
            | reversed safely. Block the changes that would require it.
            |--------------------------------------------------------------------------
            */

            if ($oldStatus === 'Paid' && $oldLoanAmount > 0) {

                if ($newStatus === 'Pending') {
                    throw new \Exception(
                        'This paid payroll included a loan installment. It cannot be moved back to Pending automatically.'
                    );
                }

                if ($keysChanged) {
                    throw new \Exception(
                        'This paid payroll included a loan installment. Team member, month and year cannot be changed.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Restore previously linked Advance Salaries
            |--------------------------------------------------------------------------
            */

            AdvanceSalary::where('payroll_id', $payroll->id)
                ->update([
                    'status'     => 'Pending',
                    'payroll_id' => null,
                ]);

            /*
            |--------------------------------------------------------------------------
            | Calculate new amounts
            |--------------------------------------------------------------------------
            */

            $employee = Employee::findOrFail($newEmployeeId);

            $basicSalary = (float) $employee->basic_salary;
            $bonus       = (float) ($request->bonus ?? 0);
            $grossSalary = $basicSalary + $bonus;

            $advanceAmount = (float) AdvanceSalary::where('employee_id', $employee->id)
                ->where('deduction_month', $newMonth)
                ->where('deduction_year', $newYear)
                ->where('status', 'Pending')
                ->whereNull('payroll_id')
                ->sum('amount');

            // A Paid payroll whose employee/month/year did not change keeps its
            // loan installment (already deducted). Everything else is recalculated.
            $keepLoanSnapshot = $oldStatus === 'Paid' && !$keysChanged;

            $loanAmount = $keepLoanSnapshot
                ? $oldLoanAmount
                : (float) $this->eligibleLoans($employee->id, $newMonth, $newYear)
                    ->sum('monthly_installment');

            $netSalary = round(max(0, $grossSalary - $advanceAmount - $loanAmount), 2);

            /*
            |--------------------------------------------------------------------------
            | Lock the bank accounts involved (fixed order, avoids deadlocks)
            |--------------------------------------------------------------------------
            */

            $oldBankId = $oldExpense
                ? (int) $oldExpense->bank_account_id
                : (int) $payroll->bank_account_id;

            $newBankId = (int) $request->bank_account_id;

            $banks = BankAccount::whereIn('id', array_unique([$oldBankId, $newBankId]))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $oldBank = $banks->get($oldBankId);
            $newBank = $banks->get($newBankId);

            if (!$newBank) {
                throw new \Exception('Selected bank account was not found.');
            }

            /*
            |--------------------------------------------------------------------------
            | Step 1: reverse the old payment (Paid -> anything)
            | If the expense is missing, no recorded deduction exists to restore.
            |--------------------------------------------------------------------------
            */

            if ($oldStatus === 'Paid' && $oldExpense) {

                if (!$oldBank) {
                    throw new \Exception('The bank account of the previous payment was not found.');
                }

                $oldBank->current_balance = round(
                    (float) $oldBank->current_balance + (float) $oldExpense->amount,
                    2
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Step 2: apply the new state
            |--------------------------------------------------------------------------
            */

            $newExpenseData = null;

            if ($newStatus === 'Paid') {

                // Balance check happens AFTER the old amount was restored
                if (round((float) $newBank->current_balance, 2) < $netSalary) {
                    throw new \Exception('Insufficient bank balance.');
                }

                $newBank->current_balance = round(
                    (float) $newBank->current_balance - $netSalary,
                    2
                );

                $salaryCategory = ExpenseCategory::where('name', 'Salary')->firstOrFail();

                $newExpenseData = [
                    'expense_category_id' => $salaryCategory->id,
                    'bank_account_id'     => $newBank->id,
                    'payroll_id'          => $payroll->id,
                    'title'               => 'Salary - ' . $employee->name,
                    'amount'              => $netSalary,
                    'expense_date'        => $request->payment_date ?? now(),
                    'payment_method'      => $newBank->type === 'Cash' ? 'Cash' : 'Bank',
                    'remarks'             => $request->remarks,
                ];
            }

            // Save every touched bank account once (same-bank case shares one object)
            foreach ($banks as $bank) {
                if ($bank->isDirty()) {
                    $bank->save();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Step 3: update Payroll
            |--------------------------------------------------------------------------
            */

            $payroll->update([
                'employee_id'      => $employee->id,
                'bank_account_id'  => $newBank->id,
                'month'            => $newMonth,
                'year'             => $newYear,
                'basic_salary'     => $basicSalary,
                'bonus'            => $bonus,
                'advance_amount'   => $advanceAmount,
                'loan_amount'      => $loanAmount,
                'total_salary'     => $grossSalary,
                'net_salary'       => $netSalary,
                'payment_date'     => $request->payment_date,
                'status'           => $newStatus,
                'remarks'          => $request->remarks,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Step 4: sync Expense, Advance Salary and Loans
            |--------------------------------------------------------------------------
            */

            if ($newStatus === 'Pending') {

                // Paid -> Pending : remove the salary expense
                if ($oldExpense) {
                    $oldExpense->delete();
                }

            } else {

                if ($oldExpense) {
                    // Paid -> Paid : update the existing expense
                    $oldExpense->update($newExpenseData);
                } else {
                    // Pending -> Paid (or Paid with missing expense) : create it
                    $newExpenseData['created_by'] = Auth::id();
                    Expense::create($newExpenseData);
                }

                AdvanceSalary::where('employee_id', $employee->id)
                    ->where('deduction_month', $newMonth)
                    ->where('deduction_year', $newYear)
                    ->where('status', 'Pending')
                    ->whereNull('payroll_id')
                    ->update([
                        'status'     => 'Deducted',
                        'payroll_id' => $payroll->id,
                    ]);

                // Loan installments are applied only when they were not
                // already applied by this payroll.
                if (!$keepLoanSnapshot) {
                    $this->applyLoanInstallments($employee->id, $newMonth, $newYear);
                }
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
                ->with('success', 'Payroll Updated Successfully.');

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors([
                    'error' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Delete payroll.
     *
     * A Paid payroll gives its money back to the bank account and its
     * salary expense is removed.
     */
    public function destroy(Payroll $payroll)
    {
        DB::beginTransaction();

        try {

            $payroll = Payroll::whereKey($payroll->id)
                ->lockForUpdate()
                ->firstOrFail();

            $payroll->load('employee');

            if ($payroll->status === 'Paid') {

                if ((float) $payroll->loan_amount > 0) {
                    throw new \Exception(
                        'This paid payroll included a loan installment. It cannot be deleted automatically.'
                    );
                }

                $expenses = Expense::where('payroll_id', $payroll->id)
                    ->lockForUpdate()
                    ->get();

                if ($expenses->count() !== 1) {
                    throw new \Exception(
                        'Data inconsistency: a paid payroll must have exactly one linked expense. Please fix the expense records first.'
                    );
                }

                $expense = $expenses->first();

                $bank = BankAccount::whereKey($expense->bank_account_id)
                    ->lockForUpdate()
                    ->first();

                if (!$bank) {
                    throw new \Exception('The bank account of this payment was not found.');
                }

                // Give the money back (the expense is the source of truth)
                $bank->current_balance = round(
                    (float) $bank->current_balance + (float) $expense->amount,
                    2
                );
                $bank->save();

                $expense->delete();
            }

            // Release advance salaries linked to this payroll
            AdvanceSalary::where('payroll_id', $payroll->id)
                ->update([
                    'status'     => 'Pending',
                    'payroll_id' => null,
                ]);

            ActivityLogger::log(
                'Payroll',
                'Deleted',
                'Deleted payroll for: ' . $payroll->employee->name,
                $payroll->toArray(),
                null
            );

            $payroll->delete();

            DB::commit();

            return redirect()
                ->route('payrolls.index')
                ->with('success', 'Payroll Deleted Successfully.');

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Active loans of an employee whose payroll start is on/before month-year.
     */
    private function eligibleLoans(int $employeeId, int $month, int $year)
    {
        return EmployeeLoan::where('employee_id', $employeeId)
            ->where('status', 'Active')
            ->where(function ($query) use ($month, $year) {
                $query->where('start_year', '<', $year)
                    ->orWhere(function ($q) use ($month, $year) {
                        $q->where('start_year', $year)
                            ->where('start_month', '<=', $month);
                    });
            });
    }

    /**
     * Apply one installment to every eligible active loan.
     */
    private function applyLoanInstallments(int $employeeId, int $month, int $year): void
    {
        $this->eligibleLoans($employeeId, $month, $year)
            ->lockForUpdate()
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
}
