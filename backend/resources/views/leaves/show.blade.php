@extends('layouts.app')

@section('title', 'Leave Details')
@section('page_title', 'Leave Details')

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="bg-white rounded-xl shadow border border-gray-200">


        <div class="border-b px-6 py-4">

            <h2 class="text-2xl font-bold text-gray-800">
                Leave Details
            </h2>

        </div>



        <div class="p-6 space-y-5">


            <div>
                <p class="text-gray-500">
                    Employee
                </p>

                <p class="font-semibold">
                    {{ $leave->employee->name ?? 'Employee Deleted' }}
                </p>
            </div>



            <div>
                <p class="text-gray-500">
                    Employee ID
                </p>

                <p class="font-semibold">
                    {{ $leave->employee->employee_id ?? '-' }}
                </p>
            </div>



            <div>
                <p class="text-gray-500">
                    Leave Type
                </p>

                <p class="font-semibold">
                    {{ $leave->leaveType->name ?? 'Leave Type Deleted' }}
                </p>
            </div>




            <div class="grid grid-cols-2 gap-5">


                <div>

                    <p class="text-gray-500">
                        From Date
                    </p>

                    <p class="font-semibold">
                        {{ $leave->from_date?->format('d M Y') }}
                    </p>

                </div>



                <div>

                    <p class="text-gray-500">
                        To Date
                    </p>

                    <p class="font-semibold">
                        {{ $leave->to_date?->format('d M Y') }}
                    </p>

                </div>


            </div>





            <div>

                <p class="text-gray-500">
                    Total Days
                </p>

                <p class="font-semibold">
                    {{ $leave->total_days }} Days
                </p>

            </div>




            <div>

                <p class="text-gray-500">
                    Status
                </p>


                @if($leave->status == 'Approved')

                    <span class="rounded-full bg-green-100 px-3 py-1 text-green-700">
                        Approved
                    </span>


                @elseif($leave->status == 'Rejected')

                    <span class="rounded-full bg-red-100 px-3 py-1 text-red-700">
                        Rejected
                    </span>


                @else

                    <span class="rounded-full bg-yellow-100 px-3 py-1 text-yellow-700">
                        Pending
                    </span>


                @endif

            </div>




            <div>

                <p class="text-gray-500">
                    Reason
                </p>

                <p>
                    {{ $leave->reason ?? 'N/A' }}
                </p>

            </div>



            @if($leave->status == 'Approved')

            <div>

                <p class="text-gray-500">
                    Approved Date
                </p>

                <p class="font-semibold">
                    {{ $leave->approved_at?->format('d M Y h:i A') ?? '-' }}
                </p>

            </div>

            @endif



        </div>




        <div class="border-t px-6 py-4">

            <a href="{{ route('leaves.index') }}"
               class="rounded-lg bg-gray-600 px-5 py-2 text-white hover:bg-gray-700">

                Back

            </a>

        </div>



    </div>

</div>

@endsection