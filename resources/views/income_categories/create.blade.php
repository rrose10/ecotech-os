@extends('layouts.app')

@section('title', 'Add Income Category')
@section('page_title', 'Add Income Category')

@section('content')

<div class="p-8">

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">

        <h1 class="text-2xl font-bold mb-6">
            Add Income Category
        </h1>

        <form action="{{ route('income-categories.store') }}" method="POST">

            @csrf

            @include('income_categories._form')

            <button
                type="submit"
                class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                Save
            </button>

            <a href="{{ route('income-categories.index') }}"
               class="ml-2 text-gray-600 hover:underline">
                Cancel
            </a>

        </form>

    </div>

</div>

@endsection