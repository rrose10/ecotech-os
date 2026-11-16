<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
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

        return view('employees.index', compact(
            'employees',
            'search'
        ));
    }

    public function create()
    {
        $departments = Department::where('status', true)->get();

        return view('employees.create', compact(
            'departments'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:users,email|unique:employees,email',
            'password' => 'required|min:6|confirmed',
            'designation' => 'required',
            'basic_salary' => 'required|numeric|min:0',
            'joining_date' => 'required|date',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
        ]);

        $user->forceFill(['must_change_password' => true])->save();

        $lastEmployee = Employee::latest()->first();

        if ($lastEmployee) {

            $number = (int) str_replace(
                'EMP',
                '',
                $lastEmployee->employee_id
            );

            $employeeId = 'EMP' . str_pad(
                $number + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

        } else {

            $employeeId = 'EMP0001';

        }

        $photo = null;

        if ($request->hasFile('photo')) {

            $photo = $request->file('photo')
                ->store('employees', 'public');

        }

        $employee = Employee::create([
            'user_id' => $user->id,
            'department_id' => $request->department_id,
            'employee_id' => $employeeId,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'designation' => $request->designation,
            'basic_salary' => $request->basic_salary,
            'joining_date' => $request->joining_date,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'blood_group' => $request->blood_group,
            'emergency_contact' => $request->emergency_contact,
            'address' => $request->address,
            'photo' => $photo,
            'status' => $request->status ?? true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Leave Balance
        |--------------------------------------------------------------------------
        */

        $leaveTypes = LeaveType::where('status', true)->get();

        foreach ($leaveTypes as $leaveType) {

            LeaveBalance::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year' => now()->year,
                'total_days' => $leaveType->days_per_year,
                'used_days' => 0,
                'remaining_days' => $leaveType->days_per_year,
            ]);

        }

        ActivityLogger::log(
            'Employee',
            'Created',
            'Created employee: ' . $employee->name,
            null,
            $employee->toArray()
        );

        return redirect()
            ->route('employees.index')
            ->with(
                'success',
                'Employee Added Successfully.'
            );
    }
        public function show(Employee $employee)
    {
        $employee->load([
            'department',
            'attendances',
            'payrolls',
            'leaves.leaveType',
        ]);

        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->get();

        return view('employees.show', compact(
            'employee',
            'leaveBalances'
        ));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::where('status', true)
            ->get();

        return view('employees.edit', compact(
            'employee',
            'departments'
        ));
    }

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

        // Old data for Activity Log
        $oldData = $employee->toArray();

        $photo = $employee->photo;

        if ($request->hasFile('photo')) {

            if ($photo && Storage::disk('public')->exists($photo)) {
                Storage::disk('public')->delete($photo);
            }

            $photo = $request->file('photo')
                ->store('employees', 'public');
        }

        $employee->update([
            'department_id' => $request->department_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'designation' => $request->designation,
            'basic_salary' => $request->basic_salary,
            'joining_date' => $request->joining_date,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'blood_group' => $request->blood_group,
            'emergency_contact' => $request->emergency_contact,
            'address' => $request->address,
            'photo' => $photo,
            'status' => $request->status,
        ]);

        if ($employee->user) {

            $employee->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

        }

        ActivityLogger::log(
            'Employee',
            'Updated',
            'Updated employee: ' . $employee->name,
            $oldData,
            $employee->fresh()->toArray()
        );

        return redirect()
            ->route('employees.index')
            ->with(
                'success',
                'Employee Updated Successfully.'
            );
    }
        public function destroy(Employee $employee)
    {
        // Activity Log (Delete করার আগে data save)
        ActivityLogger::log(
            'Employee',
            'Deleted',
            'Deleted employee: ' . $employee->name,
            $employee->toArray(),
            null
        );

        if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
            Storage::disk('public')->delete($employee->photo);
        }

        if ($employee->user) {
            $employee->user->delete();
        }

        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with(
                'success',
                'Employee Deleted Successfully.'
            );
    }
}