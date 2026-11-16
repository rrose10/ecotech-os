@extends('layouts.app')
@section('title', 'Payroll report')
@section('content')
<div class="p-8">
    <h1 class="text-3xl font-bold mb-6">Payroll report</h1>
    @include('reports._period')

    <div class="grid gap-4 md:grid-cols-4 mb-6">
        <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Gross</p><p class="text-2xl font-bold">৳{{ number_format($totals['gross'], 2) }}</p></div>
        <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Advance + loan deducted</p><p class="text-2xl font-bold">৳{{ number_format($totals['advance'] + $totals['loan'], 2) }}</p></div>
        <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Net paid</p><p class="text-2xl font-bold text-green-700">৳{{ number_format($totals['paid'], 2) }}</p></div>
        <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Net pending</p><p class="text-2xl font-bold text-amber-700">৳{{ number_format($totals['pending'], 2) }}</p></div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
        @if($payrolls->count())
        <table class="w-full">
            <thead><tr class="border-b">
                <th class="text-left p-2">Team Member</th><th class="text-left p-2">Gross</th><th class="text-left p-2">Advance</th>
                <th class="text-left p-2">Loan</th><th class="text-left p-2">Net</th><th class="text-left p-2">Status</th>
            </tr></thead>
            <tbody>
            @foreach($payrolls as $p)
                <tr class="border-b">
                    <td class="p-2">{{ $p->employee->name ?? '—' }}</td>
                    <td class="p-2">৳{{ number_format($p->total_salary, 2) }}</td>
                    <td class="p-2">৳{{ number_format($p->advance_amount, 2) }}</td>
                    <td class="p-2">৳{{ number_format($p->loan_amount, 2) }}</td>
                    <td class="p-2">৳{{ number_format($p->net_salary, 2) }}</td>
                    <td class="p-2">{{ $p->status }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @else
            <p class="text-gray-500">No payroll for this month.</p>
        @endif
    </div>
</div>
@endsection
