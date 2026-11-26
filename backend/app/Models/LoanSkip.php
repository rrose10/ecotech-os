<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanSkip extends Model
{
    protected $fillable = ['employee_loan_id', 'month', 'year', 'reason', 'created_by'];

    public function loan()
    {
        return $this->belongsTo(EmployeeLoan::class, 'employee_loan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
