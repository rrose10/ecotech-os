<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'department_id',
        'employee_id',
        'name',
        'email',
        'phone',
        'designation',
        'joining_date',
        'salary',
        'date_of_birth',
        'address',
        'photo',
        'status',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}