<?php

namespace App\Models;

use Carbon\Carbon;
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

    /*
    |--------------------------------------------------------------------------
    | Night shift helpers (7:00 PM -> 6:00 AM next day)
    |--------------------------------------------------------------------------
    */

    public const SHIFT_START_HOUR = 19;
    public const SHIFT_END_HOUR   = 6;

    /**
     * The attendance date (= shift start date) a given moment belongs to.
     * Before 6 AM we are still inside yesterday's shift.
     */
    public static function shiftDateFor(?Carbon $moment = null): Carbon
    {
        $moment = ($moment ?? Carbon::now())->copy();

        $date = $moment->hour < self::SHIFT_END_HOUR
            ? $moment->copy()->subDay()
            : $moment->copy();

        return $date->startOfDay();
    }

    /**
     * 7:00 PM of the given attendance date.
     */
    public static function shiftStartFor(Carbon $shiftDate): Carbon
    {
        return $shiftDate->copy()->startOfDay()->setTime(self::SHIFT_START_HOUR, 0);
    }

    /**
     * Whole minutes between two moments (never negative, never fractional).
     */
    public static function minutesBetween(Carbon $from, Carbon $to): int
    {
        return max(0, (int) round(($to->getTimestamp() - $from->getTimestamp()) / 60));
    }

    /**
     * The record an employee is working on right now:
     * 1. an open record (checked in, not checked out) from the last 18 hours,
     *    even after midnight;
     * 2. otherwise the record of the current shift date, if any.
     */
    public static function currentFor(int $employeeId): ?self
    {
        $open = static::where('employee_id', $employeeId)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->where('check_in', '>=', Carbon::now()->subHours(18))
            ->latest('check_in')
            ->first();

        if ($open) {
            return $open;
        }

        return static::where('employee_id', $employeeId)
            ->whereDate('attendance_date', self::shiftDateFor()->toDateString())
            ->first();
    }

    /**
     * check_in / check_out are TIME columns, so the date is lost. For forms
     * and lists, rebuild full date-times from attendance_date; a check-out
     * earlier than the check-in belongs to the next day (night shift).
     */
    public function checkInAt(): ?Carbon
    {
        if (!$this->check_in || !$this->attendance_date) {
            return null;
        }

        return Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $this->check_in->format('H:i:s'));
    }

    public function checkOutAt(): ?Carbon
    {
        if (!$this->check_out || !$this->attendance_date) {
            return null;
        }

        $out = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $this->check_out->format('H:i:s'));
        $in = $this->checkInAt();

        return ($in && $out->lte($in)) ? $out->addDay() : $out;
    }
}
