@extends('layouts.app')

@section('title', 'Employees')
@section('page_title', 'Employees')

@section('content')

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Employees
            </h1>

            <p class="mt-1 text-gray-500">
                Manage all employees from one place.
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">

            <form action="{{ route('employees.index') }}" method="GET" class="flex">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by ID, Name or Email..."
                    class="w-72 rounded-l-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none">

                <button
                    class="rounded-r-lg bg-blue-600 px-5 text-white hover:bg-blue-700">
                    Search
                </button>

            </form>

            <a href="{{ route('employees.create') }}"
               class="rounded-lg bg-green-600 px-5 py-2 text-center font-medium text-white hover:bg-green-700">
                + Add Employee
            </a>

        </div>

    </div>

    @if(session('success'))

        <div class="rounded-lg border border-green-300 bg-green-100 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>

    @endif

    <!-- Table -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        @if($employees->count())

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead class="border-b bg-gray-50">

                    <tr>

                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            Photo
                        </th>

                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            Employee
                        </th>

                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            Department
                        </th>

                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            Designation
                        </th>

                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            Email
                        </th>

                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            Status
                        </th>

                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                @foreach($employees as $employee)

                    <tr class="border-b last:border-0 hover:bg-gray-50">

                        <!-- Photo -->
                        <td class="px-6 py-4">

                            @if($employee->photo)

                                <img
                                    src="{{ asset('storage/'.$employee->photo) }}"
                                    class="h-12 w-12 rounded-full border object-cover">

                            @else

                                <img
                                    src="https://ui-avatars.com/api/?name={{ urlencode($employee->name) }}&background=2563eb&color=fff"
                                    class="h-12 w-12 rounded-full border">

                            @endif

                        </td>

                        <!-- Employee -->
                        <td class="px-6 py-4">

                            <div class="font-semibold text-gray-800">
                                {{ $employee->name }}
                            </div>

                            <div class="text-sm text-gray-500">
                                {{ $employee->employee_id }}
                            </div>

                        </td>

                        <!-- Department -->
                        <td class="px-6 py-4">
                            {{ $employee->department->name ?? '-' }}
                        </td>

                        <!-- Designation -->
                        <td class="px-6 py-4">
                            {{ $employee->designation }}
                        </td>

                        <!-- Email -->
                        <td class="px-6 py-4 text-gray-600">
                            {{ $employee->email }}
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4">

                            @if($employee->status)

                                <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700">
                                    Active
                                </span>

                            @else

                                <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">
                                    Inactive
                                </span>

                            @endif

                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4">

                            <div class="flex justify-center gap-2">

                                <a href="{{ route('employees.show',$employee) }}"
                                   class="rounded-lg bg-gray-700 px-3 py-2 text-sm text-white hover:bg-gray-800">
                                    👁 View
                                </a>

                                <a href="{{ route('employees.edit',$employee) }}"
                                   class="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700">
                                    ✏ Edit
                                </a>

                                <form action="{{ route('employees.destroy',$employee) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this employee?')">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="rounded-lg bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-700">
                                        🗑 Delete
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

        <div class="border-t bg-gray-50 px-6 py-4">
            {{ $employees->links() }}
        </div>

        @else

        <div class="p-12 text-center">

            <div class="text-6xl">
                👥
            </div>

            <h2 class="mt-4 text-xl font-semibold text-gray-700">
                No Employees Found
            </h2>

            <p class="mt-2 text-gray-500">
                Start by adding your first employee.
            </p>

            <a href="{{ route('employees.create') }}"
               class="mt-5 inline-block rounded-lg bg-green-600 px-6 py-3 text-white hover:bg-green-700">
                + Add Employee
            </a>

        </div>

        @endif

    </div>

</div>

@endsection