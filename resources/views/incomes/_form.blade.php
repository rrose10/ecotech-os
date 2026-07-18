<div class="space-y-6">

    {{-- Income Category --}}
    <div>
        <label class="block mb-2 font-medium">Income Category</label>

        <select
            name="income_category_id"
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

            <option value="">Select Category</option>

            @foreach($incomeCategories as $category)
                <option
                    value="{{ $category->id }}"
                    {{ old('income_category_id', $income->income_category_id ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach

        </select>

        @error('income_category_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Bank Account --}}
    <div>
        <label class="block mb-2 font-medium">Account</label>

        <select
            name="bank_account_id"
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

            <option value="">Select Account</option>

            @foreach($bankAccounts as $account)
                <option
                    value="{{ $account->id }}"
                    {{ old('bank_account_id', $income->bank_account_id ?? '') == $account->id ? 'selected' : '' }}>
                    {{ $account->account_name }}
                    ({{ $account->type }})
                </option>
            @endforeach

        </select>

        @error('bank_account_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Title --}}
    <div>
        <label class="block mb-2 font-medium">Title</label>

        <input
            type="text"
            name="title"
            value="{{ old('title', $income->title ?? '') }}"
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

        @error('title')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Amounts --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div>
            <label class="block mb-2 font-medium">Amount (BDT)</label>

            <input
                type="number"
                step="0.01"
                min="0"
                name="amount_bdt"
                value="{{ old('amount_bdt', $income->amount_bdt ?? '') }}"
                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

            @error('amount_bdt')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-2 font-medium">Amount (USD)</label>

            <input
                type="number"
                step="0.01"
                min="0"
                name="amount_usd"
                value="{{ old('amount_usd', $income->amount_usd ?? '') }}"
                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

            @error('amount_usd')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- Income Date & Payment --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div>
            <label class="block mb-2 font-medium">Income Date</label>

            <input
                type="date"
                name="income_date"
                value="{{ old('income_date', $income->income_date ?? date('Y-m-d')) }}"
                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

            @error('income_date')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-2 font-medium">Payment Method</label>

            <select
                name="payment_method"
                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

                <option value="">Select Payment Method</option>

                @foreach(['Cash','Bank','bKash','Nagad','Rocket','Card'] as $method)
                    <option
                        value="{{ $method }}"
                        {{ old('payment_method', $income->payment_method ?? '') == $method ? 'selected' : '' }}>
                        {{ $method }}
                    </option>
                @endforeach

            </select>

            @error('payment_method')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- Remarks --}}
    <div>
        <label class="block mb-2 font-medium">Remarks</label>

        <textarea
            name="remarks"
            rows="4"
            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">{{ old('remarks', $income->remarks ?? '') }}</textarea>

        @error('remarks')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Submit --}}
    <div>
        <button
            type="submit"
            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">

            {{ isset($income) ? 'Update Income' : 'Save Income' }}

        </button>
    </div>

</div>