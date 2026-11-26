<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLoan;
use App\Models\LoanSkip;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeLoanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $loans = EmployeeLoan::with([
                'employee',
                'bankAccount'
            ])
            ->when($search, function ($query) use ($search) {

                $query->whereHas('employee', function ($q) use ($search) {

                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");

                });

            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'employee-loans.index',
            compact(
                'loans',
                'search'
            )
        );
    }

    public function create()
    {
        $employees = Employee::where('status', true)
            ->orderBy('name')
            ->get();

        $bankAccounts = BankAccount::where('is_active', true)
            ->orderBy('account_name')
            ->get();

        return view(
            'employee-loans.create',
            compact(
                'employees',
                'bankAccounts'
            )
        );
    }

    public function store(Request $request)
    {
        $request->validate([

            'employee_id'            => 'required|exists:employees,id',

            'bank_account_id'        => 'required|exists:bank_accounts,id',

            'loan_amount'            => 'required|numeric|min:1',

            'monthly_installment'    => 'required|numeric|min:1',

            'loan_date'              => 'required|date',

            'start_month'            => 'required|integer|between:1,12',

            'start_year'             => 'required|integer|min:2024|max:2100',

            'remarks'                => 'nullable|string',

        ]);

        DB::beginTransaction();

        try {

            $employee = Employee::findOrFail(
                $request->employee_id
            );

            $bankAccount = BankAccount::findOrFail(
                $request->bank_account_id
            );

            if ($bankAccount->current_balance < $request->loan_amount) {

                throw new \Exception(
                    'Insufficient bank balance.'
                );

            }

            // Last installment month, worked out from the start month when left blank.
            $steps = max(0, (int) $request->total_installments - 1) + ((int) $request->start_month - 1);
            $endMonth = $steps % 12 + 1;
            $endYear = (int) $request->start_year + intdiv($steps, 12);

            $loan = EmployeeLoan::create([

                'employee_id'            => $employee->id,

                'bank_account_id'        => $bankAccount->id,

                'loan_amount'            => $request->loan_amount,

                'monthly_installment'    => $request->monthly_installment,

                'total_installments' => $request->total_installments,

                'paid_installments' => 0,

                'end_month' => $request->filled('end_month') ? $request->end_month : $endMonth,

                'end_year' => $request->filled('end_year') ? $request->end_year : $endYear,

                'remaining_balance'      => $request->loan_amount,

                'loan_date'              => $request->loan_date,

                'start_month'            => $request->start_month,

                'start_year'             => $request->start_year,

                'status'                 => 'Active',

                'remarks'                => $request->remarks,

                'created_by'             => Auth::id(),

            ]);

            $expenseCategory = ExpenseCategory::firstOrCreate(['name' => 'Employee Loan'], ['status' => true]);

            $expense = Expense::create([

                'expense_category_id'    => $expenseCategory->id,

                'bank_account_id'        => $bankAccount->id,

                'title'                  => 'Employee Loan - ' . $employee->name,

                'amount'                 => $request->loan_amount,

                'expense_date'           => $request->loan_date,

                'payment_method'         => $bankAccount->type == 'Cash'
                                                ? 'Cash'
                                                : 'Bank',

                'remarks'                => $request->remarks,

                'created_by'             => Auth::id(),

            ]);

            $loan->update([

                'expense_id' => $expense->id,

            ]);

            $bankAccount->decrement(
                'current_balance',
                $request->loan_amount
            );

            ActivityLogger::log(

                'Employee Loan',

                'Created',

                'Created employee loan for: ' . $employee->name,

                null,

                $loan->fresh()->toArray()

            );

            DB::commit();

            return redirect()
                ->route('employee-loans.index')
                ->with(
                    'success',
                    'Team loan created successfully.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors([

                    'error' => $e->getMessage()

                ]);

        }
    }

    public function show(EmployeeLoan $employeeLoan)
    {
        $employeeLoan->load([
            'employee',
            'bankAccount',
            'expense',
            'creator',
            'skips',
        ]);

        return view(
            'employee-loans.show',
            compact('employeeLoan')
        );
    }

    public function edit(EmployeeLoan $employeeLoan)
    {
        $employees = Employee::where('status', true)
            ->orderBy('name')
            ->get();

        $bankAccounts = BankAccount::where('is_active', true)
            ->orderBy('account_name')
            ->get();

        return view(
            'employee-loans.edit',
            compact(
                'employeeLoan',
                'employees',
                'bankAccounts'
            )
        );
    }

    public function update(Request $request, EmployeeLoan $employeeLoan)
    {
        $request->validate([

            'employee_id'         => 'required|exists:employees,id',
            'bank_account_id'     => 'required|exists:bank_accounts,id',
            'loan_amount'         => 'required|numeric|min:1',
            'monthly_installment' => 'required|numeric|min:1',
            'loan_date'           => 'required|date',
            'start_month'         => 'required|integer|between:1,12',
            'start_year'          => 'required|integer|min:2024|max:2100',
            'status'              => 'required|in:Active,Closed',
            'remarks'             => 'nullable|string',

        ]);

        DB::beginTransaction();

        try {

            $oldData = $employeeLoan->toArray();

            // Restore old bank balance
            $oldBank = BankAccount::findOrFail(
                $employeeLoan->bank_account_id
            );

            $oldBank->increment(
                'current_balance',
                $employeeLoan->loan_amount
            );

            // New bank
            $newBank = BankAccount::findOrFail(
                $request->bank_account_id
            );

            if ($newBank->current_balance < $request->loan_amount) {
                throw new \Exception('Insufficient bank balance.');
            }

            $newBank->decrement(
                'current_balance',
                $request->loan_amount
            );

            $employee = Employee::findOrFail(
                $request->employee_id
            );

            $employeeLoan->update([

                'employee_id'         => $employee->id,
                'bank_account_id'     => $newBank->id,

                'loan_amount'         => $request->loan_amount,

                'monthly_installment' => $request->monthly_installment,

                'remaining_balance'   => $employeeLoan->remaining_balance,

                'loan_date'           => $request->loan_date,

                'start_month'         => $request->start_month,

                'start_year'          => $request->start_year,

                'status'              => $request->status,

                'remarks'             => $request->remarks,

            ]);

            if ($employeeLoan->expense) {

                $expenseCategory = ExpenseCategory::firstOrCreate(['name' => 'Employee Loan'], ['status' => true]);

                $employeeLoan->expense->update([

                    'expense_category_id' => $expenseCategory->id,

                    'bank_account_id'     => $newBank->id,

                    'title'               => 'Employee Loan - ' . $employee->name,

                    'amount'              => $request->loan_amount,

                    'expense_date'        => $request->loan_date,

                    'payment_method'      => $newBank->type == 'Cash'
                                                ? 'Cash'
                                                : 'Bank',

                    'remarks'             => $request->remarks,

                ]);

            }

            ActivityLogger::log(

                'Employee Loan',

                'Updated',

                'Updated employee loan for: ' . $employee->name,

                $oldData,

                $employeeLoan->fresh()->toArray()

            );

            DB::commit();

            return redirect()
                ->route('employee-loans.index')
                ->with(
                    'success',
                    'Team loan updated successfully.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors([
                    'error' => $e->getMessage()
                ]);

        }
    }

    public function destroy(EmployeeLoan $employeeLoan)
    {
        DB::beginTransaction();

        try {

            $employeeLoan->load([
                'employee',
                'expense',
                'bankAccount',
            ]);

            ActivityLogger::log(
                'Employee Loan',
                'Deleted',
                'Deleted employee loan for: ' . $employeeLoan->employee->name,
                $employeeLoan->toArray(),
                null
            );

            // Restore Bank Balance
            if ($employeeLoan->bankAccount) {

                $employeeLoan->bankAccount->increment(
                    'current_balance',
                    $employeeLoan->loan_amount
                );

            }

            // Delete Expense
            if ($employeeLoan->expense) {

                $employeeLoan->expense->delete();

            }

            $employeeLoan->delete();

            DB::commit();

            return redirect()
                ->route('employee-loans.index')
                ->with(
                    'success',
                    'Team loan deleted successfully.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $e->getMessage()
            ]);

        }
    }

    /**
     * Do not collect the installment of one month. The last installment
     * moves one month later; balance and paid installments stay the same.
     */
    public function skipMonth(Request $request, EmployeeLoan $employeeLoan)
    {
        $data = $request->validate([
            'month'  => 'required|integer|between:1,12',
            'year'   => 'required|integer|min:2024|max:2100',
            'reason' => 'nullable|string|max:255',
        ]);

        $month = (int) $data['month'];
        $year  = (int) $data['year'];
        $at    = $year * 12 + $month;

        if ($employeeLoan->status !== 'Active') {
            return back()->withErrors(['error' => 'Only an active loan can skip a month.']);
        }

        if ($at < $employeeLoan->start_year * 12 + $employeeLoan->start_month
            || $at > $employeeLoan->end_year * 12 + $employeeLoan->end_month) {
            return back()->withErrors(['error' => 'Pick a month between the loan start and its last installment.']);
        }

        if ($employeeLoan->skips()->where('month', $month)->where('year', $year)->exists()) {
            return back()->withErrors(['error' => 'That month is already skipped.']);
        }

        $paid = Payroll::where('employee_id', $employeeLoan->employee_id)
            ->where('month', $month)->where('year', $year)->where('status', 'Paid')->exists();

        if ($paid) {
            return back()->withErrors(['error' => 'Payroll for that month is already Paid, so its installment was collected.']);
        }

        $pending = Payroll::where('employee_id', $employeeLoan->employee_id)
            ->where('month', $month)->where('year', $year)->where('status', 'Pending')->exists();

        DB::transaction(function () use ($employeeLoan, $month, $year, $data) {
            LoanSkip::create([
                'employee_loan_id' => $employeeLoan->id,
                'month'            => $month,
                'year'             => $year,
                'reason'           => $data['reason'] ?? null,
                'created_by'       => Auth::id(),
            ]);

            $this->shiftEnd($employeeLoan, 1);

            ActivityLogger::log(
                'Employee Loan', 'Skipped',
                'Skipped ' . sprintf('%02d/%d', $month, $year) . ' installment for loan #' . $employeeLoan->id,
                null, $employeeLoan->fresh()->toArray()
            );
        });

        $message = 'Installment skipped. The last installment moved one month later.';

        if ($pending) {
            $message .= ' A Pending payroll exists for that month: open it and save again so the loan deduction is recalculated.';
        }

        return back()->with('success', $message);
    }

    /** Collect that month's installment again. */
    public function undoSkip(EmployeeLoan $employeeLoan, LoanSkip $skip)
    {
        abort_unless($skip->employee_loan_id === $employeeLoan->id, 404);

        $paid = Payroll::where('employee_id', $employeeLoan->employee_id)
            ->where('month', $skip->month)->where('year', $skip->year)->where('status', 'Paid')->exists();

        if ($paid) {
            return back()->withErrors(['error' => 'Payroll for that month is already Paid without this installment, so the skip cannot be undone.']);
        }

        DB::transaction(function () use ($employeeLoan, $skip) {
            $skip->delete();
            $this->shiftEnd($employeeLoan, -1);
        });

        return back()->with('success', 'Skip removed. That month will collect the installment again.');
    }

    /** Move the loan's last installment month by +1 / -1 month. */
    private function shiftEnd(EmployeeLoan $loan, int $months): void
    {
        $at = $loan->end_year * 12 + ($loan->end_month - 1) + $months;

        $loan->update([
            'end_month' => $at % 12 + 1,
            'end_year'  => intdiv($at, 12),
        ]);
    }
}
