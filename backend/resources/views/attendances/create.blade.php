@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

    <h1 class="text-3xl font-bold mb-8">
        Mark Attendance
    </h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded mb-6">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('attendances.store') }}" method="POST">

        @csrf

        <div class="grid grid-cols-2 gap-6">

            <div>
                <label class="block mb-2 font-medium">Employee</label>

                <select name="employee_id" class="w-full border rounded p-3">

                    <option value="">Select Employee</option>

                    @foreach($employees as $employee)

                        <option value="{{ $employee->id }}">
                            {{ $employee->employee_id }} - {{ $employee->name }}
                        </option>

                    @endforeach

                </select>
            </div>

            <div>
                <label class="block mb-2 font-medium">Date</label>

                <input
                    type="date"
                    name="date"
                    value="{{ date('Y-m-d') }}"
                    class="w-full border rounded p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">
                    Status
                </label>

                <select
                    name="status"
                    class="w-full border rounded p-3">

                    <option value="Present">Present</option>
                    <option value="Late">Late</option>
                    <option value="Absent">Absent</option>
                    <option value="Leave">Leave</option>

                </select>
            </div>

            <div>
                <label class="block mb-2 font-medium">
                    Check In
                </label>

                <input
                    type="time"
                    name="check_in"
                    class="w-full border rounded p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">
                    Check Out
                </label>

                <input
                    type="time"
                    name="check_out"
                    class="w-full border rounded p-3">
            </div>

        </div>

        <div class="mt-6">

            <label class="block mb-2 font-medium">
                Remarks
            </label>

            <textarea
                name="remarks"
                rows="4"
                class="w-full border rounded p-3"></textarea>

        </div>

        <button
            class="mt-8 bg-green-600 text-white px-8 py-3 rounded hover:bg-green-700">

            Save Attendance

        </button>

    </form>

</div>

@endsection