@extends('layouts.app')
@section('title', 'Attendance report')
@section('content')
<div class="p-8">
    <h1 class="text-3xl font-bold mb-6">Attendance report</h1>
    @include('reports._period')

    <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
        @if($rows->count())
        <table class="w-full">
            <thead><tr class="border-b">
                <th class="text-left p-2">Team Member</th><th class="text-left p-2">Present</th><th class="text-left p-2">Late</th>
                <th class="text-left p-2">Absent</th><th class="text-left p-2">Leave</th><th class="text-left p-2">Hours worked</th>
            </tr></thead>
            <tbody>
            @foreach($rows as $row)
                <tr class="border-b">
                    <td class="p-2">{{ $row['employee']->name }} <span class="text-sm text-gray-500">{{ $row['employee']->employee_id }}</span></td>
                    <td class="p-2">{{ $row['present'] }}</td><td class="p-2">{{ $row['late'] }}</td>
                    <td class="p-2">{{ $row['absent'] }}</td><td class="p-2">{{ $row['leave'] }}</td>
                    <td class="p-2">{{ $row['hours'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @else
            <p class="text-gray-500">No employees yet.</p>
        @endif
    </div>
</div>
@endsection
