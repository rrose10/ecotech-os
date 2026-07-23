@extends('layouts.app')

@section('title', 'Add Leave Type')
@section('page_title', 'Add Leave Type')

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="bg-white rounded-xl shadow border border-gray-200">

        <div class="border-b px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800">
                Add Leave Type
            </h2>

            <p class="text-gray-500 mt-1">
                Create a new leave type.
            </p>
        </div>

        <form action="{{ route('leave-types.store') }}" method="POST">

            @csrf

            <div class="p-6 space-y-6">

                <div>
                    <label class="block mb-2 font-medium">
                        Leave Type Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="w-full rounded-lg border-gray-300"
                        required>

                    @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium">
                        Days Per Year
                    </label>

                    <input
                        type="number"
                        name="days_per_year"
                        value="{{ old('days_per_year') }}"
                        min="0"
                        class="w-full rounded-lg border-gray-300"
                        required>

                    @error('days_per_year')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium">
                        Paid Leave
                    </label>

                    <select
                        name="is_paid"
                        class="w-full rounded-lg border-gray-300">

                        <option value="1" {{ old('is_paid', 1) == 1 ? 'selected' : '' }}>
                            Yes
                        </option>

                        <option value="0" {{ old('is_paid') == 0 ? 'selected' : '' }}>
                            No
                        </option>

                    </select>

                    @error('is_paid')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium">
                        Status
                    </label>

                    <select
                        name="status"
                        class="w-full rounded-lg border-gray-300">

                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>
                            Active
                        </option>

                        <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>
                            Inactive
                        </option>

                    </select>

                    @error('status')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror

                </div>

            </div>

            <div class="flex justify-end gap-3 border-t px-6 py-4">

                <a href="{{ route('leave-types.index') }}"
                   class="rounded-lg bg-gray-500 px-5 py-2 text-white hover:bg-gray-600">
                    Cancel
                </a>

                <button
                    class="rounded-lg bg-green-600 px-5 py-2 text-white hover:bg-green-700">
                    Save Leave Type
                </button>

            </div>

        </form>

    </div>

</div>

@endsection