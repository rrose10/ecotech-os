<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'bank_account_id',
        'month',
        'year',
        'basic_salary',
        'bonus',
        'advance_amount',
        'loan_amount',
        'total_salary',
        'net_salary',
        'payment_date',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'basic_salary'    => 'decimal:2',
        'bonus'           => 'decimal:2',
        'advance_amount'  => 'decimal:2',
        'loan_amount'     => 'decimal:2',
        'total_salary'    => 'decimal:2',
        'net_salary'      => 'decimal:2',
        'payment_date'    => 'date',
    ];

    /**
     * Employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Bank Account
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Salary Expense
     */
    public function expense(): HasOne
    {
        return $this->hasOne(Expense::class);
    }

    /**
     * Advance Salaries deducted in this payroll
     */
    public function advanceSalaries(): HasMany
    {
        return $this->hasMany(AdvanceSalary::class);
    }

    /**
     * Created By
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}