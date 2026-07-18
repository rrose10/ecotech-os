@extends('layouts.app')

@section('title', 'Income Categories')
@section('page_title', 'Income Categories')

@section('content')

<div class="p-8">

    <div class="flex justify-between items-center mb-6">

        <h1 class="text-3xl font-bold">
            Income Categories
        </h1>

        <a href="{{ route('income-categories.create') }}"
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Add Category
        </a>

    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">

        <form method="GET" class="mb-4">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search Category..."
                class="w-full md:w-80 border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">
        </form>

        @if($incomeCategories->count())

            <table class="w-full">

                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2">Name</th>
                        <th class="text-left p-2">Status</th>
                        <th class="text-left p-2">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($incomeCategories as $category)

                        <tr class="border-b">

                            <td class="p-2">
                                {{ $category->name }}
                            </td>

                            <td class="p-2">
                                @if($category->status)
                                    <span class="text-green-600 font-medium">Active</span>
                                @else
                                    <span class="text-red-600 font-medium">Inactive</span>
                                @endif
                            </td>

                            <td class="p-2 flex gap-2">

                                <a href="{{ route('income-categories.edit', $category) }}"
                                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                    Edit
                                </a>

                                <form action="{{ route('income-categories.destroy', $category) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this category?')">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

            <div class="mt-6">
                {{ $incomeCategories->links() }}
            </div>

        @else

            <p class="text-gray-500">
                No Income Categories Found.
            </p>

        @endif

    </div>

</div>

@endsection