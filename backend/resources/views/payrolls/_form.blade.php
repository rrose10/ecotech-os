<div class="space-y-6">

    {{-- Team Member --}}
    <div>
        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
            Team Member <span class="text-red-500">*</span>
        </label>

        <select
            id="employee_id"
            name="employee_id"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

            <option value="">Select Team Member</option>

            @foreach($employees as $employee)
                <option
                    value="{{ $employee->id }}"
                    {{ old('employee_id', $payroll->employee_id ?? '') == $employee->id ? 'selected' : '' }}>

                    {{ $employee->employee_id }} - {{ $employee->name }}

                </option>
            @endforeach

        </select>

        @error('employee_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Payment Account --}}
    <div>
        <label for="bank_account_id" class="block text-sm font-medium text-gray-700 mb-2">
            Payment Account <span class="text-red-500">*</span>
        </label>

        <select
            id="bank_account_id"
            name="bank_account_id"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

            <option value="">Select Account</option>

            @foreach($bankAccounts as $account)
                <option
                    value="{{ $account->id }}"
                    {{ old('bank_account_id', $payroll->bank_account_id ?? '') == $account->id ? 'selected' : '' }}>

                    {{ $account->account_name }}
                    @if($account->type == 'Bank')
                        ({{ $account->bank_name }})
                    @endif

                </option>
            @endforeach

        </select>

        @error('bank_account_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Month --}}
    <div>
        <label for="month" class="block text-sm font-medium text-gray-700 mb-2">
            Month
        </label>

        <select
            id="month"
            name="month"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

            @foreach(range(1,12) as $month)
                <option
                    value="{{ $month }}"
                    {{ old('month', $payroll->month ?? date('n')) == $month ? 'selected' : '' }}>

                    {{ date('F', mktime(0,0,0,$month,1)) }}

                </option>
            @endforeach

        </select>
    </div>

    {{-- Year --}}
    <div>
        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">
            Year
        </label>

        <input
            type="number"
            id="year"
            name="year"
            value="{{ old('year', $payroll->year ?? date('Y')) }}"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    {{-- Basic Salary --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Basic Salary
        </label>

        <input
            type="text"
            id="basic_salary"
            readonly
            value="{{ old('basic_salary', $payroll->basic_salary ?? '0.00') }}"
            class="w-full rounded-lg bg-gray-100 border-gray-300">
    </div>

    {{-- Bonus --}}
    <div>
        <label for="bonus" class="block text-sm font-medium text-gray-700 mb-2">
            Bonus
        </label>

        <input
            type="number"
            step="0.01"
            id="bonus"
            name="bonus"
            value="{{ old('bonus', $payroll->bonus ?? 0) }}"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

        @error('bonus')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
        {{-- Gross Salary --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Gross Salary
        </label>

        <input
            type="text"
            id="gross_salary"
            readonly
            value="{{ old('total_salary', $payroll->total_salary ?? '0.00') }}"
            class="w-full rounded-lg bg-gray-100 border-gray-300">
    </div>

    {{-- Advance Deduction --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Advance Deduction
        </label>

        <input
            type="text"
            id="advance_amount"
            readonly
            value="{{ old('advance_amount', $payroll->advance_amount ?? '0.00') }}"
            class="w-full rounded-lg bg-gray-100 border-gray-300">
    </div>

    {{-- Loan Deduction --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Loan Deduction
        </label>

        <input
            type="text"
            id="loan_amount"
            readonly
            value="{{ old('loan_amount', $payroll->loan_amount ?? '0.00') }}"
            class="w-full rounded-lg bg-gray-100 border-gray-300">
    </div>

    {{-- Net Salary --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Net Salary
        </label>

        <input
            type="text"
            id="net_salary"
            readonly
            value="{{ old('net_salary', $payroll->net_salary ?? '0.00') }}"
            class="w-full rounded-lg bg-gray-100 border-gray-300">
    </div>

    {{-- Payment Date --}}
    <div>
        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">
            Payment Date
        </label>

        <input
            type="date"
            id="payment_date"
            name="payment_date"
            value="{{ old('payment_date', isset($payroll) ? optional($payroll->payment_date)->format('Y-m-d') : '') }}"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

        @error('payment_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Status --}}
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
            Status
        </label>

        <select
            id="status"
            name="status"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

            <option value="Pending"
                {{ old('status', $payroll->status ?? 'Pending') == 'Pending' ? 'selected' : '' }}>
                Pending
            </option>

            <option value="Paid"
                {{ old('status', $payroll->status ?? '') == 'Paid' ? 'selected' : '' }}>
                Paid
            </option>

        </select>

        @error('status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
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
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('remarks', $payroll->remarks ?? '') }}</textarea>

        @error('remarks')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Submit --}}
    <div class="pt-4">
        <button
            type="submit"
            class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700">

            {{ isset($payroll) ? 'Update Payroll' : 'Save Payroll' }}

        </button>
    </div>

</div>
|@push('scripts')
<script>
console.log('Payroll JS Loaded');

document.addEventListener('DOMContentLoaded', function () {

    const employee = document.getElementById('employee_id');
    const month = document.getElementById('month');
    const year = document.getElementById('year');
    const bonus = document.getElementById('bonus');

    const basicSalary = document.getElementById('basic_salary');
    const grossSalary = document.getElementById('gross_salary');
    const advanceAmount = document.getElementById('advance_amount');
    const loanAmount = document.getElementById('loan_amount');
    const netSalary = document.getElementById('net_salary');

    console.log(basicSalary);
    console.log(grossSalary);
    console.log(advanceAmount);
    console.log(loanAmount);
    console.log(netSalary);

    function calculatePayroll() {

        console.log('calculatePayroll() called');
        console.log('Employee:', employee.value);

        if (!employee.value) {
            basicSalary.value = '0.00';
            grossSalary.value = '0.00';
            advanceAmount.value = '0.00';
            loanAmount.value = '0.00';
            netSalary.value = '0.00';
            return;
        }

        const url =
            "{{ route('payrolls.calculate') }}"
            + "?employee_id=" + employee.value
            + "&month=" + month.value
            + "&year=" + year.value
            + "&bonus=" + (bonus.value || 0);

        console.log("URL:", url);

        fetch(url)
            .then(response => {
                console.log("Status:", response.status);
                return response.json();
            })
            .then(data => {

                console.log("Response:", data);

                basicSalary.value = parseFloat(data.basic_salary || 0).toFixed(2);
                grossSalary.value = parseFloat(data.gross_salary || 0).toFixed(2);
                advanceAmount.value = parseFloat(data.advance_amount || 0).toFixed(2);
                loanAmount.value = parseFloat(data.loan_amount || 0).toFixed(2);
                netSalary.value = parseFloat(data.net_salary || 0).toFixed(2);

                console.log("Basic after:", basicSalary.value);
                console.log("Gross after:", grossSalary.value);
                console.log("Advance after:", advanceAmount.value);
                console.log("Loan after:", loanAmount.value);
                console.log("Net after:", netSalary.value);

            })
            .catch(error => {
                console.error("Payroll Calculator Error:", error);
            });
    }

    employee.addEventListener('change', calculatePayroll);
    month.addEventListener('change', calculatePayroll);
    year.addEventListener('change', calculatePayroll);
    bonus.addEventListener('input', calculatePayroll);

    calculatePayroll();

});
</script>
@endpush