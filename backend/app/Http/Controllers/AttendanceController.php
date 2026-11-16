<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display attendance list.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $attendances = Attendance::with('employee')
            ->when($search, function ($query) use ($search) {

                $query->whereHas('employee', function ($q) use ($search) {

                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");

                });

            })
            ->latest('attendance_date')
            ->paginate(10);

        return view('attendances.index', compact(
            'attendances',
            'search'
        ));
    }

    /**
     * Show attendance form.
     */
    public function create()
    {
        $employees = Employee::where('status', true)
            ->orderBy('name')
            ->get();

        return view('attendances.create', compact(
            'employees'
        ));
    }

    /**
     * Store attendance.
     */
    public function store(Request $request)
    {
        $request->validate([

            'employee_id' => 'required|exists:employees,id',

            'attendance_date' => 'required|date',

            'status' => 'required',

        ]);

        $attendance = Attendance::create([

            'employee_id' => $request->employee_id,

            'attendance_date' => $request->attendance_date,

            'check_in' => $request->check_in,

            'check_out' => $request->check_out,

            'working_minutes' => $request->working_minutes ?? 0,

            'late_minutes' => $request->late_minutes ?? 0,

            'shift' => 'Night',

            'status' => $request->status,

            'remarks' => $request->remarks,

        ]);

        $attendance->load('employee');

        ActivityLogger::log(
            'Attendance',
            'Created',
            'Created attendance for: ' . $attendance->employee->name,
            null,
            $attendance->toArray()
        );

        return redirect()
            ->route('attendances.index')
            ->with(
                'success',
                'Attendance Added Successfully.'
            );
    }

    /**
     * Show attendance.
     */
    public function show(Attendance $attendance)
    {
        return redirect()->route('attendances.edit', $attendance);
    }

    /**
     * Edit attendance.
     */
    public function edit(Attendance $attendance)
    {
        $employees = Employee::where('status', true)
            ->orderBy('name')
            ->get();

        return view(
            'attendances.edit',
            compact(
                'attendance',
                'employees'
            )
        );
    }

    /**
     * Update attendance.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([

            'employee_id' => 'required|exists:employees,id',

            'attendance_date' => 'required|date',

            'status' => 'required',

        ]);

        $oldData = $attendance->toArray();

        $in  = $request->filled('check_in') ? Carbon::parse($request->check_in) : null;
        $out = $request->filled('check_out') ? Carbon::parse($request->check_out) : null;

        $working = $request->working_minutes ?? 0;
        $late    = $request->late_minutes ?? 0;

        if ($in) {
            $shiftStart = Attendance::shiftStartFor(Attendance::shiftDateFor($in));
            $late = $in->gt($shiftStart) ? Attendance::minutesBetween($shiftStart, $in) : 0;
        }

        if ($in && $out && $out->gt($in)) {
            $working = max(0, Attendance::minutesBetween($in, $out) - (int) $attendance->break_minutes);
        }

        $attendance->update([

            'employee_id' => $request->employee_id,

            'attendance_date' => $request->attendance_date,

            'check_in' => $request->check_in,

            'check_out' => $request->check_out,

            'working_minutes' => $working,

            'late_minutes' => $late,

            'shift' => 'Night',

            'status' => $request->status,

            'remarks' => $request->remarks,

        ]);

        $attendance->load('employee');

        ActivityLogger::log(
            'Attendance',
            'Updated',
            'Updated attendance for: ' . $attendance->employee->name,
            $oldData,
            $attendance->fresh()->toArray()
        );

        return redirect()
            ->route('attendances.index')
            ->with(
                'success',
                'Attendance Updated Successfully.'
            );
    }

    /**
     * Delete attendance.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->load('employee');

        ActivityLogger::log(
            'Attendance',
            'Deleted',
            'Deleted attendance for: ' . $attendance->employee->name,
            $attendance->toArray(),
            null
        );

        $attendance->delete();

        return redirect()
            ->route('attendances.index')
            ->with(
                'success',
                'Attendance Deleted Successfully.'
            );
    }
}