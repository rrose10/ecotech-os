<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $expenses = Expense::with([
                'expenseCategory',
                'bankAccount',
                'creator'
            ])
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('expenses.index', compact(
            'expenses',
            'search'
        ));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()
            ->orderBy('name')
            ->get();

        $bankAccounts = BankAccount::where('is_active', true)
            ->orderBy('account_name')
            ->get();

        return view('expenses.create', compact(
            'categories',
            'bankAccounts'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required',
            'remarks' => 'nullable|string',
        ]);

        try {

        DB::transaction(function () use ($validated) {

            $bank = BankAccount::whereKey($validated['bank_account_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $bank->current_balance < (float) $validated['amount']) {
                throw new \RuntimeException('Insufficient balance in the selected account.');
            }

            $expense = Expense::create([
                ...$validated,
                'created_by' => auth()->id(),
            ]);

            $bank->decrement('current_balance', $validated['amount']);

            $expense->load([
                'expenseCategory',
                'bankAccount',
                'creator'
            ]);

            ActivityLogger::log(
                'Expense',
                'Created',
                'Created expense: ' . $expense->title,
                null,
                $expense->toArray()
            );
        });

        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::active()
            ->orderBy('name')
            ->get();

        $bankAccounts = BankAccount::where('is_active', true)
            ->orderBy('account_name')
            ->get();

        return view('expenses.edit', compact(
            'expense',
            'categories',
            'bankAccounts'
        ));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required',
            'remarks' => 'nullable|string',
        ]);

        if ($expense->isSystemManaged()) {
            return back()->with(
                'error',
                'This expense was created by Payroll, Advance Salary or Loan. Please edit it from that module.'
            );
        }

        $oldData = $expense->toArray();

        try {

        DB::transaction(function () use ($expense, $validated, $oldData) {

            $ids = array_unique([$expense->bank_account_id, (int) $validated['bank_account_id']]);

            $banks = BankAccount::whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Restore old balance
            $oldBank = $banks[$expense->bank_account_id];
            $oldBank->current_balance = round((float) $oldBank->current_balance + (float) $expense->amount, 2);

            // Deduct new balance
            $newBank = $banks[(int) $validated['bank_account_id']];

            if ((float) $newBank->current_balance < (float) $validated['amount']) {
                throw new \RuntimeException('Insufficient balance in the selected account.');
            }

            $newBank->current_balance = round((float) $newBank->current_balance - (float) $validated['amount'], 2);

            foreach ($banks as $bank) {
                $bank->save();
            }

            $expense->update($validated);

            $expense->load([
                'expenseCategory',
                'bankAccount',
                'creator'
            ]);

            ActivityLogger::log(
                'Expense',
                'Updated',
                'Updated expense: ' . $expense->title,
                $oldData,
                $expense->fresh()->toArray()
            );
        });

        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->isSystemManaged()) {
            return back()->with(
                'error',
                'This expense was created by Payroll, Advance Salary or Loan. Please delete it from that module.'
            );
        }

        $expense->load([
            'expenseCategory',
            'bankAccount',
            'creator'
        ]);

        ActivityLogger::log(
            'Expense',
            'Deleted',
            'Deleted expense: ' . $expense->title,
            $expense->toArray(),
            null
        );

        DB::transaction(function () use ($expense) {

            BankAccount::find($expense->bank_account_id)
                ->increment('current_balance', $expense->amount);

            $expense->delete();
        });

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}