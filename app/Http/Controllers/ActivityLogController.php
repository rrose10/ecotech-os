<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $activityLogs = ActivityLog::with('user')
            ->when($search, function ($query) use ($search) {
                $query->where('module', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20);

        return view('activity-logs.index', compact(
            'activityLogs',
            'search'
        ));
    }
}