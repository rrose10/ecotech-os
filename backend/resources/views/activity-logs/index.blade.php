@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-6">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Activity Logs
            </h1>

            <p class="text-gray-500 text-sm">
                View all user activities across the system.
            </p>
        </div>
    </div>

    <form method="GET" class="mb-4">
        <input
            type="text"
            name="search"
            value="{{ $search }}"
            placeholder="Search module, action or description..."
            class="w-full md:w-80 rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    </form>

    <div class="bg-white rounded-xl shadow overflow-hidden">

        <table class="min-w-full divide-y divide-gray-200">

            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Module</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Description</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse($activityLogs as $log)

                    <tr class="hover:bg-gray-50">

                        <td class="px-6 py-4 text-sm">
                            {{ $log->created_at->format('d M Y h:i A') }}
                        </td>

                        <td class="px-6 py-4 text-sm">
                            {{ $log->user->name ?? 'N/A' }}
                        </td>

                        <td class="px-6 py-4 text-sm">
                            {{ $log->module === 'Employee' ? 'Team' : $log->module }}
                        </td>

                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                                @if($log->action == 'Created')
                                    bg-green-100 text-green-700
                                @elseif($log->action == 'Updated')
                                    bg-yellow-100 text-yellow-700
                                @elseif($log->action == 'Deleted')
                                    bg-red-100 text-red-700
                                @else
                                    bg-gray-100 text-gray-700
                                @endif">
                                {{ $log->action }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-sm">
                            {{ $log->description }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            No activity logs found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-6">
        {{ $activityLogs->withQueryString()->links() }}
    </div>

</div>
@endsection