<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Account Name --}}
    <div>
        <label class="block mb-2 font-medium">
            Account Name <span class="text-red-500">*</span>
        </label>

        <input
            type="text"
            name="account_name"
            value="{{ old('account_name', $bankAccount->account_name ?? '') }}"
            class="w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200"
            placeholder="Office Bank"
            required>

        @error('account_name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Account Type --}}
    <div>
        <label class="block mb-2 font-medium">
            Account Type <span class="text-red-500">*</span>
        </label>

        <select
            name="type"
            id="type"
            class="w-full border rounded-lg px-4 py-2"
            required>

            <option value="">Select Type</option>

            <option value="Cash"
                {{ old('type', $bankAccount->type ?? '') == 'Cash' ? 'selected' : '' }}>
                Cash
            </option>

            <option value="Bank"
                {{ old('type', $bankAccount->type ?? '') == 'Bank' ? 'selected' : '' }}>
                Bank
            </option>

        </select>

        @error('type')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Bank Name --}}
    <div id="bank_name_div">

        <label class="block mb-2 font-medium">
            Bank Name
        </label>

        <input
            type="text"
            name="bank_name"
            value="{{ old('bank_name', $bankAccount->bank_name ?? '') }}"
            class="w-full border rounded-lg px-4 py-2"
            placeholder="BRAC Bank">

    </div>

    {{-- Account Number --}}
    <div id="account_number_div">

        <label class="block mb-2 font-medium">
            Account Number
        </label>

        <input
            type="text"
            name="account_number"
            value="{{ old('account_number', $bankAccount->account_number ?? '') }}"
            class="w-full border rounded-lg px-4 py-2"
            placeholder="1234567890">

    </div>

    {{-- Opening Balance --}}
    <div>

        <label class="block mb-2 font-medium">
            Opening Balance <span class="text-red-500">*</span>
        </label>

        <input
            type="number"
            step="0.01"
            min="0"
            name="opening_balance"
            value="{{ old('opening_balance', $bankAccount->opening_balance ?? 0) }}"
            class="w-full border rounded-lg px-4 py-2"
            required>

        @error('opening_balance')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror

    </div>

    {{-- Current Balance --}}
    <div>

        <label class="block mb-2 font-medium">
            Current Balance
        </label>

        <input
            type="text"
            value="৳ {{ number_format($bankAccount->current_balance ?? 0, 2) }}"
            class="w-full border rounded-lg px-4 py-2 bg-gray-100"
            readonly>

        <p class="text-xs text-gray-500 mt-1">
            Current balance is updated automatically.
        </p>

    </div>

    {{-- Status --}}
    <div>

        <label class="block mb-2 font-medium">
            Status
        </label>

        <select
            name="is_active"
            class="w-full border rounded-lg px-4 py-2">

            <option value="1"
                {{ old('is_active', $bankAccount->is_active ?? 1) == 1 ? 'selected' : '' }}>
                Active
            </option>

            <option value="0"
                {{ old('is_active', $bankAccount->is_active ?? 1) == 0 ? 'selected' : '' }}>
                Inactive
            </option>

        </select>

    </div>

</div>

{{-- Notes --}}
<div class="mt-6">

    <label class="block mb-2 font-medium">
        Notes
    </label>

    <textarea
        name="notes"
        rows="4"
        class="w-full border rounded-lg px-4 py-2"
        placeholder="Optional...">{{ old('notes', $bankAccount->notes ?? '') }}</textarea>

</div>

<div class="mt-8 flex gap-3">

    <button
        type="submit"
        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">

        {{ isset($bankAccount) ? 'Update Account' : 'Save Account' }}

    </button>

    <a href="{{ route('bank-accounts.index') }}"
       class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">

        Cancel

    </a>

</div>

<script>

const type = document.getElementById('type');
const bankName = document.getElementById('bank_name_div');
const accountNumber = document.getElementById('account_number_div');

function toggleBankFields() {

    if (type.value === 'Cash') {

        bankName.style.display = 'none';
        accountNumber.style.display = 'none';

    } else {

        bankName.style.display = 'block';
        accountNumber.style.display = 'block';

    }

}

toggleBankFields();

type.addEventListener('change', toggleBankFields);

</script>