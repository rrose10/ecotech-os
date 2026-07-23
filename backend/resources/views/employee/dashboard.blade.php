@extends('layouts.app')

@section('title','Employee Dashboard')
@section('page_title','Employee Dashboard')


@section('content')


<div class="space-y-6">


    <div class="bg-white rounded-xl shadow p-6">

        <h1 class="text-3xl font-bold">
            Welcome, {{ $employee->name }}
        </h1>

        <p class="text-gray-500 mt-2">
            Employee ID: {{ $employee->employee_id }}
        </p>

    </div>




    <div class="grid md:grid-cols-2 gap-6">



        <div class="bg-white rounded-xl shadow p-6">


            <h2 class="text-xl font-bold mb-4">
                Today's Shift
            </h2>


            <p>
                🕖 Start:
                <b>07:00 PM</b>
            </p>


            <p class="mt-2">
                🌅 End:
                <b>06:00 AM</b>
            </p>


        </div>





        <div class="bg-white rounded-xl shadow p-6">


            <h2 class="text-xl font-bold mb-4">
                Today's Attendance
            </h2>



            @if($attendance)


                <p class="text-green-600 font-semibold">
                    Status:
                    {{ $attendance->status }}
                </p>


                <p class="mt-2">
                    Check In:
                    {{ $attendance->check_in ?? '-' }}
                </p>


                <p>
                    Check Out:
                    {{ $attendance->check_out ?? '-' }}
                </p>


            @else


                <p class="text-gray-500">
                    Not Checked In
                </p>



                <button
                    class="mt-4 bg-green-600 text-white px-5 py-2 rounded-lg">

                    ✅ Check In

                </button>


            @endif


        </div>


    </div>



</div>


@endsection