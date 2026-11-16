@extends('layouts.portal')

@section('title', __('Salary'))

@section('page_title', __('Salary'))

@section('content')

<div class="space-y-6">

    <!-- Current Salary -->

    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-xl font-bold text-slate-900">
                {{ __("Current Salary") }}
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 p-6">

            <div>
                <p class="text-sm text-slate-500">
                    {{ __("Basic Salary") }}
                </p>

                <p class="mt-2 text-lg font-bold text-slate-900">
                    ৳ {{ number_format($latestPayroll?->basic_salary ?? $employee?->basic_salary ?? 0, 2) }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">
                    {{ __("Bonus") }}
                </p>

                <p class="mt-2 text-lg font-bold text-green-600">
                    ৳ {{ number_format($latestPayroll?->bonus ?? 0, 2) }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">
                    {{ __("Total Salary") }}
                </p>

                <p class="mt-2 text-lg font-bold text-indigo-600">
                    ৳ {{ number_format($latestPayroll?->total_salary ?? $employee?->basic_salary ?? 0, 2) }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">
                    {{ __("Payment Date") }}
                </p>

                <p class="mt-2 font-semibold text-slate-900">
                    {{ $latestPayroll?->payment_date?->format('d M Y') ?? '--' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">
                    {{ __("Status") }}
                </p>

                @if(($latestPayroll?->status ?? '') === 'Paid')

                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700">
                        {{ __("Paid") }}
                    </span>

                @elseif(($latestPayroll?->status ?? '') === 'Pending')

                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-700">
                        {{ __("Pending") }}
                    </span>

                @else

                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">
                        --
                    </span>

                @endif

            </div>

        </div>

    </div>

    <!-- Salary History -->

    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-xl font-bold text-slate-900">
                {{ __("Salary History") }}
            </h2>
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-slate-50">

                    <tr>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            {{ __("Month") }}
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            {{ __("Year") }}
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            {{ __("Basic") }}
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            {{ __("Bonus") }}
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            {{ __("Total") }}
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            {{ __("Payment Date") }}
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            {{ __("Status") }}
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            {{ __("Payslip") }}
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-200">

                    @forelse($payrolls as $payroll)

                        <tr>

                            <td class="px-6 py-4">
                                {{ \Carbon\Carbon::create()->month($payroll->month)->format('F') }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $payroll->year }}
                            </td>

                            <td class="px-6 py-4">
                                ৳ {{ number_format($payroll->basic_salary, 2) }}
                            </td>

                            <td class="px-6 py-4">
                                ৳ {{ number_format($payroll->bonus, 2) }}
                            </td>

                            <td class="px-6 py-4 font-semibold">
                                ৳ {{ number_format($payroll->total_salary, 2) }}
                            </td>

                            <td class="px-6 py-4">
                                {{ optional($payroll->payment_date)->format('d M Y') ?? '--' }}
                            </td>

                            <td class="px-6 py-4">

                                @if($payroll->status == 'Paid')

                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                        {{ __("Paid") }}
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                        {{ __("Pending") }}
                                    </span>

                                @endif

                            </td>

                            <td class="px-6 py-4">
                                <a href="{{ route('portal.payslip', $payroll) }}" target="_blank" rel="noopener"
                                   class="font-semibold text-indigo-600 hover:text-indigo-700">{{ __("View") }}</a>
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                                {{ __("No salary records found.") }}
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if(method_exists($payrolls, 'links'))

            <div class="border-t border-slate-200 px-6 py-4">

                {{ $payrolls->links() }}

            </div>

        @endif

    </div>

</div>

@endsection