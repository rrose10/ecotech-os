<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Notice;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;

        $todayAttendance = null;

        if ($employee) {

            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', today())
                ->first();

        }

        $latestNotices = Notice::where('is_published', true)
            ->whereDate('publish_date', '<=', today())
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', today());
            })
            ->latest('publish_date')
            ->take(5)
            ->get();

        return view(
            'portal.dashboard.index',
            compact(
                'employee',
                'todayAttendance',
                'latestNotices'
            )
        );
    }
}