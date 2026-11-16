<div class="space-y-6">

    {{-- Team Member --}}
    <div>
        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
            Team Member <span class="text-red-500">*</span>
        </label>

        <select
            id="employee_id"
            name="employee_id"
            required
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

            <option value="">Select Team Member</option>

            @foreach($employees as $employee)
                <option
                    value="{{ $employee->id }}"
                    {{ old('employee_id', $advanceSalary->employee_id ?? '') == $employee->id ? 'selected' : '' }}>

                    {{ $employee->employee_id }} - {{ $employee->name }}

                </option>
            @endforeach

        </select>
    </div>

    {{-- Payment Account --}}
    <div>
        <label for="bank_account_id" class="block text-sm font-medium text-gray-700 mb-2">
            Payment Account <span class="text-red-500">*</span>
        </label>

        <select
            id="bank_account_id"
            name="bank_account_id"
            required
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

            <option value="">Select Account</option>

            @foreach($bankAccounts as $account)
                <option
                    value="{{ $account->id }}"
                    {{ old('bank_account_id', $advanceSalary->bank_account_id ?? '') == $account->id ? 'selected' : '' }}>

                    {{ $account->account_name }}

                    @if($account->type == 'Bank')
                        ({{ $account->bank_name }})
                    @endif

                </option>
            @endforeach

        </select>
    </div>

    {{-- Amount --}}
    <div>
        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
            Advance Amount <span class="text-red-500">*</span>
        </label>

        <input
            type="number"
            step="0.01"
            min="1"
            id="amount"
            name="amount"
            required
            value="{{ old('amount', $advanceSalary->amount ?? '') }}"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    {{-- Advance Date --}}
    <div>
        <label for="advance_date" class="block text-sm font-medium text-gray-700 mb-2">
            Advance Date <span class="text-red-500">*</span>
        </label>

        <input
            type="date"
            id="advance_date"
            name="advance_date"
            required
            value="{{ old('advance_date', isset($advanceSalary) ? optional($advanceSalary->advance_date)->format('Y-m-d') : date('Y-m-d')) }}"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    {{-- Deduction Month --}}
    <div>
        <label for="deduction_month" class="block text-sm font-medium text-gray-700 mb-2">
            Deduction Month <span class="text-red-500">*</span>
        </label>

        <select
            id="deduction_month"
            name="deduction_month"
            required
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

            @foreach(range(1,12) as $month)
                <option
                    value="{{ $month }}"
                    {{ old('deduction_month', $advanceSalary->deduction_month ?? date('n')) == $month ? 'selected' : '' }}>

                    {{ date('F', mktime(0,0,0,$month,1)) }}

                </option>
            @endforeach

        </select>
    </div>

    {{-- Deduction Year --}}
    <div>
        <label for="deduction_year" class="block text-sm font-medium text-gray-700 mb-2">
            Deduction Year <span class="text-red-500">*</span>
        </label>

        <input
            type="number"
            id="deduction_year"
            name="deduction_year"
            required
            min="2024"
            max="2100"
            value="{{ old('deduction_year', $advanceSalary->deduction_year ?? date('Y')) }}"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    {{-- Remarks --}}
    <div>
        <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">
            Remarks
        </label>

        <textarea
            id="remarks"
            name="remarks"
            rows="4"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('remarks', $advanceSalary->remarks ?? '') }}</textarea>
    </div>

    {{-- Submit --}}
    <div class="flex items-center gap-3 pt-4">

        <button
            type="submit"
            class="rounded-lg bg-blue-600 px-6 py-3 text-white font-medium hover:bg-blue-700">

            {{ isset($advanceSalary) ? 'Update Advance Salary' : 'Save Advance Salary' }}

        </button>

        <a
            href="{{ route('advance-salaries.index') }}"
            class="rounded-lg border px-6 py-3 hover:bg-gray-100">

            Cancel

        </a>

    </div>

</div>