@extends('layouts.app')

@section('content')

<div class="p-8">

    <div class="flex justify-between items-center mb-6">

        <h1 class="text-3xl font-bold">
            Employees
        </h1>

        <a href="{{ route('employees.create') }}"
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Add Employee
        </a>

    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">

        @if($employees->count())

        <table class="w-full">

            <thead>

                <tr class="border-b">

                    <th class="text-left p-2">Employee ID</th>
                    <th class="text-left p-2">Name</th>
                    <th class="text-left p-2">Department</th>
                    <th class="text-left p-2">Designation</th>
                    <th class="text-left p-2">Email</th>
                    <th class="text-left p-2">Status</th>
                    <th class="text-left p-2">Action</th>

                </tr>

            </thead>

            <tbody>

                @foreach($employees as $employee)

                <tr class="border-b">

                    <td class="p-2">{{ $employee->employee_id }}</td>

                    <td class="p-2">{{ $employee->name }}</td>

                    <td class="p-2">
                        {{ $employee->department->name ?? '-' }}
                    </td>

                    <td class="p-2">{{ $employee->designation }}</td>

                    <td class="p-2">{{ $employee->email }}</td>

                    <td class="p-2">
                        {{ $employee->status ? 'Active' : 'Inactive' }}
                    </td>

                    <td class="p-2">

                        <a href="{{ route('employees.edit', $employee) }}"
                           class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                            Edit
                        </a>

                        <form action="{{ route('employees.destroy', $employee) }}"
                              method="POST"
                              class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this employee?')">

                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                Delete
                            </button>

                        </form>

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

        @else

        <p class="text-gray-500">
            No Employees Found.
        </p>

        @endif

    </div>

</div>

@endsection