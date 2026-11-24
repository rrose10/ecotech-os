<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Support\OfficeNetwork;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Night shift: 7:00 PM -> 6:00 AM (next day), Asia/Dhaka.
 * attendance_date is always the date the shift STARTED on.
 */
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

        $todayAttendance = Attendance::currentFor($employee->id);

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
        if ($blocked = $this->blockedOutsideOffice($request)) {
            return $blocked;
        }

        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->with('error', 'Team profile not found.');
        }

        try {

            DB::transaction(function () use ($employee) {

                $existing = Attendance::currentFor($employee->id);

                if ($existing) {
                    throw new \RuntimeException(
                        $existing->check_out
                            ? 'Your attendance for this shift is already completed.'
                            : 'You have already checked in for this shift.'
                    );
                }

                $now        = Carbon::now();
                $shiftDate  = Attendance::shiftDateFor($now);
                $shiftStart = Attendance::shiftStartFor($shiftDate);

                $lateMinutes = Attendance::lateMinutes($shiftStart, $now);

                Attendance::create([
                    'employee_id'     => $employee->id,
                    'attendance_date' => $shiftDate->toDateString(),
                    'check_in'        => $now,
                    'break_start'     => null,
                    'break_end'       => null,
                    'check_out'       => null,
                    'working_minutes' => 0,
                    'break_minutes'   => 0,
                    'late_minutes'    => $lateMinutes,
                    'status'          => $lateMinutes > 0 ? 'Late' : 'Present',
                    'shift'           => 'Night',
                    'remarks'         => null,
                ]);
            });

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            // unique (employee_id, attendance_date): double click / parallel request
            return back()->with('error', 'You have already checked in for this shift.');
        }

        return redirect()
            ->route('portal.dashboard')
            ->with('success', 'Checked in successfully.');
    }

    public function startBreak(Request $request)
    {
        if ($blocked = $this->blockedOutsideOffice($request)) {
            return $blocked;
        }

        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->with('error', 'Team profile not found.');
        }

        $attendance = Attendance::currentFor($employee->id);

        if (!$attendance) {
            return back()->with('error', 'Please check in first.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'You have already checked out.');
        }

        if ($attendance->break_start) {
            return back()->with('error', 'Dinner break already started.');
        }

        $attendance->update([
            'break_start' => Carbon::now(),
        ]);

        return back()->with('success', 'Dinner break started.');
    }

    public function endBreak(Request $request)
    {
        if ($blocked = $this->blockedOutsideOffice($request)) {
            return $blocked;
        }

        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->with('error', 'Team profile not found.');
        }

        $attendance = Attendance::currentFor($employee->id);

        if (!$attendance) {
            return back()->with('error', 'Attendance not found.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'You have already checked out.');
        }

        if (!$attendance->break_start) {
            return back()->with('error', 'Dinner break has not started.');
        }

        if ($attendance->break_end) {
            return back()->with('error', 'Dinner break already ended.');
        }

        $breakEnd = Carbon::now();

        $attendance->update([
            'break_end'     => $breakEnd,
            'break_minutes' => Attendance::minutesBetween($attendance->break_start, $breakEnd),
        ]);

        return back()->with('success', 'Dinner break ended.');
    }

    public function checkOut(Request $request)
    {
        if ($blocked = $this->blockedOutsideOffice($request)) {
            return $blocked;
        }

        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->with('error', 'Team profile not found.');
        }

        $attendance = Attendance::currentFor($employee->id);

        if (!$attendance || !$attendance->check_in) {
            return back()->with('error', 'Please check in first.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'You have already checked out.');
        }

        if ($attendance->break_start && !$attendance->break_end) {
            return back()->with('error', 'Please end your dinner break before checking out.');
        }

        $now = Carbon::now();

        $totalMinutes   = Attendance::minutesBetween($attendance->checkInAt(), $now);
        $breakMinutes   = (int) ($attendance->break_minutes ?? 0);
        $workingMinutes = max(0, $totalMinutes - $breakMinutes);

        $attendance->update([
            'check_out'       => $now,
            'working_minutes' => $workingMinutes,
        ]);

        return redirect()
            ->route('portal.dashboard')
            ->with('success', 'Checked out successfully.');
    }

    /** Returns a redirect when an office-network rule is set and this request comes from elsewhere. */
    private function blockedOutsideOffice(Request $request)
    {
        if (OfficeNetwork::allowed($request->ip())) {
            return null;
        }

        return back()->with('error', 'Check-in and check-out are allowed only from the office network. Please connect to the office Wi-Fi.');
    }
}
