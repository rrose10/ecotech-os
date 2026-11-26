@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <div>
        <label class="block mb-2 font-medium">From account</label>
        <select name="from_account_id" class="w-full rounded-lg border-gray-300" required>
            <option value="">Select account</option>
            @foreach($accounts as $a)
                <option value="{{ $a->id }}" @selected(old('from_account_id', $transfer->from_account_id ?? '') == $a->id)>
                    {{ $a->account_name }} ({{ $a->type }}) - balance {{ number_format($a->current_balance, 2) }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block mb-2 font-medium">To account</label>
        <select name="to_account_id" class="w-full rounded-lg border-gray-300" required>
            <option value="">Select account</option>
            @foreach($accounts as $a)
                <option value="{{ $a->id }}" @selected(old('to_account_id', $transfer->to_account_id ?? '') == $a->id)>
                    {{ $a->account_name }} ({{ $a->type }}) - balance {{ number_format($a->current_balance, 2) }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block mb-2 font-medium">Amount</label>
        <input type="number" step="0.01" min="0.01" name="amount"
               value="{{ old('amount', isset($transfer) ? $transfer->amount : '') }}"
               class="w-full rounded-lg border-gray-300" required>
    </div>

    <div>
        <label class="block mb-2 font-medium">Date</label>
        <input type="date" name="transfer_date"
               value="{{ old('transfer_date', isset($transfer) ? $transfer->transfer_date->toDateString() : now()->toDateString()) }}"
               class="w-full rounded-lg border-gray-300" required>
    </div>

    <div class="md:col-span-2">
        <label class="block mb-2 font-medium">Note (optional)</label>
        <input type="text" name="remarks" maxlength="255"
               value="{{ old('remarks', $transfer->remarks ?? '') }}"
               placeholder="e.g. Cash for monthly small expenses"
               class="w-full rounded-lg border-gray-300">
    </div>

</div>

<p class="mt-4 text-sm text-gray-500">
    A transfer only moves money between your own accounts. It is not counted as income or expense.
</p>

<div class="mt-6 flex gap-3">
    <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Save transfer</button>
    <a href="{{ route('fund-transfers.index') }}" class="border px-6 py-2 rounded-lg">Cancel</a>
</div>
