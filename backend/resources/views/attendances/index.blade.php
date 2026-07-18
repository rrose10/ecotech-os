@extends('layouts.app')

@section('content')

<div class="p-8">

    <div class="flex justify-between items-center mb-6">

        <h1 class="text-3xl font-bold">
            Attendance
        </h1>

        <div class="flex gap-3">

            <form action="{{ route('attendances.index') }}" method="GET">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search employee..."
                    class="border rounded px-4 py-2">

                <button
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Search
                </button>

            </form>

            <a href="{{ route('attendances.create') }}"
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                + Mark Attendance
            </a>

        </div>

    </div>

    @if(session('success'))

        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>

    @endif

    <div class="bg-white rounded-lg shadow p-6">

        @if($attendances->count())

        <table class="w-full">

            <thead>

                <tr class="border-b">

                    <th class="text-left p-2">Employee</th>
                    <th class="text-left p-2">Date</th>
                    <th class="text-left p-2">Status</th>
                    <th class="text-left p-2">Check In</th>
                    <th class="text-left p-2">Check Out</th>
                    <th class="text-left p-2">Action</th>

                </tr>

            </thead>

            <tbody>

                @foreach($attendances as $attendance)

                <tr class="border-b">

                    <td class="p-2">
                        {{ $attendance->employee->name }}
                    </td>

                    <td class="p-2">
                        {{ $attendance->date->format('d M Y') }}
                    </td>

                    <td class="p-2">

                        @php
                            $color = match($attendance->status){
                                'Present' => 'green',
                                'Late' => 'yellow',
                                'Absent' => 'red',
                                default => 'blue'
                            };
                        @endphp

                        <span class="px-3 py-1 rounded-full text-sm bg-{{ $color }}-100 text-{{ $color }}-700">
                            {{ $attendance->status }}
                        </span>

                    </td>

                    <td class="p-2">
                        {{ $attendance->check_in ?? '-' }}
                    </td>

                    <td class="p-2">
                        {{ $attendance->check_out ?? '-' }}
                    </td>

                    <td class="p-2 flex gap-2">

                        <a href="{{ route('attendances.edit',$attendance) }}"
                           class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                            Edit
                        </a>

                        <form action="{{ route('attendances.destroy',$attendance) }}"
                              method="POST"
                              onsubmit="return confirm('Delete this attendance?')">

                            @csrf
                            @method('DELETE')

                            <button
                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                Delete
                            </button>

                        </form>

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

        <div class="mt-6">
            {{ $attendances->links() }}
        </div>

        @else

            <p class="text-gray-500">
                No Attendance Found.
            </p>

        @endif

    </div>

</div>

@endsection