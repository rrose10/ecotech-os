<?php

namespace App\Http\Controllers;

use App\Models\AdvanceSalary;
use App\Models\BankAccount;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdvanceSalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $advanceSalaries = AdvanceSalary::with([
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

        return view('advance-salaries.index', compact(
            'advanceSalaries',
            'search'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::where('status', true)
            ->orderBy('name')
            ->get();

        $bankAccounts = BankAccount::where('is_active', true)
            ->orderBy('account_name')
            ->get();

        return view('advance-salaries.create', compact(
            'employees',
            'bankAccounts'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id'      => 'required|exists:employees,id',
            'bank_account_id'  => 'required|exists:bank_accounts,id',
            'amount'           => 'required|numeric|min:1',
            'advance_date'     => 'required|date',
            'deduction_month'  => 'required|integer|between:1,12',
            'deduction_year'   => 'required|integer|min:2024|max:2100',
            'remarks'          => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {

            $employee = Employee::findOrFail($request->employee_id);

            $bankAccount = BankAccount::findOrFail($request->bank_account_id);

            if ($bankAccount->current_balance < $request->amount) {
                throw new \Exception('Insufficient bank balance.');
            }

            $advanceSalary = AdvanceSalary::create([
                'employee_id'      => $employee->id,
                'bank_account_id'  => $bankAccount->id,
                'amount'           => $request->amount,
                'advance_date'     => $request->advance_date,
                'deduction_month'  => $request->deduction_month,
                'deduction_year'   => $request->deduction_year,
                'status'           => 'Pending',
                'remarks'          => $request->remarks,
                'created_by'       => Auth::id(),
            ]);

            $expenseCategory = ExpenseCategory::firstOrCreate(['name' => 'Advance Salary'], ['status' => true]);

            Expense::create([
                'expense_category_id' => $expenseCategory->id,
                'bank_account_id'     => $bankAccount->id,
                'advance_salary_id'   => $advanceSalary->id,
                'title'               => 'Advance Salary - ' . $employee->name,
                'amount'              => $request->amount,
                'expense_date'        => $request->advance_date,
                'payment_method'      => $bankAccount->type === 'Cash'
                                            ? 'Cash'
                                            : 'Bank',
                'remarks'             => $request->remarks,
                'created_by'          => Auth::id(),
            ]);

            $bankAccount->decrement(
                'current_balance',
                $request->amount
            );

            ActivityLogger::log(
                'Advance Salary',
                'Created',
                'Created advance salary for: ' . $employee->name,
                null,
                $advanceSalary->fresh()->toArray()
            );

            DB::commit();

            return redirect()
                ->route('advance-salaries.index')
                ->with(
                    'success',
                    'Advance salary added successfully.'
                );

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
     * Display the specified resource.
     */
    public function show(AdvanceSalary $advanceSalary)
    {
        return view('advance-salaries.show', compact('advanceSalary'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdvanceSalary $advanceSalary)
    {
        $employees = Employee::where('status', true)
            ->orderBy('name')
            ->get();

        $bankAccounts = BankAccount::where('is_active', true)
            ->orderBy('account_name')
            ->get();

        return view('advance-salaries.edit', compact(
            'advanceSalary',
            'employees',
            'bankAccounts'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdvanceSalary $advanceSalary)
    {
        $request->validate([
            'employee_id'      => 'required|exists:employees,id',
            'bank_account_id'  => 'required|exists:bank_accounts,id',
            'amount'           => 'required|numeric|min:1',
            'advance_date'     => 'required|date',
            'deduction_month'  => 'required|integer|between:1,12',
            'deduction_year'   => 'required|integer|min:2024|max:2100',
            'status'           => 'required|in:Pending,Deducted,Cancelled',
            'remarks'          => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {

            $oldData = $advanceSalary->toArray();

            $oldBank = BankAccount::findOrFail($advanceSalary->bank_account_id);
            $oldBank->increment('current_balance', $advanceSalary->amount);

            $newBank = BankAccount::findOrFail($request->bank_account_id);

            if ($newBank->current_balance < $request->amount) {
                throw new \Exception('Insufficient bank balance.');
            }

            $newBank->decrement('current_balance', $request->amount);

            $employee = Employee::findOrFail($request->employee_id);

            $advanceSalary->update([
                'employee_id'      => $employee->id,
                'bank_account_id'  => $newBank->id,
                'amount'           => $request->amount,
                'advance_date'     => $request->advance_date,
                'deduction_month'  => $request->deduction_month,
                'deduction_year'   => $request->deduction_year,
                'status'           => $request->status,
                'remarks'          => $request->remarks,
            ]);

            if ($advanceSalary->expense) {

                $expenseCategory = ExpenseCategory::firstOrCreate(['name' => 'Advance Salary'], ['status' => true]);

                $advanceSalary->expense->update([
                    'expense_category_id' => $expenseCategory->id,
                    'bank_account_id'     => $newBank->id,
                    'title'               => 'Advance Salary - ' . $employee->name,
                    'amount'              => $request->amount,
                    'expense_date'        => $request->advance_date,
                    'payment_method'      => $newBank->type === 'Cash'
                                                ? 'Cash'
                                                : 'Bank',
                    'remarks'             => $request->remarks,
                ]);
            }

            ActivityLogger::log(
                'Advance Salary',
                'Updated',
                'Updated advance salary for: ' . $employee->name,
                $oldData,
                $advanceSalary->fresh()->toArray()
            );

            DB::commit();

            return redirect()
                ->route('advance-salaries.index')
                ->with(
                    'success',
                    'Advance salary updated successfully.'
                );

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
     * Remove the specified resource from storage.
     */
    public function destroy(AdvanceSalary $advanceSalary)
    {
        DB::beginTransaction();

        try {

            $advanceSalary->load([
                'employee',
                'expense',
                'bankAccount',
            ]);

            ActivityLogger::log(
                'Advance Salary',
                'Deleted',
                'Deleted advance salary for: ' . $advanceSalary->employee->name,
                $advanceSalary->toArray(),
                null
            );

            if ($advanceSalary->expense) {

                $bank = $advanceSalary->bankAccount;

                if ($bank) {
                    $bank->increment(
                        'current_balance',
                        $advanceSalary->amount
                    );
                }

                $advanceSalary->expense->delete();
            }

            $advanceSalary->delete();

            DB::commit();

            return redirect()
                ->route('advance-salaries.index')
                ->with(
                    'success',
                    'Advance salary deleted successfully.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }
    }
}