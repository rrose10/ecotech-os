<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'bank_account_id',

        'loan_amount',
        'monthly_installment',

        'total_installments',
        'paid_installments',

        'remaining_balance',

        'loan_date',

        'start_month',
        'start_year',

        'end_month',
        'end_year',

        'status',
        'remarks',

        'expense_id',
        'payroll_id',
        'created_by',
    ];

    protected $casts = [
        'loan_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}