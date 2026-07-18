<div class="mb-4">

    <label class="block mb-2 font-medium">
        Category Name
    </label>

    <input
        type="text"
        name="name"
        value="{{ old('name', $expenseCategory->name ?? '') }}"
        class="w-full border rounded px-4 py-2 @error('name') border-red-500 @enderror"
        required>

    @error('name')
        <p class="text-red-500 text-sm mt-1">
            {{ $message }}
        </p>
    @enderror

</div>

<div class="mb-4">

    <label class="block mb-2 font-medium">
        Status
    </label>

    <select
        name="status"
        class="w-full border rounded px-4 py-2">

        <option value="1"
            {{ old('status', $expenseCategory->status ?? 1) == 1 ? 'selected' : '' }}>
            Active
        </option>

        <option value="0"
            {{ old('status', $expenseCategory->status ?? 1) == 0 ? 'selected' : '' }}>
            Inactive
        </option>

    </select>

</div>