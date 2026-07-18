<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'department_id',
        'employee_id',
        'name',
        'email',
        'phone',
        'designation',
        'basic_salary',
        'joining_date',
        'date_of_birth',
        'gender',
        'blood_group',
        'emergency_contact',
        'address',
        'photo',
        'status',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'joining_date' => 'date',
        'date_of_birth' => 'date',
        'status' => 'boolean',
    ];

    /**
     * Employee belongs to Department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Employee has many Attendances
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Employee has many Payrolls
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }
}