@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Employee --}}
    <div>
        <label class="block mb-2 font-medium text-gray-700">
            Employee <span class="text-red-500">*</span>
        </label>

        <select
            name="employee_id"
            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
            required>

            <option value="">Select Employee</option>

            @foreach($employees as $employee)
                <option
                    value="{{ $employee->id }}"
                    @selected(old('employee_id', $employeeLoan->employee_id ?? '') == $employee->id)>
                    {{ $employee->employee_id }} - {{ $employee->name }}
                </option>
            @endforeach

        </select>

        @error('employee_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Payment Account --}}
    <div>

        <label class="block mb-2 font-medium text-gray-700">
            Payment Account <span class="text-red-500">*</span>
        </label>

        <select
            name="bank_account_id"
            class="w-full rounded-lg border-gray-300"
            required>

            <option value="">Select Account</option>

            @foreach($bankAccounts as $bank)

                <option
                    value="{{ $bank->id }}"
                    @selected(old('bank_account_id', $employeeLoan->bank_account_id ?? '') == $bank->id)>

                    {{ $bank->account_name }}
                    (Balance: {{ number_format($bank->current_balance,2) }})

                </option>

            @endforeach

        </select>

        @error('bank_account_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror

    </div>

    {{-- Loan Amount --}}
    <div>

        <label class="block mb-2 font-medium text-gray-700">
            Loan Amount
        </label>

        <input
            type="number"
            id="loan_amount"
            name="loan_amount"
            step="0.01"
            value="{{ old('loan_amount', $employeeLoan->loan_amount ?? '') }}"
            class="w-full rounded-lg border-gray-300"
            required>

        @error('loan_amount')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror

    </div>

    {{-- Monthly Installment --}}
    <div>

        <label class="block mb-2 font-medium text-gray-700">
            Monthly Installment
        </label>

        <input
            type="number"
            id="monthly_installment"
            name="monthly_installment"
            step="0.01"
            value="{{ old('monthly_installment', $employeeLoan->monthly_installment ?? '') }}"
            class="w-full rounded-lg border-gray-300 bg-gray-100"
            readonly>

        @error('monthly_installment')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror

    </div>

    {{-- Duration --}}
    <div>

        <label class="block mb-2 font-medium text-gray-700">
            Duration (Months)
        </label>

        <input
            type="number"
            id="total_installments"
            name="total_installments"
            min="1"
            value="{{ old('total_installments', $employeeLoan->total_installments ?? '') }}"
            class="w-full rounded-lg border-gray-300"
            required>

        @error('total_installments')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror

    </div>
        {{-- Loan Date --}}
    <div>

        <label class="block mb-2 font-medium text-gray-700">
            Loan Date
        </label>

        <input
            type="date"
            name="loan_date"
            value="{{ old('loan_date', isset($employeeLoan) ? $employeeLoan->loan_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
            class="w-full rounded-lg border-gray-300"
            required>

    </div>

    {{-- Start Month --}}
    <div>

        <label class="block mb-2 font-medium text-gray-700">
            Start Month
        </label>

        <select
            id="start_month"
            name="start_month"
            class="w-full rounded-lg border-gray-300"
            required>

            @for($i = 1; $i <= 12; $i++)
                <option
                    value="{{ $i }}"
                    @selected(old('start_month', $employeeLoan->start_month ?? date('n')) == $i)>
                    {{ date('F', mktime(0,0,0,$i,1)) }}
                </option>
            @endfor

        </select>

    </div>

    {{-- Start Year --}}
    <div>

        <label class="block mb-2 font-medium text-gray-700">
            Start Year
        </label>

        <input
            type="number"
            id="start_year"
            name="start_year"
            value="{{ old('start_year', $employeeLoan->start_year ?? date('Y')) }}"
            class="w-full rounded-lg border-gray-300"
            required>

    </div>

    {{-- End Month --}}
    <div>

        <label class="block mb-2 font-medium text-gray-700">
            End Month
        </label>

        <input
            type="text"
            id="end_month"
            class="w-full rounded-lg border-gray-300 bg-gray-100"
            readonly>

        <input
            type="hidden"
            name="end_month"
            id="end_month_value"
            value="{{ old('end_month', $employeeLoan->end_month ?? '') }}">

    </div>

    {{-- End Year --}}
    <div>

        <label class="block mb-2 font-medium text-gray-700">
            End Year
        </label>

        <input
            type="text"
            id="end_year"
            class="w-full rounded-lg border-gray-300 bg-gray-100"
            readonly>

        <input
            type="hidden"
            name="end_year"
            id="end_year_value"
            value="{{ old('end_year', $employeeLoan->end_year ?? '') }}">

    </div>

    {{-- Remaining Balance --}}
    <div>

        <label class="block mb-2 font-medium text-gray-700">
            Remaining Balance
        </label>

        <input
            type="number"
            id="remaining_balance"
            name="remaining_balance"
            value="{{ old('remaining_balance', $employeeLoan->remaining_balance ?? '') }}"
            class="w-full rounded-lg border-gray-300 bg-gray-100"
            readonly>

    </div>

    {{-- Status --}}
    @isset($employeeLoan)

    <div>

        <label class="block mb-2 font-medium text-gray-700">
            Status
        </label>

        <select
            name="status"
            class="w-full rounded-lg border-gray-300">

            <option value="Active"
                @selected(($employeeLoan->status ?? '') == 'Active')>
                Active
            </option>

            <option value="Closed"
                @selected(($employeeLoan->status ?? '') == 'Closed')>
                Closed
            </option>

        </select>

    </div>

    @endisset

</div>

{{-- Remarks --}}
<div class="mt-6">

    <label class="block mb-2 font-medium text-gray-700">
        Remarks
    </label>

    <textarea
        name="remarks"
        rows="4"
        class="w-full rounded-lg border-gray-300">{{ old('remarks', $employeeLoan->remarks ?? '') }}</textarea>

</div>

<div class="mt-8 flex justify-end">

    <button
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">

        {{ isset($employeeLoan) ? 'Update Loan' : 'Save Loan' }}

    </button>

</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const loanAmount = document.getElementById('loan_amount');
    const duration = document.getElementById('total_installments');
    const installment = document.getElementById('monthly_installment');

    const startMonth = document.getElementById('start_month');
    const startYear = document.getElementById('start_year');

    const endMonthText = document.getElementById('end_month');
    const endYearText = document.getElementById('end_year');

    const endMonthValue = document.getElementById('end_month_value');
    const endYearValue = document.getElementById('end_year_value');

    const remainingBalance = document.getElementById('remaining_balance');

    const monthNames = [
        "January","February","March","April","May","June",
        "July","August","September","October","November","December"
    ];

    function calculateLoan() {

        const loan = parseFloat(loanAmount.value) || 0;
        const months = parseInt(duration.value) || 0;

        // Monthly Installment
        if (loan > 0 && months > 0) {
            installment.value = (loan / months).toFixed(2);
        } else {
            installment.value = '';
        }

        // Remaining Balance
        remainingBalance.value = loan > 0 ? loan.toFixed(2) : '';

        // End Month & Year
        if (months > 0 && startMonth.value && startYear.value) {

            let month = parseInt(startMonth.value);
            let year = parseInt(startYear.value);

            let total = month + months - 1;

            let endMonth = ((total - 1) % 12) + 1;
            let endYear = year + Math.floor((total - 1) / 12);

            endMonthText.value = monthNames[endMonth - 1];
            endYearText.value = endYear;

            endMonthValue.value = endMonth;
            endYearValue.value = endYear;

        } else {

            endMonthText.value = '';
            endYearText.value = '';

            endMonthValue.value = '';
            endYearValue.value = '';
        }
    }

    loanAmount.addEventListener('input', calculateLoan);
    duration.addEventListener('input', calculateLoan);
    startMonth.addEventListener('change', calculateLoan);
    startYear.addEventListener('input', calculateLoan);

    calculateLoan();
});
</script>
@endpush