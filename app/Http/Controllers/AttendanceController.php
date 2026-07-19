<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
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





        Attendance::create([

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

        $attendance->load('employee');


        return view(
            'attendances.show',
            compact('attendance')
        );

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





        $attendance->update([


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

        $attendance->delete();


        return redirect()

            ->route('attendances.index')

            ->with(
                'success',
                'Attendance Deleted Successfully.'
            );

    }


}