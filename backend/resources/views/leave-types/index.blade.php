@extends('layouts.app')

@section('title', 'Leave Types')
@section('page_title', 'Leave Types')

@section('content')

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Leave Types
            </h1>

            <p class="mt-1 text-gray-500">
                Manage employee leave types.
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">

            <form action="{{ route('leave-types.index') }}" method="GET" class="flex">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search Leave Type..."
                    class="w-72 rounded-l-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none">

                <button
                    class="rounded-r-lg bg-blue-600 px-5 text-white hover:bg-blue-700">
                    Search
                </button>

            </form>

            <a href="{{ route('leave-types.create') }}"
               class="rounded-lg bg-green-600 px-5 py-2 text-white hover:bg-green-700">
                + Add Leave Type
            </a>

        </div>

    </div>

    @if(session('success'))

        <div class="rounded-lg border border-green-300 bg-green-100 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>

    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        @if($leaveTypes->count())

        <table class="w-full">

            <thead class="border-b bg-gray-50">

                <tr>

                    <th class="px-6 py-4 text-left">Leave Type</th>

                    <th class="px-6 py-4 text-left">
                        Days / Year
                    </th>

                    <th class="px-6 py-4 text-left">
                        Status
                    </th>

                    <th class="px-6 py-4 text-center">
                        Actions
                    </th>

                </tr>

            </thead>

            <tbody>

            @foreach($leaveTypes as $leaveType)

                <tr class="border-b hover:bg-gray-50">

                    <td class="px-6 py-4 font-medium">
                        {{ $leaveType->name }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $leaveType->days_per_year }}
                    </td>

                    <td class="px-6 py-4">

                        @if($leaveType->status)

                            <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700">
                                Active
                            </span>

                        @else

                            <span class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-700">
                                Inactive
                            </span>

                        @endif

                    </td>

                    <td class="px-6 py-4">

                        <div class="flex justify-center gap-2">

                            <a href="{{ route('leave-types.edit',$leaveType) }}"
                               class="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700">
                                ✏ Edit
                            </a>

                            <form
                                action="{{ route('leave-types.destroy',$leaveType) }}"
                                method="POST"
                                onsubmit="return confirm('Delete this leave type?')">

                                @csrf
                                @method('DELETE')

                                <button
                                    class="rounded-lg bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-700">
                                    🗑 Delete
                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

        <div class="border-t bg-gray-50 px-6 py-4">
            {{ $leaveTypes->links() }}
        </div>

        @else

        <div class="p-12 text-center">

            <div class="text-5xl">
                📅
            </div>

            <h2 class="mt-4 text-xl font-semibold">
                No Leave Types Found
            </h2>

            <p class="mt-2 text-gray-500">
                Create your first leave type.
            </p>

            <a href="{{ route('leave-types.create') }}"
               class="mt-5 inline-block rounded-lg bg-green-600 px-6 py-3 text-white hover:bg-green-700">
                + Add Leave Type
            </a>

        </div>

        @endif

    </div>

</div>

@endsection