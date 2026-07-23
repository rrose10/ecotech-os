<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{

    /**
     * Display leave balances
     */
    public function index(Request $request)
    {
        $search = $request->search;


        $employees = Employee::with([
            'leaveBalances.leaveType'
        ])
        ->when($search, function ($query) use ($search) {

            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");

        })
        ->orderBy('name')
        ->paginate(10);



        return view('leave-balances.index', compact(
            'employees',
            'search'
        ));
    }



    /**
     * Employee leave balance details
     */
    public function show(Employee $employee)
    {

        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->get();



        return view('leave-balances.show', compact(
            'employee',
            'balances'
        ));
    }

}