@extends('layouts.app')

@section('content')

<div class="p-8">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Employee Profile</h1>

        <div class="flex gap-2">
            <a href="{{ route('employees.index') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                Back
            </a>

            <a href="{{ route('employees.edit', $employee) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Edit
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-8">

        <div class="flex items-center gap-6 mb-8">

            @if($employee->photo)
                <img src="{{ asset('storage/'.$employee->photo) }}"
                     class="w-32 h-32 rounded-full object-cover">
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->name) }}&size=256"
                     class="w-32 h-32 rounded-full">
            @endif

            <div>
                <h2 class="text-3xl font-bold">{{ $employee->name }}</h2>
                <p class="text-gray-500">{{ $employee->employee_id }}</p>

                @if($employee->status)
                    <span class="inline-block mt-2 px-3 py-1 rounded-full bg-green-100 text-green-700">
                        Active
                    </span>
                @else
                    <span class="inline-block mt-2 px-3 py-1 rounded-full bg-red-100 text-red-700">
                        Inactive
                    </span>
                @endif
            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <p><strong>Department:</strong> {{ $employee->department->name ?? '-' }}</p>
                <p><strong>Designation:</strong> {{ $employee->designation }}</p>
                <p><strong>Email:</strong> {{ $employee->email }}</p>
                <p><strong>Phone:</strong> {{ $employee->phone ?: '-' }}</p>
                <p><strong>Salary:</strong> ৳{{ number_format($employee->salary, 2) }}</p>
            </div>

            <div>
                <p><strong>Joining Date:</strong> {{ optional($employee->joining_date)->format('d M Y') }}</p>
                <p><strong>Date of Birth:</strong> {{ optional($employee->date_of_birth)->format('d M Y') ?? '-' }}</p>
                <p><strong>Gender:</strong> {{ $employee->gender ?: '-' }}</p>
                <p><strong>Blood Group:</strong> {{ $employee->blood_group ?: '-' }}</p>
                <p><strong>Emergency Contact:</strong> {{ $employee->emergency_contact ?: '-' }}</p>
            </div>

        </div>

        <div class="mt-8">
            <h3 class="font-semibold text-lg mb-2">Address</h3>

            <div class="border rounded-lg p-4 bg-gray-50">
                {{ $employee->address ?: 'No address provided.' }}
            </div>
        </div>

    </div>

</div>

@endsection