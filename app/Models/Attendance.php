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
        'break_start',
        'break_end',
        'check_out',

        'working_minutes',
        'break_minutes',
        'late_minutes',

        'status',
        'shift',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',

        'check_in' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}