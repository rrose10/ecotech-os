@php
    $chart = $monthlyChart ?? [];
    $max = max(1, collect($chart)->flatMap(fn ($m) => [$m['income'], $m['expense']])->max() ?? 1);
    $hasData = collect($chart)->contains(fn ($m) => $m['income'] > 0 || $m['expense'] > 0);
@endphp

<div class="rounded-xl bg-white shadow-lg" id="finance-chart">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b px-6 py-4">
        <h2 class="text-xl font-bold">Income vs expense</h2>
        <div class="flex items-center gap-4 text-sm">
            <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded-sm bg-emerald-500"></span> Income (BDT)</span>
            <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded-sm bg-rose-500"></span> Expense</span>
        </div>
    </div>

    @if($hasData)
        <div class="px-4 pb-5 pt-6 sm:px-6">
            <div class="flex h-56 items-end gap-2 sm:gap-4">
                @foreach($chart as $m)
                    <div class="flex h-full min-w-0 flex-1 flex-col justify-end">
                        <div class="flex flex-1 items-end justify-center gap-1">
                            <div class="w-1/2 max-w-[2.5rem] rounded-t bg-emerald-500" style="height: {{ round($m['income'] / $max * 100, 1) }}%" title="Income ৳{{ number_format($m['income'], 2) }}"></div>
                            <div class="w-1/2 max-w-[2.5rem] rounded-t bg-rose-500" style="height: {{ round($m['expense'] / $max * 100, 1) }}%" title="Expense ৳{{ number_format($m['expense'], 2) }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex gap-2 sm:gap-4">
                @foreach($chart as $m)
                    <div class="min-w-0 flex-1 text-center">
                        <p class="text-xs font-semibold">{{ $m['label'] }}</p>
                        <p class="truncate text-[11px] text-emerald-600">{{ number_format($m['income']) }}</p>
                        <p class="truncate text-[11px] text-rose-600">{{ number_format($m['expense']) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="p-6 text-center text-gray-500">No income or expense recorded in the last 6 months.</p>
    @endif
</div>
