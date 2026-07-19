@extends('layouts.portal')

@section('title', 'Dashboard')

@section('page_title', 'Dashboard')

@section('content')

<div class="space-y-6">


    {{-- Welcome --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold text-slate-900">
                    Welcome, {{ $employee?->name ?? 'Employee' }}
                </h1>

                <p class="text-slate-500 mt-2">
                    Employee ID:
                    <strong>
                        {{ $employee?->employee_id ?? '--' }}
                    </strong>
                </p>

            </div>


            <div class="text-right">

                <div id="liveTime"
                     class="text-3xl font-bold text-indigo-600">
                </div>

                <div id="liveDate"
                     class="text-sm text-slate-500 mt-1">
                </div>

            </div>

        </div>

    </div>



    {{-- Today's Attendance --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">


        <h2 class="text-xl font-bold mb-6">
            Today's Attendance
        </h2>


        <div class="grid md:grid-cols-2 gap-6">


            <div class="space-y-4">


                <div>
                    <span class="text-slate-500">
                        Status
                    </span>

                    <br>

                    <span class="font-semibold text-lg">
                        {{ $todayAttendance?->status ?? 'Not Checked In' }}
                    </span>
                </div>



                <div>
                    <span class="text-slate-500">
                        Shift
                    </span>

                    <br>

                    <span class="font-semibold">
                        {{ $todayAttendance?->shift ?? 'Night' }}
                    </span>
                </div>



                <div>
                    <span class="text-slate-500">
                        Check In
                    </span>

                    <br>

                    <span class="font-semibold">
                        {{ optional($todayAttendance?->check_in)->format('h:i A') ?? '--' }}
                    </span>
                </div>



                <div>
                    <span class="text-slate-500">
                        Check Out
                    </span>

                    <br>

                    <span class="font-semibold">
                        {{ optional($todayAttendance?->check_out)->format('h:i A') ?? '--' }}
                    </span>
                </div>



                <div>
                    <span class="text-slate-500">
                        Working Hours
                    </span>

                    <br>

                    <span class="font-semibold">

                        @if($todayAttendance && $todayAttendance->working_minutes)

                            {{ floor($todayAttendance->working_minutes / 60) }}h
                            {{ $todayAttendance->working_minutes % 60 }}m

                        @else

                            --

                        @endif

                    </span>
                </div>


            </div>



            <div class="flex items-center justify-center">


                @if(!$todayAttendance)


                    <form action="{{ route('portal.attendance.checkin') }}"
                          method="POST">

                        @csrf


                        <button
                            onclick="return confirm('Are you sure you want to Check In?')"
                            class="px-10 py-4 rounded-xl bg-green-600 hover:bg-green-700 text-white font-semibold text-lg">

                            🟢 Check In

                        </button>


                    </form>



                @elseif(!$todayAttendance->check_out)


                    <form action="{{ route('portal.attendance.checkout') }}"
                          method="POST">

                        @csrf


                        <button
                            onclick="return confirm('Are you sure you want to Check Out?')"
                            class="px-10 py-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-lg">

                            🔴 Check Out

                        </button>


                    </form>



                @else


                    <div class="text-center">

                        <div class="text-green-600 text-6xl">
                            ✓
                        </div>


                        <p class="mt-3 font-semibold text-green-700">
                            Attendance Completed
                        </p>

                    </div>


                @endif


            </div>


        </div>


    </div>





    {{-- Latest Notices --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">


        <div class="flex items-center justify-between mb-6">


            <h2 class="text-xl font-bold">
                📢 Latest Notices
            </h2>



            <a href="{{ route('portal.notices') }}"
               class="text-indigo-600 hover:underline font-medium">

                View All →

            </a>


        </div>



        @forelse($latestNotices as $notice)


            <a href="{{ route('portal.notices.show', $notice) }}"
               class="block border border-slate-200 rounded-xl p-4 mb-4 hover:bg-slate-50 transition">


                <div class="flex justify-between items-start">


                    <div>


                        <h3 class="font-semibold text-lg">

                            {{ $notice->title }}

                        </h3>


                        <p class="text-sm text-slate-500 mt-1">

                            {{ $notice->publish_date->format('d M Y') }}

                        </p>


                    </div>



                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">

                        {{ $notice->type }}

                    </span>


                </div>



                <p class="mt-3 text-slate-600">

                    {{ \Illuminate\Support\Str::limit(strip_tags($notice->content),120) }}

                </p>


            </a>



        @empty


            <div class="text-center py-8 text-slate-500">

                No notices available.

            </div>


        @endforelse


    </div>



</div>



<script>

function updateClock(){

    const now = new Date();

    document.getElementById('liveTime').innerHTML =
        now.toLocaleTimeString();


    document.getElementById('liveDate').innerHTML =
        now.toDateString();

}


updateClock();


setInterval(updateClock,1000);


</script>


@endsection