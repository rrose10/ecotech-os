@extends('layouts.app')

@section('title', 'Edit Leave')
@section('page_title', 'Edit Leave')

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="bg-white rounded-xl shadow border border-gray-200">

        <div class="border-b px-6 py-4">

            <h2 class="text-2xl font-bold text-gray-800">
                Edit Leave
            </h2>

            <p class="text-gray-500 mt-1">
                Update leave application.
            </p>

        </div>


        <form action="{{ route('leaves.update',$leave) }}" method="POST">

            @csrf
            @method('PUT')


            <div class="p-6 space-y-6">


                <div>

                    <label class="block mb-2 font-medium">
                        Team Member
                    </label>


                    <select
                        name="employee_id"
                        class="w-full rounded-lg border-gray-300"
                        required>


                        @foreach($employees as $employee)

                            <option value="{{ $employee->id }}"
                                {{ old('employee_id',$leave->employee_id) == $employee->id ? 'selected' : '' }}>

                                {{ $employee->name }}
                                ({{ $employee->employee_id }})

                            </option>

                        @endforeach


                    </select>


                </div>



                <div>

                    <label class="block mb-2 font-medium">
                        Leave Type
                    </label>


                    <select
                        name="leave_type_id"
                        class="w-full rounded-lg border-gray-300"
                        required>


                        @foreach($leaveTypes as $type)

                            <option value="{{ $type->id }}"
                                {{ old('leave_type_id',$leave->leave_type_id) == $type->id ? 'selected' : '' }}>

                                {{ $type->name }}

                            </option>

                        @endforeach


                    </select>


                </div>




                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                    <div>

                        <label class="block mb-2 font-medium">
                            From Date
                        </label>


                        <input
                            type="date"
                            name="from_date"
                            value="{{ old('from_date',$leave->from_date->format('Y-m-d')) }}"
                            class="w-full rounded-lg border-gray-300"
                            required>

                    </div>



                    <div>

                        <label class="block mb-2 font-medium">
                            To Date
                        </label>


                        <input
                            type="date"
                            name="to_date"
                            value="{{ old('to_date',$leave->to_date->format('Y-m-d')) }}"
                            class="w-full rounded-lg border-gray-300"
                            required>

                    </div>


                </div>




                <div>

                    <label class="block mb-2 font-medium">
                        Status
                    </label>


                    <select
                        name="status"
                        class="w-full rounded-lg border-gray-300">


                        <option value="Pending"
                            {{ $leave->status == 'Pending' ? 'selected' : '' }}>
                            Pending
                        </option>


                        <option value="Approved"
                            {{ $leave->status == 'Approved' ? 'selected' : '' }}>
                            Approved
                        </option>


                        <option value="Rejected"
                            {{ $leave->status == 'Rejected' ? 'selected' : '' }}>
                            Rejected
                        </option>


                    </select>

                </div>




                <div>

                    <label class="block mb-2 font-medium">
                        Reason
                    </label>


                    <textarea
                        name="reason"
                        rows="4"
                        class="w-full rounded-lg border-gray-300">{{ old('reason',$leave->reason) }}</textarea>


                </div>




            </div>




            <div class="flex justify-end gap-3 border-t px-6 py-4">


                <a href="{{ route('leaves.index') }}"
                   class="rounded-lg bg-gray-500 px-5 py-2 text-white hover:bg-gray-600">

                    Cancel

                </a>


                <button
                    class="rounded-lg bg-blue-600 px-5 py-2 text-white hover:bg-blue-700">

                    Update Leave

                </button>


            </div>


        </form>


    </div>

</div>

@endsection