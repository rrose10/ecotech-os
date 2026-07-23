<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return view('portal.attendance.index', [
                'employee' => null,
                'todayAttendance' => null,
                'attendances' => collect(),
            ]);
        }

            $todayAttendance = $employee->attendances()
                ->whereDate('attendance_date', Carbon::today())
                ->latest()
                ->first();

        $attendances = $employee->attendances()
            ->latest('attendance_date')
            ->paginate(10);

        return view('portal.attendance.index', compact(
            'employee',
            'todayAttendance',
            'attendances'
        ));
    }

    public function checkIn(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->with('error', 'Employee profile not found.');
        }

        $todayAttendance = $employee->attendances()
            ->whereDate('attendance_date', today())
            ->first();

        if ($todayAttendance) {
            return back()->with('error', 'You have already checked in today.');
        }

        $now = Carbon::now();

        $shiftStart = Carbon::today()->setTime(19, 0); // 7:00 PM

        $status = $now->gt($shiftStart) ? 'Late' : 'Present';

        $lateMinutes = $status === 'Late'
            ? $shiftStart->diffInMinutes($now)
            : 0;

        Attendance::create([
            'employee_id'     => $employee->id,
            'attendance_date' => today(),
            'check_in'        => $now,
            'break_start'     => null,
            'break_end'       => null,
            'check_out'       => null,
            'working_minutes' => 0,
            'break_minutes'   => 0,
            'late_minutes'    => $lateMinutes,
            'status'          => $status,
            'shift'           => 'Night',
            'remarks'         => null,
        ]);

        return redirect()
            ->route('portal.dashboard')
            ->with('success', 'Checked in successfully.');
    }

    public function startBreak(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->with('error', 'Employee profile not found.');
        }

        $attendance = $employee->attendances()
            ->whereDate('attendance_date', today())
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Please check in first.');
        }

        if ($attendance->break_start) {
            return back()->with('error', 'Dinner break already started.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'You have already checked out.');
        }

        $attendance->update([
            'break_start' => now(),
        ]);

        return back()->with('success', 'Dinner break started.');
    }

    public function endBreak(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->with('error', 'Employee profile not found.');
        }

        $attendance = $employee->attendances()
            ->whereDate('attendance_date', today())
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Attendance not found.');
        }

        if (!$attendance->break_start) {
            return back()->with('error', 'Dinner break has not started.');
        }

        if ($attendance->break_end) {
            return back()->with('error', 'Dinner break already ended.');
        }

        $breakEnd = now();

        $attendance->update([
            'break_end'     => $breakEnd,
            'break_minutes' => round(
                $attendance->break_start->diffInMinutes($breakEnd)
            ),
        ]);

        return back()->with('success', 'Dinner break ended.');
    }

    public function checkOut(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->with('error', 'Employee profile not found.');
        }

        $attendance = $employee->attendances()
            ->whereDate('attendance_date', today())
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Please check in first.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'You have already checked out.');
        }

        $now = Carbon::now();

        $totalMinutes = $attendance->check_in->diffInMinutes($now);

        $breakMinutes = round($attendance->break_minutes ?? 0);

        $workingMinutes = max(
            0,
            $totalMinutes - $breakMinutes
        );

        $attendance->update([
            'check_out'       => $now,
            'working_minutes' => $workingMinutes,
        ]);

        return redirect()
            ->route('portal.dashboard')
            ->with('success', 'Checked out successfully.');
    }
}