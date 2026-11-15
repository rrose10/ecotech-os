<?php

namespace Tests\Unit;

use App\Models\Attendance;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AttendanceShiftTest extends TestCase
{
    public function test_shift_date_for_night_shift(): void
    {
        $cases = [
            '2026-11-15 19:05' => '2026-11-15',
            '2026-11-15 23:59' => '2026-11-15',
            '2026-11-16 00:30' => '2026-11-15',
            '2026-11-16 05:59' => '2026-11-15',
            '2026-11-16 06:00' => '2026-11-16',
            '2026-11-15 17:00' => '2026-11-15',
        ];

        foreach ($cases as $moment => $expected) {
            $this->assertSame(
                $expected,
                Attendance::shiftDateFor(Carbon::parse($moment))->toDateString(),
                $moment
            );
        }
    }

    public function test_minutes_are_whole_numbers_and_never_negative(): void
    {
        $a = Carbon::parse('2026-11-15 20:39:51');

        $this->assertSame(0, Attendance::minutesBetween($a, Carbon::parse('2026-11-15 20:40:04')));
        $this->assertSame(100, Attendance::minutesBetween(Carbon::parse('2026-11-15 19:00'), $a));
        $this->assertSame(0, Attendance::minutesBetween($a, Carbon::parse('2026-11-15 19:00')));
    }

    public function test_shift_start_is_seven_pm_of_shift_date(): void
    {
        $start = Attendance::shiftStartFor(Carbon::parse('2026-11-15'));

        $this->assertSame('2026-11-15 19:00:00', $start->format('Y-m-d H:i:s'));
    }
}
