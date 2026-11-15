<form method="GET" class="mb-6 flex flex-wrap items-center gap-2">
    <select name="month" class="border rounded p-2">
        @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" @selected($month === $m)>{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
        @endfor
    </select>
    <input type="number" name="year" value="{{ $year }}" min="2000" max="2100" class="border rounded p-2 w-28">
    <button class="bg-green-600 text-white px-4 py-2 rounded">Show</button>
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="border px-4 py-2 rounded">Download CSV</a>
</form>
