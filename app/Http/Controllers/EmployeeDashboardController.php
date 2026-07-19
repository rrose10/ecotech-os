<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardController extends Controller
{

    public function index()
    {

        $employee = Auth::user()->employee;


        if (!$employee) {

            abort(403, 'Employee profile not found.');

        }




        $today = Carbon::today();



        $attendance = Attendance::where('employee_id', $employee->id)

            ->where('attendance_date', $today)

            ->first();





        return view('employee.dashboard', compact(

            'employee',

            'attendance'

        ));

    }

}