@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

    <div class="flex items-center justify-between mb-6">

        <div>
            <h1 class="text-3xl font-bold">
                Notices
            </h1>

            <p class="text-gray-500 mt-1">
                Manage office notices.
            </p>
        </div>

        <a href="{{ route('notices.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg">
            + Create Notice
        </a>

    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-x-auto">

        <table class="w-full">

            <thead class="bg-gray-100">

                <tr>

                    <th class="text-left px-5 py-3">Title</th>

                    <th class="text-left px-5 py-3">Type</th>

                    <th class="text-left px-5 py-3">Publish Date</th>

                    <th class="text-left px-5 py-3">Status</th>

                    <th class="text-right px-5 py-3">Actions</th>

                </tr>

            </thead>

            <tbody>

                @forelse($notices as $notice)

                    <tr class="border-t">

                        <td class="px-5 py-4 font-medium">
                            {{ $notice->title }}
                        </td>

                        <td class="px-5 py-4">
                            {{ $notice->type }}
                        </td>

                        <td class="px-5 py-4">
                            {{ $notice->publish_date->format('d M Y') }}
                        </td>

                        <td class="px-5 py-4">

                            @if($notice->is_published)
                                <span class="text-green-600 font-semibold">
                                    Published
                                </span>
                            @else
                                <span class="text-red-600 font-semibold">
                                    Draft
                                </span>
                            @endif

                        </td>

                        <td class="px-5 py-4 text-right space-x-2">

                            <a href="{{ route('notices.show',$notice) }}"
                               class="text-blue-600 hover:underline">
                                View
                            </a>

                            <a href="{{ route('notices.edit',$notice) }}"
                               class="text-indigo-600 hover:underline">
                                Edit
                            </a>

                            <form action="{{ route('notices.destroy',$notice) }}"
                                  method="POST"
                                  class="inline">

                                @csrf
                                @method('DELETE')

                                <button
                                    onclick="return confirm('Delete this notice?')"
                                    class="text-red-600 hover:underline">

                                    Delete

                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5"
                            class="text-center py-10 text-gray-500">

                            No notices found.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-6">

        {{ $notices->links() }}

    </div>

</div>

@endsection