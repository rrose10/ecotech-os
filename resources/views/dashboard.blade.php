@extends('layouts.app')

@section('content')

<div class="grid grid-cols-4 gap-6">

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-gray-500">Employees</h2>
        <p class="text-3xl font-bold mt-2">0</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-gray-500">Income</h2>
        <p class="text-3xl font-bold mt-2">৳0</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-gray-500">Expense</h2>
        <p class="text-3xl font-bold mt-2">৳0</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-gray-500">Assets</h2>
        <p class="text-3xl font-bold mt-2">0</p>
    </div>

</div>

@endsection