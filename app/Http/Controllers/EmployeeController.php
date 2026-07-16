<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::with('department')->latest()->get();

        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::where('status', true)->get();

        return view('employees.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required',
            'name'          => 'required',
            'email'         => 'required|email|unique:employees,email',
            'designation'   => 'required',
            'joining_date'  => 'required',
        ]);

        $lastEmployee = Employee::latest()->first();

        if ($lastEmployee) {
            $number = (int) str_replace('EMP', '', $lastEmployee->employee_id);
            $employeeId = 'EMP' . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $employeeId = 'EMP0001';
        }

        Employee::create([
            'department_id' => $request->department_id,
            'employee_id'   => $employeeId,
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'designation'   => $request->designation,
            'joining_date'  => $request->joining_date,
            'salary'        => $request->salary ?? 0,
            'date_of_birth' => $request->date_of_birth,
            'address'       => $request->address,
            'status'        => $request->status ?? true,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee Added Successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $departments = Department::where('status', true)->get();

        return view('employees.edit', compact('employee', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'department_id' => 'required',
            'name'          => 'required',
            'email'         => 'required|email|unique:employees,email,' . $employee->id,
            'designation'   => 'required',
            'joining_date'  => 'required',
        ]);

        $employee->update([
            'department_id' => $request->department_id,
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'designation'   => $request->designation,
            'joining_date'  => $request->joining_date,
            'salary'        => $request->salary ?? 0,
            'date_of_birth' => $request->date_of_birth,
            'address'       => $request->address,
            'status'        => $request->status,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee Deleted Successfully.');
    }
}