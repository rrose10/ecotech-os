<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $leaveTypes = LeaveType::when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return view('leave-types.index', compact(
            'leaveTypes',
            'search'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('leave-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100|unique:leave_types,name',
            'days_per_year' => 'required|integer|min:0',
            'is_paid' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        $leaveType = LeaveType::create([
            'name' => $request->name,
            'days_per_year' => $request->days_per_year,
            'is_paid' => $request->is_paid,
            'status' => $request->status,
        ]);

        ActivityLogger::log(
            'Leave Type',
            'Created',
            'Created leave type: ' . $leaveType->name,
            null,
            $leaveType->toArray()
        );

        return redirect()
            ->route('leave-types.index')
            ->with('success', 'Leave Type Added Successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType)
    {
        return redirect()->route('leave-types.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveType $leaveType)
    {
        return view('leave-types.edit', compact('leaveType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $request->validate([
            'name' => 'required|max:100|unique:leave_types,name,' . $leaveType->id,
            'days_per_year' => 'required|integer|min:0',
            'is_paid' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        // Old data for Activity Log
        $oldData = $leaveType->toArray();

        $leaveType->update([
            'name' => $request->name,
            'days_per_year' => $request->days_per_year,
            'is_paid' => $request->is_paid,
            'status' => $request->status,
        ]);

        ActivityLogger::log(
            'Leave Type',
            'Updated',
            'Updated leave type: ' . $leaveType->name,
            $oldData,
            $leaveType->fresh()->toArray()
        );

        return redirect()
            ->route('leave-types.index')
            ->with('success', 'Leave Type Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->leaves()->exists()) {

            return redirect()
                ->route('leave-types.index')
                ->withErrors([
                    'error' => 'This Leave Type cannot be deleted because it is already in use.',
                ]);

        }

        ActivityLogger::log(
            'Leave Type',
            'Deleted',
            'Deleted leave type: ' . $leaveType->name,
            $leaveType->toArray(),
            null
        );

        $leaveType->delete();

        return redirect()
            ->route('leave-types.index')
            ->with('success', 'Leave Type Deleted Successfully.');
    }
}