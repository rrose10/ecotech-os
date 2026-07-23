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

        DB::transaction(function () use ($validated) {

            $expense = Expense::create([
                ...$validated,
                'created_by' => auth()->id(),
            ]);

            BankAccount::find($validated['bank_account_id'])
                ->decrement('current_balance', $validated['amount']);

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

        $oldData = $expense->toArray();

        DB::transaction(function () use ($expense, $validated, $oldData) {

            // Restore old balance
            BankAccount::find($expense->bank_account_id)
                ->increment('current_balance', $expense->amount);

            $expense->update($validated);

            // Deduct new balance
            BankAccount::find($expense->bank_account_id)
                ->decrement('current_balance', $expense->amount);

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

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
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