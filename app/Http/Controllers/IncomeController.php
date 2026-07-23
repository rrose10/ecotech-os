<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $incomes = Income::with([
                'incomeCategory',
                'bankAccount',
                'creator'
            ])
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('incomes.index', compact(
            'incomes',
            'search'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $incomeCategories = IncomeCategory::active()->get();

        $bankAccounts = BankAccount::where('is_active', true)
            ->get();

        return view('incomes.create', compact(
            'incomeCategories',
            'bankAccounts'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'income_category_id' => 'required|exists:income_categories,id',
            'bank_account_id'    => 'required|exists:bank_accounts,id',
            'title'              => 'required|string|max:255',
            'amount_bdt'         => 'nullable|numeric|min:0',
            'amount_usd'         => 'nullable|numeric|min:0',
            'income_date'        => 'required|date',
            'payment_method'     => 'required|in:Cash,Bank,bKash,Nagad,Rocket,Card',
            'remarks'            => 'nullable|string',
        ]);

        $validated['amount_bdt'] = $validated['amount_bdt'] ?? 0;
        $validated['amount_usd'] = $validated['amount_usd'] ?? 0;

        if (
            (float) $validated['amount_bdt'] == 0 &&
            (float) $validated['amount_usd'] == 0
        ) {
            return back()
                ->withErrors([
                    'amount_bdt' => 'Please enter either BDT or USD amount.',
                ])
                ->withInput();
        }

        $validated['created_by'] = auth()->id();

        $income = Income::create($validated);

        // Only BDT affects account balance
        if ($income->amount_bdt > 0) {
            BankAccount::find($income->bank_account_id)
                ?->increment('current_balance', $income->amount_bdt);
        }

        $income->load([
            'incomeCategory',
            'bankAccount',
            'creator'
        ]);

        ActivityLogger::log(
            'Income',
            'Created',
            'Created income: ' . $income->title,
            null,
            $income->toArray()
        );

        return redirect()
            ->route('incomes.index')
            ->with('success', 'Income created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Income $income)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Income $income)
    {
        $incomeCategories = IncomeCategory::active()->get();

        $bankAccounts = BankAccount::where('is_active', true)
            ->get();

        return view('incomes.edit', compact(
            'income',
            'incomeCategories',
            'bankAccounts'
        ));
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, Income $income)
    {
        $validated = $request->validate([
            'income_category_id' => 'required|exists:income_categories,id',
            'bank_account_id'    => 'required|exists:bank_accounts,id',
            'title'              => 'required|string|max:255',
            'amount_bdt'         => 'nullable|numeric|min:0',
            'amount_usd'         => 'nullable|numeric|min:0',
            'income_date'        => 'required|date',
            'payment_method'     => 'required|in:Cash,Bank,bKash,Nagad,Rocket,Card',
            'remarks'            => 'nullable|string',
        ]);

        $validated['amount_bdt'] = $validated['amount_bdt'] ?? 0;
        $validated['amount_usd'] = $validated['amount_usd'] ?? 0;

        if (
            (float) $validated['amount_bdt'] == 0 &&
            (float) $validated['amount_usd'] == 0
        ) {
            return back()
                ->withErrors([
                    'amount_bdt' => 'Please enter either BDT or USD amount.',
                ])
                ->withInput();
        }

        // Old data for Activity Log
        $oldData = $income->toArray();

        // Remove previous BDT amount from previous account
        if ($income->amount_bdt > 0) {
            BankAccount::find($income->bank_account_id)
                ?->decrement('current_balance', $income->amount_bdt);
        }

        $income->update($validated);

        // Add new BDT amount to selected account
        if ($income->amount_bdt > 0) {
            BankAccount::find($income->bank_account_id)
                ?->increment('current_balance', $income->amount_bdt);
        }

        $income->load([
            'incomeCategory',
            'bankAccount',
            'creator'
        ]);

        ActivityLogger::log(
            'Income',
            'Updated',
            'Updated income: ' . $income->title,
            $oldData,
            $income->fresh()->toArray()
        );

        return redirect()
            ->route('incomes.index')
            ->with('success', 'Income updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Income $income)
    {
        $income->load([
            'incomeCategory',
            'bankAccount',
            'creator'
        ]);

        ActivityLogger::log(
            'Income',
            'Deleted',
            'Deleted income: ' . $income->title,
            $income->toArray(),
            null
        );

        if ($income->amount_bdt > 0) {
            BankAccount::find($income->bank_account_id)
                ?->decrement('current_balance', $income->amount_bdt);
        }

        $income->delete();

        return redirect()
            ->route('incomes.index')
            ->with('success', 'Income deleted successfully.');
    }
}