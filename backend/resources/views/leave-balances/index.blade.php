@extends('layouts.app')

@section('title', 'Leave Balance')
@section('page_title', 'Leave Balance')

@section('content')

<div class="space-y-6">


    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Leave Balance
            </h1>

            <p class="mt-1 text-gray-500">
                View team leave balance.
            </p>
        </div>


        <form action="{{ route('leave-balances.index') }}"
              method="GET"
              class="flex">


            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search team member..."
                class="w-72 rounded-l-lg border border-gray-300 px-4 py-2">


            <button
                class="rounded-r-lg bg-blue-600 px-5 text-white">

                Search

            </button>


        </form>


    </div>





    <div class="overflow-hidden rounded-xl border bg-white shadow-sm">


        <table class="w-full">


            <thead class="border-b bg-gray-50">

                <tr>

                    <th class="px-6 py-4 text-left">
                        Team Member
                    </th>


                    <th class="px-6 py-4 text-left">
                        Member ID
                    </th>


                    <th class="px-6 py-4 text-left">
                        Department
                    </th>


                    <th class="px-6 py-4 text-center">
                        Action
                    </th>


                </tr>


            </thead>




            <tbody>


            @forelse($employees as $employee)


                <tr class="border-b hover:bg-gray-50">


                    <td class="px-6 py-4">

                        <div class="font-semibold">
                            {{ $employee->name }}
                        </div>

                    </td>



                    <td class="px-6 py-4">

                        {{ $employee->employee_id }}

                    </td>




                    <td class="px-6 py-4">

                        {{ $employee->department->name ?? '-' }}

                    </td>




                    <td class="px-6 py-4 text-center">


                        <a href="{{ route('leave-balances.show', ['employee'=>$employee->id]) }}"
                           class="rounded-lg bg-blue-600 px-4 py-2 text-white">

                            View Balance

                        </a>


                    </td>



                </tr>


            @empty


                <tr>

                    <td colspan="4"
                        class="p-10 text-center text-gray-500">

                        No team member found.

                    </td>

                </tr>


            @endforelse



            </tbody>


        </table>



        <div class="border-t px-6 py-4">

            {{ $employees->links() }}

        </div>


    </div>


</div>


@endsection