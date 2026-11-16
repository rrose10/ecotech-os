@extends('layouts.app')
@section('title', 'Income & expense report')
@section('content')
<div class="p-4 sm:p-8">
    <h1 class="text-3xl font-bold mb-6">Income &amp; expense report</h1>
    @include('reports._period')

    <div class="grid gap-4 md:grid-cols-4 mb-6">
        <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Income (BDT)</p><p class="text-2xl font-bold text-green-700">৳{{ number_format($incomeBdt, 2) }}</p></div>
        <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Income (USD)</p><p class="text-2xl font-bold text-green-700">${{ number_format($incomeUsd, 2) }}</p></div>
        <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Expense</p><p class="text-2xl font-bold text-red-600">৳{{ number_format($expenseTotal, 2) }}</p></div>
        <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Net (BDT)</p><p class="text-2xl font-bold {{ $incomeBdt - $expenseTotal >= 0 ? 'text-green-700' : 'text-red-600' }}">৳{{ number_format($incomeBdt - $expenseTotal, 2) }}</p></div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
            <h2 class="text-xl font-bold mb-3">Income by category</h2>
            @forelse($incomeByCategory as $name => $v)
                <div class="flex justify-between border-b py-2"><span>{{ $name }}</span><span>৳{{ number_format($v['bdt'], 2) }}@if($v['usd'] > 0) · ${{ number_format($v['usd'], 2) }}@endif</span></div>
            @empty
                <p class="text-gray-500">No income this month.</p>
            @endforelse
        </div>
        <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
            <h2 class="text-xl font-bold mb-3">Expense by category</h2>
            @forelse($expenseByCategory as $name => $v)
                <div class="flex justify-between border-b py-2"><span>{{ $name }}</span><span>৳{{ number_format($v, 2) }}</span></div>
            @empty
                <p class="text-gray-500">No expense this month.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
