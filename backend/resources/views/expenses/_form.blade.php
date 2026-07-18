<div class="space-y-6">

    {{-- Expense Category --}}
    <div>
        <label class="block mb-2 font-medium">
            Expense Category
        </label>

        <select
            name="expense_category_id"
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

            <option value="">Select Category</option>

            @foreach($categories as $category)
                <option
                    value="{{ $category->id }}"
                    {{ old('expense_category_id', $expense->expense_category_id ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach

        </select>

        @error('expense_category_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Bank Account --}}
    <div>
        <label class="block mb-2 font-medium">
            Bank Account
        </label>

        <select
            name="bank_account_id"
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

            <option value="">Select Bank Account</option>

            @foreach($bankAccounts as $bank)
                <option
                    value="{{ $bank->id }}"
                    {{ old('bank_account_id', $expense->bank_account_id ?? '') == $bank->id ? 'selected' : '' }}>
                    {{ $bank->account_name }}
                    ({{ number_format($bank->current_balance, 2) }})
                </option>
            @endforeach

        </select>

        @error('bank_account_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Title --}}
    <div>
        <label class="block mb-2 font-medium">
            Title
        </label>

        <input
            type="text"
            name="title"
            value="{{ old('title', $expense->title ?? '') }}"
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

        @error('title')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Amount --}}
    <div>
        <label class="block mb-2 font-medium">
            Amount
        </label>

        <input
            type="number"
            step="0.01"
            min="0.01"
            name="amount"
            value="{{ old('amount', $expense->amount ?? '') }}"
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

        @error('amount')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Expense Date --}}
    <div>
        <label class="block mb-2 font-medium">
            Expense Date
        </label>

        <input
            type="date"
            name="expense_date"
            value="{{ old('expense_date', $expense->expense_date ?? now()->format('Y-m-d')) }}"
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

        @error('expense_date')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Payment Method --}}
    <div>
        <label class="block mb-2 font-medium">
            Payment Method
        </label>

        <select
            name="payment_method"
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

            <option value="">Select Payment Method</option>

            @foreach(['Cash','Bank','bKash','Nagad','Rocket','Card'] as $method)
                <option
                    value="{{ $method }}"
                    {{ old('payment_method', $expense->payment_method ?? '') == $method ? 'selected' : '' }}>
                    {{ $method }}
                </option>
            @endforeach

        </select>

        @error('payment_method')
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
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">{{ old('remarks', $expense->remarks ?? '') }}</textarea>

        @error('remarks')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Submit --}}
    <div>
        <button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">

            {{ isset($expense) ? 'Update Expense' : 'Save Expense' }}

        </button>
    </div>

</div>