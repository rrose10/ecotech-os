@extends('layouts.portal')

@section('title', 'Attendance')

@section('page_title', 'Attendance')

@section('content')

<div class="space-y-6">

    <!-- Today's Attendance -->
    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-xl font-bold text-slate-900">
                Today's Attendance
            </h2>
        </div>

        @php
            $status = strtolower($todayAttendance?->status ?? '');
        @endphp

        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-6">

            <div>
                <p class="text-sm text-slate-500">Status</p>

            @if($status === 'present')

                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700">
                    Present
                </span>

            @elseif($status === 'late')

                <span class="inline-flex rounded-full bg-orange-100 px-3 py-1 text-sm font-medium text-orange-700">
                    Late
                </span>

            @elseif($status === 'absent')

                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">
                    Absent
                </span>

            @elseif($status === 'leave')

                <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-700">
                    Leave
                </span>

            @else

                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">
                    No Record
                </span>

            @endif
            </div>

            <div>
                <p class="text-sm text-slate-500">Shift</p>

                <p class="mt-2 font-semibold">
                    {{ $todayAttendance->shift ?? '--' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Check In</p>

                <p class="mt-2 font-semibold">
                    {{ $todayAttendance?->check_in?->format('h:i A') ?? '--' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Check Out</p>

                <p class="mt-2 font-semibold">
                    {{ $todayAttendance?->check_out?->format('h:i A') ?? '--' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Working Hours</p>

                <p class="mt-2 font-semibold">
                    @if($todayAttendance)
                        {{ floor(($todayAttendance->working_minutes ?? 0) / 60) }}h
                        {{ ($todayAttendance->working_minutes ?? 0) % 60 }}m
                    @else
                        --
                    @endif
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Late</p>

                <p class="mt-2 font-semibold">
                    @if($todayAttendance && $todayAttendance->late_minutes)

                        {{ floor($todayAttendance->late_minutes / 60) }}h
                        {{ round($todayAttendance->late_minutes % 60) }}m

                    @else

                        --

                    @endif
                </p>
            </div>

        </div>

    </div>

    <!-- Attendance History -->

    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-xl font-bold text-slate-900">
                Attendance History
            </h2>
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-slate-50">

                    <tr>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Date
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Shift
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Status
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Check In
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Check Out
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Hours
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Late
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Remarks
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-200">

                    @forelse($attendances as $attendance)

                        <tr>

                            <td class="px-6 py-4">
                                {{ $attendance->attendance_date->format('d M Y') }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $attendance->shift }}
                            </td>

                            <td class="px-6 py-4">
                                {{ ucfirst($attendance->status) }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $attendance->check_in?->format('h:i A') ?? '--' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $attendance->check_out?->format('h:i A') ?? '--' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ floor(($attendance->working_minutes ?? 0)/60) }}h
                                {{ ($attendance->working_minutes ?? 0)%60 }}m
                            </td>

                            <td class="px-6 py-4">
                                @if($attendance->late_minutes)

                                    {{ floor($attendance->late_minutes / 60) }}h
                                    {{ round($attendance->late_minutes % 60) }}m

                                @else

                                    --

                                @endif
                            </td>

                            <td class="px-6 py-4">
                                {{ $attendance->remarks ?? '--' }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                                No attendance records found.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if(method_exists($attendances, 'links'))

            <div class="border-t border-slate-200 px-6 py-4">

                {{ $attendances->links() }}

            </div>

        @endif

    </div>

</div>

@endsection