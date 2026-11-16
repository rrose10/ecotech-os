@extends('layouts.portal')

@section('title', 'Team Dashboard')

@section('page_title', 'Dashboard')

@section('content')

<div class="space-y-6">

    @include('partials.celebrations')

    {{-- Welcome --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold text-slate-900">
                    Welcome Back, {{ $employee?->name ?? 'Team Member' }} 👋
                </h1>

                <p class="text-slate-500 mt-2">
                    Employee ID:
                    <strong>{{ $employee?->employee_id ?? '--' }}</strong>
                </p>

            </div>

            <div class="text-right">

                <div id="liveTime"
                     class="text-3xl font-bold text-indigo-600">
                </div>

                <div id="liveDate"
                     class="text-sm text-slate-500 mt-1">
                </div>

            </div>

        </div>

    </div>

    {{-- Daily Motivation --}}
@if($dailyQuote)

<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-blue-600 to-cyan-600 p-8 text-white shadow-xl">

    <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/10"></div>
    <div class="absolute -left-10 -bottom-10 h-32 w-32 rounded-full bg-white/10"></div>

    <div class="relative">

        <div class="flex items-center gap-4">

            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur">

                💡

            </div>

            <div>

                <h2 class="text-2xl font-bold">

                    Daily Motivation

                </h2>

                <p class="text-indigo-100">

                    A new quote appears every day.

                </p>

            </div>

        </div>

        <div class="mt-8">

            <blockquote class="text-2xl leading-10 italic font-medium">

                “{{ $dailyQuote->quote }}”

            </blockquote>

            @if($dailyQuote->author)

                <p class="mt-6 text-lg font-semibold text-indigo-100">

                    — {{ $dailyQuote->author }}

                </p>

            @endif

        </div>

    </div>

</div>

@endif

    {{-- Today's Attendance --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">

        <h2 class="text-xl font-bold mb-6">
            Today's Attendance
        </h2>

        <div class="grid lg:grid-cols-2 gap-8">

            {{-- Attendance Info --}}
            <div class="space-y-5">

                <div>
                    <p class="text-slate-500 text-sm">
                        Status
                    </p>

                    <p class="font-semibold text-lg">
                        {{ $todayAttendance?->status ?? 'Not Checked In' }}
                    </p>
                </div>

                <div>
                    <p class="text-slate-500 text-sm">
                        Shift
                    </p>

                    <p class="font-semibold">
                        {{ $todayAttendance?->shift ?? 'Night Shift' }}
                    </p>
                </div>

                <div>
                    <p class="text-slate-500 text-sm">
                        Check In
                    </p>

                    <p class="font-semibold">
                        {{ optional($todayAttendance?->check_in)->format('h:i A') ?? '--' }}
                    </p>
                </div>

                <div>
                    <p class="text-slate-500 text-sm">
                        Dinner Break Start
                    </p>

                    <p class="font-semibold">
                        {{ optional($todayAttendance?->break_start)->format('h:i A') ?? '--' }}
                    </p>
                </div>

                <div>
                    <p class="text-slate-500 text-sm">
                        Dinner Break End
                    </p>

                    <p class="font-semibold">
                        {{ optional($todayAttendance?->break_end)->format('h:i A') ?? '--' }}
                    </p>
                </div>

                <div>
                    <p class="text-slate-500 text-sm">
                        Check Out
                    </p>

                    <p class="font-semibold">
                        {{ optional($todayAttendance?->check_out)->format('h:i A') ?? '--' }}
                    </p>
                </div>

                <div>
                    <p class="text-slate-500 text-sm">
                        Break Duration
                    </p>

                    <p class="font-semibold">

                        @if($todayAttendance && $todayAttendance->break_minutes)

                            {{ floor($todayAttendance->break_minutes / 60) }}h
                            {{ $todayAttendance->break_minutes % 60 }}m

                        @else

                            --

                        @endif

                    </p>
                </div>

                <div>
                    <p class="text-slate-500 text-sm">
                        Net Working Time
                    </p>

                    <p class="font-semibold">

                        @if($todayAttendance && $todayAttendance->working_minutes)

                            {{ floor($todayAttendance->working_minutes / 60) }}h
                            {{ $todayAttendance->working_minutes % 60 }}m

                        @else

                            --

                        @endif

                    </p>
                </div>

            </div>

            {{-- Action Button --}}
            <div class="flex items-center justify-center">

                @if(!$todayAttendance)

                    <form action="{{ route('portal.attendance.checkin') }}" method="POST">
                        @csrf

                        <button
                            onclick="return confirm('Check In now?')"
                            class="px-10 py-4 rounded-xl bg-green-600 hover:bg-green-700 text-white font-semibold text-lg">

                            🟢 Check In

                        </button>

                    </form>

                @elseif(!$todayAttendance->break_start)

                    <form action="{{ route('portal.attendance.break.start') }}" method="POST">
                        @csrf

                        <button
                            onclick="return confirm('Start Dinner Break?')"
                            class="px-10 py-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-lg">

                            🍽 Start Dinner Break

                        </button>

                    </form>

                @elseif(!$todayAttendance->break_end)

                    <form action="{{ route('portal.attendance.break.end') }}" method="POST">
                        @csrf

                        <button
                            onclick="return confirm('End Dinner Break?')"
                            class="px-10 py-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-lg">

                            ▶ End Dinner Break

                        </button>

                    </form>

                @elseif(!$todayAttendance->check_out)

                    <form action="{{ route('portal.attendance.checkout') }}" method="POST">
                        @csrf

                        <button
                            onclick="return confirm('Check Out now?')"
                            class="px-10 py-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-lg">

                            🔴 Check Out

                        </button>

                    </form>

                @else

                    <div class="text-center">

                        <div class="text-green-600 text-6xl">
                            ✓
                        </div>

                        <p class="mt-3 font-semibold text-green-700 text-lg">
                            Attendance Completed
                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>

    {{-- Monthly Attendance Summary --}}

    <div class="grid md:grid-cols-3 gap-6">

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <p class="text-slate-500 text-sm">
                Present This Month
            </p>

            <h2 class="text-3xl font-bold text-green-600 mt-2">
                {{ $attendanceSummary?->present_days ?? 0 }}
                Days
            </h2>
        </div>


        <div class="bg-white rounded-2xl border border-slate-200 p-6">

            <p class="text-slate-500 text-sm">
                Late This Month
            </p>

            <h2 class="text-3xl font-bold text-orange-500 mt-2">
                {{ $attendanceSummary?->late_days ?? 0 }}
                Days
            </h2>

        </div>


        <div class="bg-white rounded-2xl border border-slate-200 p-6">

            <p class="text-slate-500 text-sm">
                Absent This Month
            </p>

            <h2 class="text-3xl font-bold text-red-600 mt-2">
                {{ $attendanceSummary?->absent_days ?? 0 }}
                Days
            </h2>

        </div>

    </div>

        {{-- Latest Notices --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">

        <div class="flex items-center justify-between mb-6">

            <div>

                <h2 class="text-xl font-bold text-slate-900">
                    📢 Team Notices
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Stay updated with the latest announcements.
                </p>

            </div>

            <a href="{{ route('portal.notices') }}"
               class="text-indigo-600 hover:text-indigo-700 font-semibold">

                View All →

            </a>

        </div>



        @forelse($latestNotices as $notice)

            <a href="{{ route('portal.notices.show', $notice) }}"
               class="block border border-slate-200 rounded-2xl p-5 mb-4 hover:border-indigo-300 hover:shadow-md transition duration-200">

                <div class="flex items-start justify-between gap-4">

                    <div class="flex-1">

                        <h3 class="text-lg font-semibold text-slate-900">
                            {{ $notice->title }}
                        </h3>

                        <p class="text-sm text-slate-500 mt-2">
                            {{ $notice->publish_date->format('d M Y') }}
                        </p>

                        <p class="mt-4 text-slate-600 leading-7">
                            {{ \Illuminate\Support\Str::limit(strip_tags($notice->content), 140) }}
                        </p>

                    </div>

                    @php
                        $badgeColor = match(strtolower($notice->type)) {
                            'urgent' => 'bg-red-100 text-red-700',
                            'important' => 'bg-amber-100 text-amber-700',
                            'holiday' => 'bg-green-100 text-green-700',
                            default => 'bg-indigo-100 text-indigo-700',
                        };
                    @endphp

                    <span class="px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $badgeColor }}">
                        {{ ucfirst($notice->type) }}
                    </span>

                </div>

            </a>

        @empty

            <div class="text-center py-12">

                <div class="text-5xl mb-3">
                    📭
                </div>

                <h3 class="text-lg font-semibold text-slate-700">
                    No Notices Found
                </h3>

                <p class="text-slate-500 mt-2">
                    There are no published notices at the moment.
                </p>

            </div>

        @endforelse

    </div>
    </div>

        {{-- Upcoming Birthdays --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">

        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-900">🎂 Upcoming Birthdays</h2>
            <p class="text-sm text-slate-500 mt-1">Celebrate with your teammates.</p>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($upcomingBirthdays as $person)
                <div class="flex items-center justify-between gap-4 py-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <x-avatar :name="$person->name" :photo="$person->photo" />
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-800">{{ $person->name }}</p>
                            <p class="truncate text-sm text-slate-500">{{ $person->department->name ?? 'No Department' }}</p>
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        @if($person->days_until === 0)
                            <span class="rounded-full bg-pink-100 px-3 py-1 text-sm font-semibold text-pink-700">Today 🎉</span>
                        @else
                            <p class="text-sm font-semibold text-slate-700">{{ $person->next_birthday->format('d M') }}</p>
                            <p class="text-xs text-slate-500">{{ $person->days_until === 1 ? 'Tomorrow' : 'in ' . $person->days_until . ' days' }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-500">No birthdays on record yet.</div>
            @endforelse
        </div>

    </div>

                {{-- Leave Balance --}}

        <div class="bg-white rounded-2xl border border-slate-200 p-6">

            <div class="mb-6">

                <h2 class="text-xl font-bold text-slate-900">
                    🏖 My Leave Balance
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Current year leave summary
                </p>

            </div>


            <div class="grid md:grid-cols-3 gap-6">


                @forelse($leaveBalances as $leave)


                    <div class="border border-slate-200 rounded-xl p-5">

                        <p class="text-slate-500 text-sm">
                            {{ $leave->leaveType->name }}
                        </p>


                        <h3 class="text-3xl font-bold text-indigo-600 mt-3">

                            {{ $leave->remaining_days }}

                            <span class="text-lg font-medium">
                                Days
                            </span>

                        </h3>


                        <p class="text-sm text-slate-500 mt-3">

                            Used:
                            {{ $leave->used_days }}

                            /
                            {{ $leave->total_days }}

                        </p>


                    </div>


                @empty


                    <div class="text-slate-500">
                        No leave balance available.
                    </div>


                @endforelse


            </div>

        </div>

<script>

function updateClock() {

    const now = new Date();

    const timeOptions = {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    };

    const dateOptions = {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    };

    document.getElementById('liveTime').textContent =
        now.toLocaleTimeString([], timeOptions);

    document.getElementById('liveDate').textContent =
        now.toLocaleDateString([], dateOptions);

}

updateClock();

setInterval(updateClock, 1000);

</script>

@endsection