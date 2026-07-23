<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->search;

        $leaves = Leave::with(['employee', 'leaveType'])
            ->when($search, function ($query) use ($search) {

                $query->whereHas('employee', function ($q) use ($search) {

                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");

                });

            })
            ->latest()
            ->paginate(10);

        return view('leaves.index', compact(
            'leaves',
            'search'
        ));
    }

    public function create()
    {
        $employees = Employee::where('status', true)
            ->orderBy('name')
            ->get();

        $leaveTypes = LeaveType::where('status', true)
            ->orderBy('name')
            ->get();

        return view('leaves.create', compact(
            'employees',
            'leaveTypes'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([

            'employee_id' => 'required|exists:employees,id',

            'leave_type_id' => 'required|exists:leave_types,id',

            'from_date' => 'required|date',

            'to_date' => 'required|date|after_or_equal:from_date',

            'reason' => 'nullable|string',

        ]);

        $totalDays = Carbon::parse($request->from_date)
            ->diffInDays(
                Carbon::parse($request->to_date)
            ) + 1;

        $leave = Leave::create([

            'employee_id' => $request->employee_id,

            'leave_type_id' => $request->leave_type_id,

            'from_date' => $request->from_date,

            'to_date' => $request->to_date,

            'total_days' => $totalDays,

            'reason' => $request->reason,

            'status' => 'Pending',

        ]);

        $leave->load([
            'employee',
            'leaveType'
        ]);

        ActivityLogger::log(
            'Leave',
            'Created',
            'Created leave application for: ' . $leave->employee->name,
            null,
            $leave->toArray()
        );

        return redirect()
            ->route('leaves.index')
            ->with(
                'success',
                'Leave Application Submitted Successfully.'
            );
    }

    public function show(Leave $leave)
    {
        $leave->load([
            'employee',
            'leaveType'
        ]);

        return view(
            'leaves.show',
            compact('leave')
        );
    }

    public function edit(Leave $leave)
    {
        $employees = Employee::where('status', true)
            ->orderBy('name')
            ->get();

        $leaveTypes = LeaveType::where('status', true)
            ->orderBy('name')
            ->get();

        return view(
            'leaves.edit',
            compact(
                'leave',
                'employees',
                'leaveTypes'
            )
        );
    }
       public function update(Request $request, Leave $leave)
    {
        $request->validate([

            'employee_id' => 'required|exists:employees,id',

            'leave_type_id' => 'required|exists:leave_types,id',

            'from_date' => 'required|date',

            'to_date' => 'required|date|after_or_equal:from_date',

            'reason' => 'nullable|string',

            'status' => 'required|in:Pending,Approved,Rejected',

        ]);

        // Old data for Activity Log
        $oldData = $leave->toArray();

        $totalDays = Carbon::parse($request->from_date)
            ->diffInDays(
                Carbon::parse($request->to_date)
            ) + 1;

        $leave->update([

            'employee_id' => $request->employee_id,

            'leave_type_id' => $request->leave_type_id,

            'from_date' => $request->from_date,

            'to_date' => $request->to_date,

            'total_days' => $totalDays,

            'reason' => $request->reason,

            'status' => $request->status,

            'approved_by' => $request->status == 'Approved'
                ? Auth::id()
                : null,

            'approved_at' => $request->status == 'Approved'
                ? now()
                : null,

        ]);

        $leave->load([
            'employee',
            'leaveType'
        ]);

        ActivityLogger::log(
            'Leave',
            'Updated',
            'Updated leave application for: ' . $leave->employee->name,
            $oldData,
            $leave->fresh()->toArray()
        );

        return redirect()
            ->route('leaves.index')
            ->with(
                'success',
                'Leave Updated Successfully.'
            );
    }

    public function approve(Leave $leave)
    {
        $leave->load([
            'employee',
            'leaveType'
        ]);

        $oldData = $leave->toArray();

        $leave->update([

            'status' => 'Approved',

            'approved_by' => Auth::id(),

            'approved_at' => now(),

        ]);

        $balance = LeaveBalance::firstOrCreate(

            [

                'employee_id' => $leave->employee_id,

                'leave_type_id' => $leave->leave_type_id,

                'year' => now()->year,

            ],

            [

                'total_days' => $leave->leaveType->days_per_year,

                'used_days' => 0,

                'remaining_days' => $leave->leaveType->days_per_year,

            ]

        );

        $balance->increment(
            'used_days',
            $leave->total_days
        );

        $balance->decrement(
            'remaining_days',
            $leave->total_days
        );

        ActivityLogger::log(
            'Leave',
            'Approved',
            'Approved leave for: ' . $leave->employee->name,
            $oldData,
            $leave->fresh()->toArray()
        );

        return redirect()
            ->route('leaves.index')
            ->with(
                'success',
                'Leave Approved Successfully.'
            );
    } 
        public function reject(Request $request, Leave $leave)
    {
        $oldData = $leave->toArray();

        $leave->load([
            'employee',
            'leaveType'
        ]);

        $leave->update([

            'status' => 'Rejected',

            'approved_by' => Auth::id(),

            'approved_at' => now(),

            'remarks' => $request->remarks,

        ]);

        ActivityLogger::log(
            'Leave',
            'Rejected',
            'Rejected leave for: ' . $leave->employee->name,
            $oldData,
            $leave->fresh()->toArray()
        );

        return redirect()
            ->route('leaves.index')
            ->with(
                'success',
                'Leave Rejected Successfully.'
            );
    }

    public function destroy(Leave $leave)
    {
        $leave->load([
            'employee',
            'leaveType'
        ]);

        ActivityLogger::log(
            'Leave',
            'Deleted',
            'Deleted leave application for: ' . $leave->employee->name,
            $leave->toArray(),
            null
        );

        $leave->delete();

        return redirect()
            ->route('leaves.index')
            ->with(
                'success',
                'Leave Deleted Successfully.'
            );
    }

}