@extends('layouts.app')

@section('title', 'Apply Leave')
@section('page_title', 'Apply Leave')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="bg-white rounded-xl shadow border border-gray-200">

        <div class="border-b px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800">
                Apply Leave
            </h2>

            <p class="mt-1 text-gray-500">
                Submit a new leave request.
            </p>
        </div>

        <form action="{{ route('leave-requests.store') }}" method="POST">

            @csrf

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block mb-2 font-medium">
                        Employee
                    </label>

                    <select name="employee_id" class="w-full rounded-lg border-gray-300" required>

                        <option value="">Select Employee</option>

                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}"
                                {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach

                    </select>

                    @error('employee_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium">
                        Leave Type
                    </label>

                    <select name="leave_type_id" class="w-full rounded-lg border-gray-300" required>

                        <option value="">Select Leave Type</option>

                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}"
                                {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach

                    </select>

                    @error('leave_type_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium">
                        From Date
                    </label>

                    <input
                        type="date"
                        name="from_date"
                        value="{{ old('from_date') }}"
                        class="w-full rounded-lg border-gray-300"
                        required>

                    @error('from_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium">
                        To Date
                    </label>

                    <input
                        type="date"
                        name="to_date"
                        value="{{ old('to_date') }}"
                        class="w-full rounded-lg border-gray-300"
                        required>

                    @error('to_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">

                    <label class="block mb-2 font-medium">
                        Reason
                    </label>

                    <textarea
                        name="reason"
                        rows="5"
                        class="w-full rounded-lg border-gray-300"
                        placeholder="Write leave reason...">{{ old('reason') }}</textarea>

                    @error('reason')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror

                </div>

            </div>

            <div class="flex justify-end gap-3 border-t px-6 py-4">

                <a href="{{ route('leave-requests.index') }}"
                   class="rounded-lg bg-gray-500 px-5 py-2 text-white hover:bg-gray-600">
                    Cancel
                </a>

                <button
                    class="rounded-lg bg-green-600 px-5 py-2 text-white hover:bg-green-700">
                    Submit Leave
                </button>

            </div>

        </form>

    </div>

</div>

@endsection