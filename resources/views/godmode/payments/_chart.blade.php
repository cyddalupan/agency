{{--
    Card 6b0342ac4cf7cfdc3e26d6a9 — a 12-bar month chart with no library.

    Twelve `div` bars with height percentages (no CDN in a `noindex` console,
    no canvas so the screen prints). Every month in `$months` gets a bar even
    when it collected nothing: a gap reads as "no data", a zero-height bar
    reads as "₱0.00", and only one of those is true.

    `max` is the tallest bucket, computed here once so no bar can be 120%.
--}}
@php
    $maxCents = 0;
    foreach ($months as $month) {
        $maxCents = max($maxCents, $service->toCents($series[$month] ?? 0));
    }
    $maxValue = sprintf('%d.%02d', intdiv($maxCents, 100), $maxCents % 100);
@endphp

<div class="card bg-base-100 shadow" id="{{ $chartId ?? 'payments-chart' }}">
    <div class="card-body p-4">
        <div class="flex items-baseline justify-between">
            <h2 class="font-semibold">{{ $chartTitle ?? 'Collected by month' }}</h2>
            <span class="text-xs opacity-60">Last 12 months</span>
        </div>

        {{-- Empty months render as zero-height bars, never as gaps. --}}
        <div class="flex items-end gap-2 h-40 mt-2" role="img" aria-label="Collected by month, last 12 months">
            @foreach ($months as $month)
                @php
                    $amount = $series[$month] ?? '0.00';
                    $height = $service->barHeight($amount, $maxValue);
                @endphp
                <div class="flex-1 flex flex-col justify-end items-center h-full" data-month="{{ $month }}">
                    <div class="w-full bg-primary/80 rounded-t"
                         style="height: {{ $height }}%"
                         title="{{ $month }}: {{ $service->formatMoney($amount) }}"
                         data-amount="{{ $amount }}"
                         data-height="{{ $height }}"></div>
                </div>
            @endforeach
        </div>

        <div class="flex gap-2 mt-1">
            @foreach ($months as $month)
                @php $label = \Illuminate\Support\Carbon::createFromFormat('Y-m', $month); @endphp
                <div class="flex-1 text-center text-[10px] opacity-70" data-label="{{ $month }}">
                    {{ $label->format('M') }} <span class="opacity-70">{{ $label->format('y') }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
