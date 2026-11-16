@extends('layouts.app')

@section('title', 'Team Leave Balance')
@section('page_title', 'Team Leave Balance')

@section('content')


<div class="max-w-4xl mx-auto space-y-6">


    <div class="rounded-xl bg-white p-6 shadow border">


        <h2 class="text-2xl font-bold text-gray-800">

            {{ $employee->name }}

        </h2>


        <p class="text-gray-500">

            Member ID:
            {{ $employee->employee_id }}

        </p>


        <p class="text-gray-500">

            Department:
            {{ $employee->department->name ?? '-' }}

        </p>


    </div>





    <div class="rounded-xl bg-white shadow border overflow-hidden">


        <table class="w-full">


            <thead class="bg-gray-50 border-b">


                <tr>

                    <th class="px-6 py-4 text-left">
                        Leave Type
                    </th>


                    <th class="px-6 py-4 text-center">
                        Total Days
                    </th>


                    <th class="px-6 py-4 text-center">
                        Used Days
                    </th>


                    <th class="px-6 py-4 text-center">
                        Remaining
                    </th>


                </tr>


            </thead>




            <tbody>


            @forelse($balances as $balance)


                <tr class="border-b">


                    <td class="px-6 py-4 font-semibold">

                        {{ $balance->leaveType->name }}

                    </td>



                    <td class="px-6 py-4 text-center">

                        {{ $balance->total_days }}

                    </td>



                    <td class="px-6 py-4 text-center text-red-600">

                        {{ $balance->used_days }}

                    </td>



                    <td class="px-6 py-4 text-center text-green-600 font-bold">

                        {{ $balance->remaining_days }}

                    </td>



                </tr>


            @empty


                <tr>

                    <td colspan="4"
                        class="p-10 text-center text-gray-500">

                        No leave balance found.

                    </td>

                </tr>


            @endforelse



            </tbody>


        </table>


    </div>




    <a href="{{ route('leave-balances.index') }}"
       class="inline-block rounded-lg bg-gray-600 px-5 py-2 text-white">

        Back

    </a>



</div>


@endsection