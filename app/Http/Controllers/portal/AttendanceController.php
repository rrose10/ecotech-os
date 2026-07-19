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
            ->whereDate('attendance_date', today())
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
            'check_out'       => null,
            'working_minutes' => 0,
            'late_minutes'    => $lateMinutes,
            'status'          => $status,
            'shift'           => 'Night',
            'remarks'         => null,
        ]);

        return redirect()
            ->route('portal.dashboard')
            ->with('success', 'Checked in successfully.');
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

        $attendance->update([
            'check_out' => $now,
            'working_minutes' => $attendance->check_in->diffInMinutes($now),
        ]);

        return redirect()
            ->route('portal.dashboard')
            ->with('success', 'Checked out successfully.');
    }
}