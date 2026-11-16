<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::latest()->get();

        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:departments',
        ]);

        $department = Department::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'status' => true,
        ]);

        ActivityLogger::log(
            'Department',
            'Created',
            'Created department: ' . $department->name,
            null,
            $department->toArray()
        );

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department Added Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        return redirect()->route('departments.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:departments,code,' . $department->id,
        ]);

        // Old data for Activity Log
        $oldData = $department->toArray();

        $department->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
        ]);

        ActivityLogger::log(
            'Department',
            'Updated',
            'Updated department: ' . $department->name,
            $oldData,
            $department->fresh()->toArray()
        );

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        ActivityLogger::log(
            'Department',
            'Deleted',
            'Deleted department: ' . $department->name,
            $department->toArray(),
            null
        );

        $department->delete();

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department Deleted Successfully');
    }
}