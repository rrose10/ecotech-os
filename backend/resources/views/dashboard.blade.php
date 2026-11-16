@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

<div class="space-y-8">

    @include('partials.celebrations')

    <!-- Stats Cards -->
<!-- Finance Summary -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

    <div class="rounded-xl bg-white shadow-lg p-6 border-l-4 border-red-500">
        <p class="text-gray-500 text-sm">Today's Expense</p>

        <h3 class="mt-2 text-3xl font-bold text-red-600">
            ৳{{ number_format($todayExpense, 2) }}
        </h3>
    </div>

    <div class="rounded-xl bg-white shadow-lg p-6 border-l-4 border-orange-500">
        <p class="text-gray-500 text-sm">This Month Expense</p>

        <h3 class="mt-2 text-3xl font-bold text-orange-600">
            ৳{{ number_format($thisMonthExpense, 2) }}
        </h3>
    </div>

    <div class="rounded-xl bg-white shadow-lg p-6 border-l-4 border-green-500">
        <p class="text-gray-500 text-sm">Today's Income</p>

        <div class="mt-3 space-y-1">
            <p class="text-2xl font-bold text-green-600">
                ৳{{ number_format($todayIncomeBDT, 2) }}
            </p>

            <p class="text-xl font-semibold text-emerald-600">
                ${{ number_format($todayIncomeUSD, 2) }}
            </p>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-lg p-6 border-l-4 border-blue-500">
        <p class="text-gray-500 text-sm">This Month Income</p>

        <div class="mt-3 space-y-1">
            <p class="text-2xl font-bold text-blue-600">
                ৳{{ number_format($thisMonthIncomeBDT, 2) }}
            </p>

            <p class="text-xl font-semibold text-indigo-600">
                ${{ number_format($thisMonthIncomeUSD, 2) }}
            </p>
        </div>
    </div>

</div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">

        <div class="rounded-xl bg-blue-600 text-white shadow-lg p-6">
            <h3 class="text-lg font-semibold">👥 Team</h3>
            <p class="mt-4 text-5xl font-bold">{{ $employees }}</p>
        </div>

        <div class="rounded-xl bg-green-600 text-white shadow-lg p-6">
            <h3 class="text-lg font-semibold">🏢 Departments</h3>
            <p class="mt-4 text-5xl font-bold">{{ $departments }}</p>
        </div>

        <div class="rounded-xl bg-emerald-600 text-white shadow-lg p-6">
            <h3 class="text-lg font-semibold">🟢 Present Today</h3>
            <p class="mt-4 text-5xl font-bold">{{ $presentToday }}</p>
        </div>

        <div class="rounded-xl bg-red-600 text-white shadow-lg p-6">
            <h3 class="text-lg font-semibold">🔴 Absent Today</h3>
            <p class="mt-4 text-5xl font-bold">{{ $absentToday }}</p>
        </div>

        <div class="rounded-xl bg-amber-500 text-white shadow-lg p-6">
            <h3 class="text-lg font-semibold">📅 Pending Leaves</h3>
            <p class="mt-4 text-5xl font-bold">{{ $pendingLeaves }}</p>
        </div>

    </div>

    <!-- Row 1 -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- Recent Team Members -->
        <div class="rounded-xl bg-white shadow-lg">

            <div class="border-b px-6 py-4">
                <h2 class="text-xl font-bold">
                    👥 Recent Team Members
                </h2>
            </div>

            <div class="divide-y">

                @forelse($recentEmployees as $employee)

                    <div class="flex items-center justify-between px-6 py-4">

                        <div>

                            <p class="font-semibold text-gray-800">
                                {{ $employee->name }}
                            </p>

                            <p class="text-sm text-gray-500">
                                {{ $employee->department->name ?? 'No Department' }}
                            </p>

                        </div>

                        <div class="text-sm text-gray-500">
                            {{ $employee->joining_date?->format('d M Y') }}
                        </div>

                    </div>

                @empty

                    <div class="p-6 text-center text-gray-500">
                        No team members found.
                    </div>

                @endforelse

            </div>

        </div>

        <!-- Pending Leave -->
        <div class="rounded-xl bg-white shadow-lg">

            <div class="border-b px-6 py-4">
                <h2 class="text-xl font-bold">
                    📝 Pending Leave Requests
                </h2>
            </div>

            <div class="divide-y">

                @forelse($recentLeaves as $leave)

                    <div class="flex items-center justify-between px-6 py-4">

                        <div>

                            <p class="font-semibold">
                                {{ $leave->employee->name }}
                            </p>

                            <p class="text-sm text-gray-500">
                                {{ $leave->leaveType->name }}
                            </p>

                        </div>

                        <span class="rounded-full bg-yellow-100 px-3 py-1 text-sm text-yellow-700">
                            Pending
                        </span>

                    </div>

                @empty

                    <div class="p-6 text-center text-gray-500">
                        No pending leave requests.
                    </div>

                @endforelse

            </div>

        </div>

    </div>    @include('partials.finance-chart')

    <!-- Row 2 -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- Upcoming Birthdays -->
        <div class="rounded-xl bg-white shadow-lg">

            <div class="border-b px-6 py-4">
                <h2 class="text-xl font-bold">
                    🎂 Upcoming Birthdays
                </h2>
            </div>

            <div class="divide-y">

                @forelse($upcomingBirthdays as $employee)

                    <div class="flex items-center justify-between px-6 py-4">

                        <div>

                            <p class="font-semibold text-gray-800">
                                {{ $employee->name }}
                            </p>

                            <p class="text-sm text-gray-500">
                                {{ $employee->department->name ?? 'No Department' }}
                            </p>

                        </div>

                        <span class="text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($employee->date_of_birth)->format('d M') }}
                        </span>

                    </div>

                @empty

                    <div class="p-6 text-center text-gray-500">
                        No upcoming birthdays.
                    </div>

                @endforelse

            </div>

        </div>

        <!-- Quick Actions -->
        <div class="rounded-xl bg-white shadow-lg">

            <div class="border-b px-6 py-4">
                <h2 class="text-xl font-bold">
                    ⚡ Quick Actions
                </h2>
            </div>

            <div class="grid grid-cols-2 gap-4 p-6">

                <a href="{{ route('employees.create') }}"
                    class="rounded-lg bg-blue-600 p-4 text-center font-semibold text-white hover:bg-blue-700 transition">
                    👥<br>
                    Add Team Member
                </a>

                <a href="{{ route('departments.create') }}"
                    class="rounded-lg bg-green-600 p-4 text-center font-semibold text-white hover:bg-green-700 transition">
                    🏢<br>
                    Add Department
                </a>

                <a href="{{ route('attendances.create') }}"
                    class="rounded-lg bg-purple-600 p-4 text-center font-semibold text-white hover:bg-purple-700 transition">
                    🕒<br>
                    Attendance
                </a>

                <a href="{{ route('leaves.create') }}"
                    class="rounded-lg bg-amber-500 p-4 text-center font-semibold text-white hover:bg-amber-600 transition">
                    📝<br>
                    Apply Leave
                </a>

            </div>

        </div>

    </div>

    <!-- Welcome -->
    <div class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-700 text-white shadow-lg p-8">

        <h2 class="text-3xl font-bold">
            Welcome to EcoTech OS 👋
        </h2>

        <p class="mt-3 text-blue-100 max-w-3xl">
            Manage your team, departments, attendance and leave requests
            from one centralized dashboard. More powerful modules like Payroll,
            Expenses, Income, Assets, Inventory and Reports will be integrated
            soon.
        </p>

    </div>

</div>

@endsection