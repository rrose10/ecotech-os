@extends('layouts.app')

@section('title', 'Edit Income Category')
@section('page_title', 'Edit Income Category')

@section('content')

<div class="p-8">

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">

        <h1 class="text-2xl font-bold mb-6">
            Edit Income Category
        </h1>

        <form action="{{ route('income-categories.update', $incomeCategory) }}" method="POST">

            @csrf
            @method('PUT')

            @include('income_categories._form')

            <button
                type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Update
            </button>

            <a href="{{ route('income-categories.index') }}"
               class="ml-2 text-gray-600 hover:underline">
                Cancel
            </a>

        </form>

    </div>

</div>

@endsection