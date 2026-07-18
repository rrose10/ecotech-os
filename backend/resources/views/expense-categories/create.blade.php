@extends('layouts.app')

@section('title', 'Add Expense Category')
@section('page_title', 'Add Expense Category')

@section('content')

<div class="p-8">

    <h1 class="text-3xl font-bold mb-6">
        Add Expense Category
    </h1>

    <div class="bg-white rounded-lg shadow p-6">

        <form action="{{ route('expense-categories.store') }}" method="POST">

            @csrf

            @include('expense-categories._form')

            <div class="mt-6 flex gap-3">

                <button
                    type="submit"
                    class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">
                    Save
                </button>

                <a href="{{ route('expense-categories.index') }}"
                   class="bg-gray-500 text-white px-5 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

@endsection