@if(isset($paymentAlerts) && $paymentAlerts->isNotEmpty())
    <div class="rounded-2xl border border-amber-300 bg-gradient-to-r from-amber-50 to-red-50 p-5 text-slate-800">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-lg font-bold">🔔 Payments to make</h2>
            <a href="{{ route('payment-reminders.index') }}" class="text-sm font-semibold text-blue-700 hover:underline">See all</a>
        </div>
        <ul class="space-y-2">
            @foreach($paymentAlerts as $reminder)
                @php $left = $reminder->daysLeft(); @endphp
                <li class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-semibold">{{ $reminder->title }}
                        @if($reminder->amount !== null)<span class="font-normal text-slate-600">· ৳{{ number_format($reminder->amount, 2) }}</span>@endif
                    </span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $left < 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $left < 0 ? abs($left).' days overdue' : ($left === 0 ? 'Due today' : 'Due in '.$left.' days') }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
