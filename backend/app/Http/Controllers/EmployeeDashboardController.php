<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveBalance;
use App\Models\Payroll;
use App\Models\Notice;
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


        /*
        |--------------------------------------------------------------------------
        | Today's Attendance
        |--------------------------------------------------------------------------
        */

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->first();



        // This Month Attendance Summary

        $attendanceSummary = Attendance::where('employee_id', $employee->id)
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->selectRaw("
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_days,
                SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_days
            ")
            ->first();



        /*
        |--------------------------------------------------------------------------
        | Leave Balance
        |--------------------------------------------------------------------------
        */

        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->get();



        /*
        |--------------------------------------------------------------------------
        | Latest Payroll
        |--------------------------------------------------------------------------
        */

        $latestPayroll = Payroll::where('employee_id', $employee->id)
            ->latest()
            ->first();



        /*
        |--------------------------------------------------------------------------
        | Latest Notices
        |--------------------------------------------------------------------------
        */

        $latestNotices = Notice::where('status', true)
            ->latest()
            ->take(5)
            ->get();



        return view('portal.dashboard.index', compact(

            'employee',

            'todayAttendance',

            'attendanceSummary',

            'leaveBalances',

            'latestPayroll',

            'latestNotices'

        ));

    }
}