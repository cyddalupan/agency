{{--
    KPI tiles. Four numbers, all from GodReceivableService::summary(), so the
    strip and the table underneath can never disagree.
--}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3" id="{{ $kpiId ?? 'payments-kpis' }}">
    @foreach ($tiles as $tile)
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4">
                <span class="text-xs uppercase tracking-wide opacity-60">{{ $tile['label'] }}</span>
                <span class="text-xl font-bold" data-kpi="{{ $tile['key'] }}">{{ $tile['value'] }}</span>
                @if (! empty($tile['hint']))
                    <span class="text-xs opacity-60">{{ $tile['hint'] }}</span>
                @endif
            </div>
        </div>
    @endforeach
</div>
