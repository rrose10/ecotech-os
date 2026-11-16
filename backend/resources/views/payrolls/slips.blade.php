@extends('layouts.app')

@section('title', 'Salary slips')
@section('page_title', 'Salary slips')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="no-print mb-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-3xl font-bold">Salary slips</h1>
            <div class="flex gap-2">
                <a href="{{ route('payrolls.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">← Back</a>
                @if($payrolls->count())
                    <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        🖨 Print / Save all as PDF
                    </button>
                @endif
            </div>
        </div>

        <form method="GET" class="flex flex-wrap items-center gap-2">
            <select name="month" class="border rounded p-2">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected($month === $m)>{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                @endfor
            </select>
            <input type="number" name="year" value="{{ $year }}" min="2000" max="2100" class="border rounded p-2 w-28">
            <select name="status" class="border rounded p-2">
                <option value="">Paid and pending</option>
                <option value="Paid" @selected($status === 'Paid')>Paid only</option>
                <option value="Pending" @selected($status === 'Pending')>Pending only</option>
            </select>
            <button class="bg-green-600 text-white px-4 py-2 rounded">Show</button>
        </form>

        @if($payrolls->count())
            <p class="mt-3 text-sm text-gray-500">
                {{ $payrolls->count() }} {{ \Illuminate\Support\Str::plural('slip', $payrolls->count()) }} for {{ \Carbon\Carbon::create(null, $month, 1)->format('F') }} {{ $year }}.
                In the print window choose <strong>Save as PDF</strong>.
            </p>
        @endif
    </div>

    @forelse($payrolls as $payroll)
        <div class="slip-page mb-8">
            @include('payrolls._slip', ['payroll' => $payroll])
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            No salary slips for this month yet.
        </div>
    @endforelse

</div>

@endsection
