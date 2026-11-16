@extends('layouts.app')

@section('title', 'Payroll Details')
@section('page_title', 'Payroll Details')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="flex justify-between items-center mb-6 no-print">

        <h1 class="text-3xl font-bold">
            Salary Slip
        </h1>

        <div class="flex gap-2">

            <a href="{{ route('payrolls.index') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                ← Back
            </a>

            <button
                onclick="window.print()"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                🖨 Print
            </button>

        </div>

    </div>

    @include('payrolls._slip', ['payroll' => $payroll])

</div>

@endsection