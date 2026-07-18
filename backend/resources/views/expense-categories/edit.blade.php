@extends('layouts.app')

@section('title', 'Edit Expense Category')
@section('page_title', 'Edit Expense Category')

@section('content')

<div class="p-8">

    <h1 class="text-3xl font-bold mb-6">
        Edit Expense Category
    </h1>

    <div class="bg-white rounded-lg shadow p-6">

        <form action="{{ route('expense-categories.update', $expenseCategory) }}" method="POST">

            @csrf
            @method('PUT')

            @include('expense-categories._form')

            <div class="mt-6 flex gap-3">

                <button
                    type="submit"
                    class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">
                    Update
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