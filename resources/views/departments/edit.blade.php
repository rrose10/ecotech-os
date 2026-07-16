@extends('layouts.app')

@section('content')

<div class="p-8 max-w-3xl">

    <h1 class="text-3xl font-bold mb-6">
        Edit Department
    </h1>

    <form action="{{ route('departments.update',$department) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="mb-5">
            <label class="block mb-2">Department Name</label>

            <input
                type="text"
                name="name"
                value="{{ old('name',$department->name) }}"
                class="w-full border rounded p-3">
        </div>

        <div class="mb-5">
            <label class="block mb-2">Department Code</label>

            <input
                type="text"
                name="code"
                value="{{ old('code',$department->code) }}"
                class="w-full border rounded p-3">
        </div>

        <div class="mb-5">
            <label class="block mb-2">Description</label>

            <textarea
                name="description"
                class="w-full border rounded p-3"
                rows="5">{{ old('description',$department->description) }}</textarea>
        </div>

        <button
            class="bg-blue-600 text-white px-6 py-3 rounded">
            Update Department
        </button>

    </form>

</div>

@endsection