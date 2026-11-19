<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveBalance;
use App\Models\MotivationalQuote;
use App\Models\Notice;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;


        $todayAttendance = null;
        $leaveBalances = collect();
        $attendanceSummary = null;
        $lastMonth = null;


        if ($employee) {


            // Today's Attendance

            // (supports overnight shifts: finds the open record after midnight)
            $todayAttendance = Attendance::currentFor($employee->id);



            // Leave Balance

            $leaveBalances = LeaveBalance::with('leaveType')
                ->where('employee_id', $employee->id)
                ->where('year', now()->year)
                ->get();

            // This month's attendance (shift date inside the current month)
            $monthly = Attendance::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString(),
                ])
                ->selectRaw("SUM(status = 'Present') as present_days")
                ->selectRaw("SUM(status = 'Late') as late_days")
                ->selectRaw("SUM(status = 'Absent') as absent_days")
                ->first();

            $attendanceSummary = (object) [
                'present_days' => (int) ($monthly->present_days ?? 0),
                'late_days'    => (int) ($monthly->late_days ?? 0),
                'absent_days'  => (int) ($monthly->absent_days ?? 0),
            ];


            // Last month's report
            $prev = now()->subMonthNoOverflow();

            $last = Attendance::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [
                    $prev->copy()->startOfMonth()->toDateString(),
                    $prev->copy()->endOfMonth()->toDateString(),
                ])
                ->selectRaw("SUM(status = 'Present') as present_days")
                ->selectRaw("SUM(status = 'Late') as late_days")
                ->selectRaw("SUM(status = 'Absent') as absent_days")
                ->selectRaw("COALESCE(SUM(working_minutes), 0) as work_minutes")
                ->selectRaw("COALESCE(SUM(late_minutes), 0) as late_minutes")
                ->first();

            $lastMonth = (object) [
                'label'        => $prev->format('F Y'),
                'present_days' => (int) ($last->present_days ?? 0),
                'late_days'    => (int) ($last->late_days ?? 0),
                'absent_days'  => (int) ($last->absent_days ?? 0),
                'work_hours'   => round(((int) ($last->work_minutes ?? 0)) / 60, 1),
                'late_minutes' => (int) ($last->late_minutes ?? 0),
            ];

        }



        // Latest Notices

        $latestNotices = Notice::where('is_published', true)
            ->whereDate('publish_date', '<=', today())
            ->where(function ($query) {

                $query->whereNull('expiry_date')
                      ->orWhereDate('expiry_date', '>=', today());

            })
            ->latest('publish_date')
            ->take(5)
            ->get();



        /*
        |--------------------------------------------------------------------------
        | Daily Motivation
        |--------------------------------------------------------------------------
        */

        // A different quote for every person, changing every hour.
        $dailyQuote = MotivationalQuote::forUser(Auth::id());



        return view(
            'portal.dashboard.index',
            compact(
                'employee',
                'todayAttendance',
                'leaveBalances',
                'latestNotices',
                'attendanceSummary',
                'lastMonth',
                'dailyQuote'
            )
        );

    }
}