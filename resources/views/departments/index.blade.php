@extends('layouts.app')

@section('content')

<div class="p-8">

    <div class="flex justify-between items-center mb-6">

        <h1 class="text-3xl font-bold">
            Departments
        </h1>

        <a href="{{ route('departments.create') }}"
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Add Department
        </a>

    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">

        @if($departments->count())

            <table class="w-full">

                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2">Name</th>
                        <th class="text-left p-2">Code</th>
                        <th class="text-left p-2">Description</th>
                        <th class="text-left p-2">Status</th>
                        <th class="text-left p-2">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($departments as $department)

                        <tr class="border-b">

                            <td class="p-2">{{ $department->name }}</td>

                            <td class="p-2">{{ $department->code }}</td>

                            <td class="p-2">
                                {{ $department->description }}
                            </td>

                            <td class="p-2">
                                {{ $department->status ? 'Active' : 'Inactive' }}
                            </td>

                            <td class="p-2 flex gap-2">

                                <a href="{{ route('departments.edit', $department) }}"
                                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                    Edit
                                </a>

                                <form action="{{ route('departments.destroy', $department) }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this department?')">

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

        @else

            <p class="text-gray-500">
                No Departments Found.
            </p>

        @endif

    </div>

</div>

@endsection