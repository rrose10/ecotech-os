@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="p-8">
    <h1 class="text-3xl font-bold mb-6">Reports</h1>
    <div class="grid gap-6 md:grid-cols-3">
        <a href="{{ route('reports.finance') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition block">
            <h2 class="text-xl font-bold mb-1">Income &amp; expense</h2>
            <p class="text-gray-600">Monthly totals and category breakdown, with net result.</p>
        </a>
        <a href="{{ route('reports.attendance') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition block">
            <h2 class="text-xl font-bold mb-1">Attendance</h2>
            <p class="text-gray-600">Present, late, absent, leave and hours for every employee.</p>
        </a>
        <a href="{{ route('reports.payroll') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition block">
            <h2 class="text-xl font-bold mb-1">Payroll</h2>
            <p class="text-gray-600">Gross, deductions, net pay, paid vs pending.</p>
        </a>
    </div>
</div>
@endsection
