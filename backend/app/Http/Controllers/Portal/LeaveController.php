<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LeaveController extends Controller
{
    private function me()
    {
        $employee = Auth::user()->employee;
        abort_if(!$employee, 403, 'Employee profile not found.');

        return $employee;
    }

    public function index()
    {
        $employee = $this->me();

        $leaveTypes = LeaveType::where('status', true)->orderBy('name')->get();

        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->get()
            ->keyBy('leave_type_id');

        $leaves = Leave::with('leaveType')
            ->where('employee_id', $employee->id)
            ->latest()
            ->paginate(10);

        return view('portal.leaves.index', compact('employee', 'leaveTypes', 'balances', 'leaves'));
    }

    public function store(Request $request)
    {
        $employee = $this->me();

        $data = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        $type = LeaveType::where('status', true)->findOrFail($data['leave_type_id']);
        $from = Carbon::parse($data['from_date'])->startOfDay();
        $to = Carbon::parse($data['to_date'])->startOfDay();
        $days = $from->diffInDays($to) + 1;

        // No overlap with the employee's own pending/approved leaves
        $overlap = Leave::where('employee_id', $employee->id)
            ->whereIn('status', ['Pending', 'Approved'])
            ->whereDate('from_date', '<=', $to)
            ->whereDate('to_date', '>=', $from)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'from_date' => 'You already have a leave request covering some of these dates.',
            ]);
        }

        // Respect the yearly allowance for this leave type
        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $type->id)
            ->where('year', $from->year)
            ->first();

        $remaining = $balance ? $balance->remaining_days : $type->days_per_year;

        if ($days > $remaining) {
            throw ValidationException::withMessages([
                'to_date' => "Only {$remaining} day(s) of {$type->name} left this year; you asked for {$days}.",
            ]);
        }

        $leave = Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'from_date' => $from,
            'to_date' => $to,
            'total_days' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => 'Pending',
        ]);

        ActivityLogger::log('Leave', 'Created', 'Leave requested by ' . $employee->name, null, $leave->toArray());

        return redirect()->route('portal.leaves')->with('success', 'Leave request sent. You will see it here once it is reviewed.');
    }

    public function cancel(Leave $leave)
    {
        $employee = $this->me();

        abort_unless($leave->employee_id === $employee->id, 404);

        if ($leave->status !== 'Pending') {
            return redirect()->route('portal.leaves')->with('error', 'Only pending requests can be cancelled.');
        }

        ActivityLogger::log('Leave', 'Deleted', 'Leave request cancelled by ' . $employee->name, $leave->toArray(), null);
        $leave->delete();

        return redirect()->route('portal.leaves')->with('success', 'Leave request cancelled.');
    }
}
