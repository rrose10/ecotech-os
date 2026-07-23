@extends('layouts.app')

@section('title', 'Add Employee Loan')

@section('page_title', 'Add Employee Loan')

@section('content')

<div class="max-w-6xl mx-auto">

    <div class="bg-white rounded-xl shadow">

        <div class="border-b px-6 py-4">

            <h2 class="text-xl font-bold">
                Add Employee Loan
            </h2>

            <p class="text-gray-500 text-sm mt-1">
                Create a new employee loan.
            </p>

        </div>

        <div class="p-6">

            @if ($errors->any())

                <div class="mb-6 rounded-lg bg-red-100 border border-red-300 p-4">

                    <ul class="list-disc list-inside text-red-700">

                        @foreach ($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif

            <form
                action="{{ route('employee-loans.store') }}"
                method="POST">

                @include('employee-loans._form')

            </form>

        </div>

    </div>

</div>

@endsection