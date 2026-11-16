@extends('layouts.app')

@section('title', 'Leave Requests')
@section('page_title', 'Leave Requests')

@section('content')

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Leave Requests
            </h1>

            <p class="mt-1 text-gray-500">
                Manage team leave applications.
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">

            <form action="{{ route('leave-requests.index') }}" method="GET" class="flex">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search team member..."
                    class="w-72 rounded-l-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none">

                <button
                    class="rounded-r-lg bg-blue-600 px-5 text-white hover:bg-blue-700">
                    Search
                </button>

            </form>

            <a href="{{ route('leave-requests.create') }}"
               class="rounded-lg bg-green-600 px-5 py-2 text-white hover:bg-green-700">
                + Apply Leave
            </a>

        </div>

    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-300 bg-green-100 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        @if($leaveRequests->count())

        <table class="min-w-full">

            <thead class="bg-gray-50 border-b">

                <tr>

                    <th class="px-6 py-4 text-left">Team Member</th>

                    <th class="px-6 py-4 text-left">Leave Type</th>

                    <th class="px-6 py-4 text-left">From</th>

                    <th class="px-6 py-4 text-left">To</th>

                    <th class="px-6 py-4 text-center">Days</th>

                    <th class="px-6 py-4 text-center">Status</th>

                    <th class="px-6 py-4 text-center">Actions</th>

                </tr>

            </thead>

            <tbody>

            @foreach($leaveRequests as $leave)

                <tr class="border-b hover:bg-gray-50">

                    <td class="px-6 py-4 font-medium">
                        {{ $leave->employee->name }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $leave->leaveType->name }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $leave->from_date->format('d M Y') }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $leave->to_date->format('d M Y') }}
                    </td>

                    <td class="px-6 py-4 text-center">
                        {{ $leave->total_days }}
                    </td>

                    <td class="px-6 py-4 text-center">

                        @if($leave->status=='Approved')

                            <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700">
                                Approved
                            </span>

                        @elseif($leave->status=='Rejected')

                            <span class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-700">
                                Rejected
                            </span>

                        @else

                            <span class="rounded-full bg-yellow-100 px-3 py-1 text-sm text-yellow-700">
                                Pending
                            </span>

                        @endif

                    </td>

                    <td class="px-6 py-4">

                        <div class="flex justify-center gap-2">

                            <a href="{{ route('leave-requests.edit',$leave) }}"
                               class="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700">
                                ✏ Edit
                            </a>

                            <form
                                action="{{ route('leave-requests.destroy',$leave) }}"
                                method="POST"
                                onsubmit="return confirm('Delete this leave request?')">

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
            {{ $leaveRequests->links() }}
        </div>

        @else

        <div class="p-12 text-center">

            <div class="text-5xl">
                📝
            </div>

            <h2 class="mt-4 text-xl font-semibold">
                No Leave Requests Found
            </h2>

            <p class="mt-2 text-gray-500">
                Create your first leave request.
            </p>

            <a href="{{ route('leave-requests.create') }}"
               class="mt-5 inline-block rounded-lg bg-green-600 px-6 py-3 text-white hover:bg-green-700">
                + Apply Leave
            </a>

        </div>

        @endif

    </div>

</div>

@endsection