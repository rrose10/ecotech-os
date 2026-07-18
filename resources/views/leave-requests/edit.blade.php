@extends('layouts.app')

@section('title', 'Edit Leave Request')
@section('page_title', 'Edit Leave Request')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="bg-white rounded-xl shadow border border-gray-200">

        <div class="border-b px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800">
                Edit Leave Request
            </h2>

            <p class="mt-1 text-gray-500">
                Update employee leave request.
            </p>
        </div>

        <form action="{{ route('leave-requests.update', $leaveRequest) }}" method="POST">

            @csrf
            @method('PUT')

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block mb-2 font-medium">
                        Employee
                    </label>

                    <select name="employee_id" class="w-full rounded-lg border-gray-300" required>

                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}"
                                {{ old('employee_id', $leaveRequest->employee_id) == $employee->id ? 'selected' : '' }}>
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

                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}"
                                {{ old('leave_type_id', $leaveRequest->leave_type_id) == $type->id ? 'selected' : '' }}>
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
                        value="{{ old('from_date', $leaveRequest->from_date->format('Y-m-d')) }}"
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
                        value="{{ old('to_date', $leaveRequest->to_date->format('Y-m-d')) }}"
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
                        rows="4"
                        class="w-full rounded-lg border-gray-300">{{ old('reason', $leaveRequest->reason) }}</textarea>

                    @error('reason')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror

                </div>

                <div class="md:col-span-2">

                    <label class="block mb-2 font-medium">
                        Status
                    </label>

                    <select name="status" class="w-full rounded-lg border-gray-300">

                        <option value="Pending"
                            {{ old('status', $leaveRequest->status) == 'Pending' ? 'selected' : '' }}>
                            Pending
                        </option>

                        <option value="Approved"
                            {{ old('status', $leaveRequest->status) == 'Approved' ? 'selected' : '' }}>
                            Approved
                        </option>

                        <option value="Rejected"
                            {{ old('status', $leaveRequest->status) == 'Rejected' ? 'selected' : '' }}>
                            Rejected
                        </option>

                    </select>

                    @error('status')
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
                    type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-white hover:bg-blue-700">
                    Update Leave Request
                </button>

            </div>

        </form>

    </div>

</div>

@endsection