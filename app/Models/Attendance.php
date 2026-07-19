<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_date',
        'check_in',
        'check_out',
        'working_minutes',
        'late_minutes',
        'status',
        'shift',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in'        => 'datetime',
        'check_out'       => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}