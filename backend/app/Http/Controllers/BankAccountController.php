<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $accounts = BankAccount::when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('account_name', 'like', "%{$search}%")
                        ->orWhere('bank_name', 'like', "%{$search}%")
                        ->orWhere('account_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('bank_accounts.index', compact(
            'accounts',
            'search'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bank_accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255|unique:bank_accounts,account_name',
            'type' => 'required|in:Cash,Bank',
            'bank_name' => 'required_if:type,Bank|nullable|string|max:255',
            'account_number' => 'required_if:type,Bank|nullable|string|max:255',
            'opening_balance' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['current_balance'] = $validated['opening_balance'];

        $bankAccount = BankAccount::create($validated);

        ActivityLogger::log(
            'Bank Account',
            'Created',
            'Created bank account: ' . $bankAccount->account_name,
            null,
            $bankAccount->toArray()
        );

        return redirect()
            ->route('bank-accounts.index')
            ->with('success', 'Bank account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BankAccount $bankAccount)
    {
        return redirect()->route('bank-accounts.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankAccount $bankAccount)
    {
        return view('bank_accounts.edit', compact('bankAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'account_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('bank_accounts')->ignore($bankAccount->id),
            ],
            'type' => 'required|in:Cash,Bank',
            'bank_name' => 'required_if:type,Bank|nullable|string|max:255',
            'account_number' => 'required_if:type,Bank|nullable|string|max:255',
            'opening_balance' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        // Old data for Activity Log
        $oldData = $bankAccount->toArray();

        $difference = $validated['opening_balance'] - $bankAccount->opening_balance;

        $validated['current_balance'] = $bankAccount->current_balance + $difference;

        $bankAccount->update($validated);

        ActivityLogger::log(
            'Bank Account',
            'Updated',
            'Updated bank account: ' . $bankAccount->account_name,
            $oldData,
            $bankAccount->fresh()->toArray()
        );

        return redirect()
            ->route('bank-accounts.index')
            ->with('success', 'Bank account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankAccount $bankAccount)
    {
        if (
            $bankAccount->expenses()->exists() ||
            $bankAccount->incomes()->exists() ||
            $bankAccount->payrolls()->exists()
        ) {
            return redirect()
                ->route('bank-accounts.index')
                ->with('error', 'This bank account is already used and cannot be deleted.');
        }

        ActivityLogger::log(
            'Bank Account',
            'Deleted',
            'Deleted bank account: ' . $bankAccount->account_name,
            $bankAccount->toArray(),
            null
        );

        $bankAccount->delete();

        return redirect()
            ->route('bank-accounts.index')
            ->with('success', 'Bank account deleted successfully.');
    }
}