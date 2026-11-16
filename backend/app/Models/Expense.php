<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'expense_category_id',
        'bank_account_id',
        'payroll_id',
        'advance_salary_id',
        'title',
        'amount',
        'expense_date',
        'payment_method',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    /**
     * Expense Category
     */
    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    /**
     * Bank Account
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Payroll
     */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    /**
     * Created By
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Expenses created automatically by Payroll, Advance Salary or
     * Employee Loan. They must be changed from their own module only,
     * otherwise bank balances and payroll status go out of sync.
     */
    public function isSystemManaged(): bool
    {
        return $this->payroll_id !== null
            || ($this->getAttribute('advance_salary_id') !== null)
            || EmployeeLoan::where('expense_id', $this->id)->exists();
    }
}
