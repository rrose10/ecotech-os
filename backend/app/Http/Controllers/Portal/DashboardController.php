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


        if ($employee) {


            // Today's Attendance

            // (supports overnight shifts: finds the open record after midnight)
            $todayAttendance = Attendance::currentFor($employee->id);



            // Leave Balance

            $leaveBalances = LeaveBalance::with('leaveType')
                ->where('employee_id', $employee->id)
                ->where('year', now()->year)
                ->get();

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
                'dailyQuote'
            )
        );

    }
}