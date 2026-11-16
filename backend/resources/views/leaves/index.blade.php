@extends('layouts.app')

@section('title', 'Leaves')
@section('page_title', 'Leaves')

@section('content')

<div class="space-y-6">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Leaves
            </h1>

            <p class="mt-1 text-gray-500">
                Manage employee leave applications.
            </p>
        </div>


        <div class="flex flex-col gap-3 sm:flex-row">

            <form action="{{ route('leaves.index') }}" method="GET" class="flex">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search Employee..."
                    class="w-72 rounded-l-lg border border-gray-300 px-4 py-2">

                <button
                    class="rounded-r-lg bg-blue-600 px-5 text-white">

                    Search

                </button>

            </form>


            <a href="{{ route('leaves.create') }}"
               class="rounded-lg bg-green-600 px-5 py-2 text-white">

                + Apply Leave

            </a>

        </div>

    </div>



    @if(session('success'))

        <div class="rounded-lg border border-green-300 bg-green-100 px-4 py-3 text-green-700">

            {{ session('success') }}

        </div>

    @endif



    <div class="overflow-hidden rounded-xl border bg-white shadow-sm">


        @if($leaves->count())


        <table class="w-full">

            <thead class="border-b bg-gray-50">

                <tr>

                    <th class="px-6 py-4 text-left">
                        Employee
                    </th>

                    <th class="px-6 py-4 text-left">
                        Leave Type
                    </th>

                    <th class="px-6 py-4 text-left">
                        Date
                    </th>

                    <th class="px-6 py-4 text-left">
                        Days
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


            @foreach($leaves as $leave)


                <tr class="border-b hover:bg-gray-50">


                    <td class="px-6 py-4">

                        <div class="flex items-center gap-3">
                        <x-avatar :name="$leave->employee->name ?? '?'" :photo="$leave->employee->photo ?? null" size="h-9 w-9" text="text-sm" />
                        <div>
                        <div class="font-medium">
                            {{ $leave->employee->name ?? 'Deleted Employee' }}
                        </div>

                        <div class="text-sm text-gray-500">
                            {{ $leave->employee->employee_id ?? '-' }}
                        </div>
                        </div>
                        </div>

                    </td>



                    <td class="px-6 py-4">

                        {{ $leave->leaveType->name ?? '-' }}

                    </td>



                    <td class="px-6 py-4">

                        {{ $leave->from_date?->format('d M Y') }}

                        <br>

                        <span class="text-sm text-gray-500">

                            To {{ $leave->to_date?->format('d M Y') }}

                        </span>

                    </td>



                    <td class="px-6 py-4">

                        {{ $leave->total_days }}

                    </td>



                    <td class="px-6 py-4">


                        @if($leave->status == 'Approved')

                            <span class="rounded-full bg-green-100 px-3 py-1 text-green-700">
                                Approved
                            </span>


                        @elseif($leave->status == 'Rejected')

                            <span class="rounded-full bg-red-100 px-3 py-1 text-red-700">
                                Rejected
                            </span>


                        @else

                            <span class="rounded-full bg-yellow-100 px-3 py-1 text-yellow-700">
                                Pending
                            </span>


                        @endif


                    </td>




                    <td class="px-6 py-4">


                        <div class="flex justify-center gap-2">


                            <a href="{{ route('leaves.show', ['leaf' => $leave->id]) }}"
                               class="rounded-lg bg-gray-600 px-3 py-2 text-sm text-white">

                                View

                            </a>



                            <a href="{{ route('leaves.edit', ['leaf' => $leave->id]) }}"
                               class="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white">

                                Edit

                            </a>




                            @if($leave->status == 'Pending')


                                <form action="{{ route('leaves.approve', ['leave' => $leave->id]) }}"
                                      method="POST">

                                    @csrf

                                    <button
                                        class="rounded-lg bg-green-600 px-3 py-2 text-sm text-white">

                                        Approve

                                    </button>

                                </form>



                                <form action="{{ route('leaves.reject', ['leave' => $leave->id]) }}"
                                      method="POST">

                                    @csrf

                                    <button
                                        class="rounded-lg bg-orange-600 px-3 py-2 text-sm text-white">

                                        Reject

                                    </button>

                                </form>


                            @endif





                            <form action="{{ route('leaves.destroy', ['leaf' => $leave->id]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this leave?')">

                                @csrf
                                @method('DELETE')


                                <button
                                    class="rounded-lg bg-red-600 px-3 py-2 text-sm text-white">

                                    Delete

                                </button>


                            </form>



                        </div>


                    </td>


                </tr>


            @endforeach


            </tbody>


        </table>



        <div class="border-t px-6 py-4">

            {{ $leaves->links() }}

        </div>



        @else


        <div class="p-12 text-center">

            <div class="text-5xl">
                📅
            </div>


            <h2 class="mt-4 text-xl font-semibold">
                No Leave Found
            </h2>


            <p class="mt-2 text-gray-500">
                Create your first leave application.
            </p>



            <a href="{{ route('leaves.create') }}"
               class="mt-5 inline-block rounded-lg bg-green-600 px-6 py-3 text-white">

                + Apply Leave

            </a>


        </div>


        @endif


    </div>


</div>


@endsection