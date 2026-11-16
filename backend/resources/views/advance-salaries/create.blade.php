@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="mb-8">

        <h1 class="text-3xl font-bold">
            Add Advance Salary
        </h1>

        <p class="text-gray-500 mt-1">
            Create a new team advance salary.
        </p>

    </div>

    {{-- Validation Errors --}}
    @if($errors->any())

        <div class="mb-6 rounded-lg border border-red-300 bg-red-100 px-4 py-3 text-red-700">

            <ul class="list-disc pl-5">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <div class="bg-white rounded-xl shadow border p-6">

        <form
            action="{{ route('advance-salaries.store') }}"
            method="POST">

            @csrf

            @include('advance-salaries._form')

        </form>

    </div>

</div>

@endsection