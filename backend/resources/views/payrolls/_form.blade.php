<div class="space-y-6">

    {{-- Employee --}}
    <div>
        <label class="block mb-2 font-medium">
            Employee
        </label>

        <select
            id="employee_id"
            name="employee_id"
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

            <option value="">Select Employee</option>

            @foreach($employees as $employee)
                <option
                    value="{{ $employee->id }}"
                    data-salary="{{ $employee->basic_salary }}"
                    {{ old('employee_id', $payroll->employee_id ?? '') == $employee->id ? 'selected' : '' }}>
                    {{ $employee->employee_id }} - {{ $employee->name }}
                </option>
            @endforeach

        </select>

        @error('employee_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Bank Account --}}
    <div>
        <label class="block mb-2 font-medium">
            Payment Account
        </label>

        <select
            name="bank_account_id"
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

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
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Month --}}
    <div>
        <label class="block mb-2 font-medium">
            Month
        </label>

        <select
            name="month"
            class="w-full border rounded px-4 py-2">

            @foreach(range(1,12) as $month)
                <option
                    value="{{ $month }}"
                    {{ old('month', $payroll->month ?? date('n')) == $month ? 'selected' : '' }}>
                    {{ date('F', mktime(0,0,0,$month,1)) }}
                </option>
            @endforeach

        </select>

        @error('month')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Year --}}
    <div>
        <label class="block mb-2 font-medium">
            Year
        </label>

        <input
            type="number"
            name="year"
            value="{{ old('year', $payroll->year ?? date('Y')) }}"
            class="w-full border rounded px-4 py-2">

        @error('year')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Basic Salary --}}
    <div>
        <label class="block mb-2 font-medium">
            Basic Salary
        </label>

        <input
            type="number"
            id="basic_salary"
            class="w-full border rounded px-4 py-2 bg-gray-100"
            value="{{ old('basic_salary', $payroll->basic_salary ?? 0) }}"
            readonly
            tabindex="-1">
    </div>

    {{-- Bonus --}}
    <div>
        <label class="block mb-2 font-medium">
            Bonus
        </label>

        <input
            type="number"
            step="0.01"
            id="bonus"
            name="bonus"
            value="{{ old('bonus', $payroll->bonus ?? 0) }}"
            class="w-full border rounded px-4 py-2">

        @error('bonus')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Total Salary --}}
    <div>
        <label class="block mb-2 font-medium">
            Total Salary
        </label>

        <input
            type="number"
            id="total_salary"
            class="w-full border rounded px-4 py-2 bg-gray-100"
            value="{{ old('total_salary', $payroll->total_salary ?? 0) }}"
            readonly
            tabindex="-1">
    </div>

    {{-- Payment Date --}}
    <div>
        <label class="block mb-2 font-medium">
            Payment Date
        </label>

        <input
            type="date"
            name="payment_date"
            value="{{ old('payment_date', isset($payroll) ? optional($payroll->payment_date)->format('Y-m-d') : '') }}"
            class="w-full border rounded px-4 py-2">

        @error('payment_date')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Status --}}
    <div>
        <label class="block mb-2 font-medium">
            Status
        </label>

        <select
            name="status"
            class="w-full border rounded px-4 py-2">

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
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Remarks --}}
    <div>
        <label class="block mb-2 font-medium">
            Remarks
        </label>

        <textarea
            name="remarks"
            rows="4"
            class="w-full border rounded px-4 py-2">{{ old('remarks', $payroll->remarks ?? '') }}</textarea>

        @error('remarks')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Submit --}}
    <div>
        <button
            type="submit"
            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">

            {{ isset($payroll) ? 'Update Payroll' : 'Save Payroll' }}

        </button>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const employee = document.getElementById('employee_id');
    const basicSalary = document.getElementById('basic_salary');
    const bonus = document.getElementById('bonus');
    const totalSalary = document.getElementById('total_salary');

    function calculateTotal() {
        let basic = parseFloat(basicSalary.value) || 0;
        let extra = parseFloat(bonus.value) || 0;

        totalSalary.value = (basic + extra).toFixed(2);
    }

    function updateSalary() {
        const option = employee.options[employee.selectedIndex];

        if (option && option.dataset.salary) {
            basicSalary.value = option.dataset.salary;
        } else {
            basicSalary.value = 0;
        }

        calculateTotal();
    }

    employee.addEventListener('change', updateSalary);
    bonus.addEventListener('input', calculateTotal);

    updateSalary();

});
</script>