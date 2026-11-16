@extends('layouts.app')

@section('title', 'Edit Team Loan')

@section('page_title', 'Edit Team Loan')

@section('content')

<div class="max-w-6xl mx-auto">

    <div class="bg-white rounded-xl shadow">

        <div class="border-b px-6 py-4">

            <h2 class="text-xl font-bold">

                Edit Team Loan

            </h2>

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
                action="{{ route('employee-loans.update',$employeeLoan) }}"
                method="POST">

                @csrf

                @method('PUT')

                @include('employee-loans._form')

            </form>

        </div>

    </div>

</div>

@endsection