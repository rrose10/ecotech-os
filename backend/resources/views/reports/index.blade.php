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

    @if(session('success'))
        <div class="mt-8 rounded-lg bg-green-100 border border-green-300 text-green-700 px-4 py-3">{{ session('success') }}</div>
    @endif

    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-1">Monthly accounts (PDF)</h2>
        <p class="text-gray-600 mb-4">
            Income, expenses, payroll, loans and bank balances of one month.
            Last month's PDF and the all-time PDF are saved automatically on the 1st of every month.
        </p>

        <form method="GET" action="{{ route('reports.accounts.pdf') }}" class="flex flex-wrap items-center gap-2 mb-4">
            <select name="month" class="border rounded p-2">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected($m === now()->month)>{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                @endfor
            </select>
            <input type="number" name="year" value="{{ now()->year }}" min="2000" max="2100" class="border rounded p-2 w-28">
            <button class="bg-green-600 text-white px-4 py-2 rounded">Download PDF</button>
            <a href="{{ route('reports.accounts.pdf', ['all' => 1]) }}" class="border px-4 py-2 rounded">Download all-time PDF</a>
        </form>

        <form method="POST" action="{{ route('reports.accounts.save-all') }}" class="mb-6">
            @csrf
            <button class="border px-4 py-2 rounded">Save a PDF for every past month</button>
        </form>

        @if(count($savedAccounts))
            <h3 class="font-semibold mb-2">Saved PDFs</h3>
            <ul class="divide-y border rounded">
                @foreach($savedAccounts as $f)
                    <li class="flex items-center justify-between px-4 py-2">
                        <span>{{ $f['label'] }}</span>
                        <span class="text-sm text-gray-500">{{ number_format($f['size'] / 1024, 1) }} KB</span>
                        <a href="{{ route('reports.accounts.saved', $f['file']) }}" class="text-blue-600">Download</a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">No saved PDFs yet.</p>
        @endif
    </div>
</div>
@endsection
