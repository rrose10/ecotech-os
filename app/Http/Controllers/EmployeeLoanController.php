<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLoan;
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

            $loan = EmployeeLoan::create([

                'employee_id'            => $employee->id,

                'bank_account_id'        => $bankAccount->id,

                'loan_amount'            => $request->loan_amount,

                'monthly_installment'    => $request->monthly_installment,

                'total_installments' => $request->total_installments,

                'paid_installments' => 0,

                'end_month' => $request->end_month,

                'end_year' => $request->end_year,

                'remaining_balance'      => $request->loan_amount,

                'loan_date'              => $request->loan_date,

                'start_month'            => $request->start_month,

                'start_year'             => $request->start_year,

                'status'                 => 'Active',

                'remarks'                => $request->remarks,

                'created_by'             => Auth::id(),

            ]);

            $expenseCategory = ExpenseCategory::where(
                'name',
                'Employee Loan'
            )->firstOrFail();

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
                    'Employee loan created successfully.'
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

                $expenseCategory = ExpenseCategory::where(
                    'name',
                    'Employee Loan'
                )->firstOrFail();

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
                    'Employee loan updated successfully.'
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
                    'Employee loan deleted successfully.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $e->getMessage()
            ]);

        }
    }
}
