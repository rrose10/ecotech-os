<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $leaveRequests = LeaveRequest::with(['employee', 'leaveType', 'approver'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('leaveType', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('leave-requests.index', compact('leaveRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::where('status', true)
            ->orderBy('name')
            ->get();

        $leaveTypes = LeaveType::where('status', true)
            ->orderBy('name')
            ->get();

        return view('leave-requests.create', compact('employees', 'leaveTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date|after_or_equal:from_date',
            'reason'        => 'nullable|string|max:1000',
        ]);

        $validated['total_days'] = Carbon::parse($validated['from_date'])
            ->diffInDays(Carbon::parse($validated['to_date'])) + 1;

        $validated['status'] = 'Pending';

        LeaveRequest::create($validated);

        return redirect()
            ->route('leave-requests.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        return redirect()->route('leave-requests.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        $employees = Employee::where('status', true)
            ->orderBy('name')
            ->get();

        $leaveTypes = LeaveType::where('status', true)
            ->orderBy('name')
            ->get();

        return view('leave-requests.edit', compact(
            'leaveRequest',
            'employees',
            'leaveTypes'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date|after_or_equal:from_date',
            'reason'        => 'nullable|string|max:1000',
            'status'        => 'required|in:Pending,Approved,Rejected',
        ]);

        $validated['total_days'] = Carbon::parse($validated['from_date'])
            ->diffInDays(Carbon::parse($validated['to_date'])) + 1;

        if ($validated['status'] !== 'Pending') {
            $validated['approved_by'] = auth()->id();
            $validated['approved_at'] = now();
        } else {
            $validated['approved_by'] = null;
            $validated['approved_at'] = null;
        }

        $leaveRequest->update($validated);

        return redirect()
            ->route('leave-requests.index')
            ->with('success', 'Leave request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        $leaveRequest->delete();

        return redirect()
            ->route('leave-requests.index')
            ->with('success', 'Leave request deleted successfully.');
    }
}