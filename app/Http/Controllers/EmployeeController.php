<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $employees = Employee::with('department')
            ->when($search, function ($query) use ($search) {
                $query->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return view('employees.index', compact('employees', 'search'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $departments = Department::where('status', true)->get();

        return view('employees.create', compact('departments'));
    }

    /**
     * Store employee.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:employees,email',
            'designation' => 'required',
            'basic_salary' => 'required|numeric|min:0',
            'joining_date' => 'required|date',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $lastEmployee = Employee::latest()->first();

        if ($lastEmployee) {
            $number = (int) str_replace('EMP', '', $lastEmployee->employee_id);
            $employeeId = 'EMP' . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $employeeId = 'EMP0001';
        }

        $photo = null;

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo')->store('employees', 'public');
        }

        Employee::create([
            'department_id'     => $request->department_id,
            'employee_id'       => $employeeId,
            'name'              => $request->name,
            'email'             => $request->email,
            'phone'             => $request->phone,
            'designation'       => $request->designation,
            'basic_salary'      => $request->basic_salary,
            'joining_date'      => $request->joining_date,
            'date_of_birth'     => $request->date_of_birth,
            'gender'            => $request->gender,
            'blood_group'       => $request->blood_group,
            'emergency_contact' => $request->emergency_contact,
            'address'           => $request->address,
            'photo'             => $photo,
            'status'            => $request->status ?? true,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee Added Successfully.');
    }

    /**
     * Display employee.
     */
    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    /**
     * Show edit form.
     */
    public function edit(Employee $employee)
    {
        $departments = Department::where('status', true)->get();

        return view('employees.edit', compact('employee', 'departments'));
    }

    /**
     * Update employee.
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'department_id' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'designation' => 'required',
            'basic_salary' => 'required|numeric|min:0',
            'joining_date' => 'required|date',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $photo = $employee->photo;

        if ($request->hasFile('photo')) {

            if ($photo && Storage::disk('public')->exists($photo)) {
                Storage::disk('public')->delete($photo);
            }

            $photo = $request->file('photo')->store('employees', 'public');
        }

        $employee->update([
            'department_id'     => $request->department_id,
            'name'              => $request->name,
            'email'             => $request->email,
            'phone'             => $request->phone,
            'designation'       => $request->designation,
            'basic_salary'      => $request->basic_salary,
            'joining_date'      => $request->joining_date,
            'date_of_birth'     => $request->date_of_birth,
            'gender'            => $request->gender,
            'blood_group'       => $request->blood_group,
            'emergency_contact' => $request->emergency_contact,
            'address'           => $request->address,
            'photo'             => $photo,
            'status'            => $request->status,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee Updated Successfully.');
    }

    /**
     * Delete employee.
     */
    public function destroy(Employee $employee)
    {
        if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
            Storage::disk('public')->delete($employee->photo);
        }

        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee Deleted Successfully.');
    }
}