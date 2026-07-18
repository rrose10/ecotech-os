@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto p-8">

    <h1 class="text-3xl font-bold mb-6">
        Add Department
    </h1>

    <form action="{{ route('departments.store') }}" method="POST">

        @csrf

        <div class="mb-4">
            <label class="block mb-2 font-semibold">
                Department Name
            </label>

            <input
                type="text"
                name="name"
                class="w-full border rounded p-3"
                required>
        </div>

        <div class="mb-4">
            <label class="block mb-2 font-semibold">
                Department Code
            </label>

            <input
                type="text"
                name="code"
                class="w-full border rounded p-3"
                required>
        </div>

        <div class="mb-6">
            <label class="block mb-2 font-semibold">
                Description
            </label>

            <textarea
                name="description"
                rows="4"
                class="w-full border rounded p-3"></textarea>
        </div>

        <button
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded">

            Save Department

        </button>

    </form>

</div>

@endsection