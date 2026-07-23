<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvanceSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'bank_account_id',
        'amount',
        'advance_date',
        'deduction_month',
        'deduction_year',
        'status',
        'remarks',
        'created_by',
        'payroll_id',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Office Bank/Cash Account
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Created By
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Related Expense
     */
    public function expense()
    {
        return $this->hasOne(Expense::class);
    }

    /**
     * Payroll where this advance was deducted
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}