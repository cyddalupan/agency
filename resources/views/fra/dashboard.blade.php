@extends('layouts.employer-app-fra')

@section('title', __('messages.fra_dashboard'))

@section('head')
<style>
.chart-block {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    padding: 20px;
    margin-bottom: 20px;
}
.chart-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.chart-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 767px) {
    .chart-row {
        grid-template-columns: 1fr;
    }
}
/* Legend styles */
.legend {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
    justify-content: center;
}
.legend-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 600;
    color: #475569;
}
.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('messages.dashboard') }}</h1>

    {{-- KPI Metric Cards --}}
    <div style="display:flex;flex-wrap:wrap;gap:1px;width:100%;margin-bottom:32px;background:#fff;border-radius:10px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.06);overflow:hidden;">
        <div style="flex:1;min-width:120px;padding:16px 20px;text-align:center;">
            <div style="font-size:32px;font-weight:800;color:#29A1C4;">{{ $selected ?? 0 }}</div>
            <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-top:4px;">{{ __('messages.stat_selected') }}</div>
        </div>
        <div style="flex:1;min-width:120px;padding:16px 20px;text-align:center;border-left:1px solid #f1f5f9;">
            <div style="font-size:32px;font-weight:800;color:#29A1C4;">{{ $onprocess ?? 0 }}</div>
            <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-top:4px;">{{ __('messages.stat_on_process') }}</div>
        </div>
        <div style="flex:1;min-width:120px;padding:16px 20px;text-align:center;border-left:1px solid #f1f5f9;">
            <div style="font-size:32px;font-weight:800;color:#29A1C4;">{{ $flight ?? 0 }}</div>
            <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-top:4px;">{{ __('messages.stat_flight') }}</div>
        </div>
        <div style="flex:1;min-width:120px;padding:16px 20px;text-align:center;border-left:1px solid #f1f5f9;">
            <div style="font-size:32px;font-weight:800;color:#29A1C4;">{{ $deployed ?? 0 }}</div>
            <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-top:4px;">{{ __('messages.stat_deployed') }}</div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="chart-row">
        {{-- Pipeline Funnel Bar Chart --}}
        <div class="chart-block">
            <div class="chart-title">Pipeline Funnel</div>
            @php
                $pipelineMax = max(array_column($pipelineStages, 'count'));
                $pipelineColors = ['#3b82f6','#a855f7','#f97316','#eab308','#22c55e'];
            @endphp
            <div style="width:100%;">
                @foreach($pipelineStages as $i => $stage)
                @php
                    $pct = $pipelineMax > 0 ? round(($stage['count'] / $pipelineMax) * 100) : 0;
                @endphp
                <div style="display:flex;align-items:center;margin-bottom:10px;">
                    <div style="width:90px;font-size:12px;font-weight:600;color:#475569;flex-shrink:0;">
                        {{ $stage['label'] }}
                    </div>
                    <div style="flex:1;height:28px;background:#f1f5f9;border-radius:6px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:{{ $pipelineColors[$i] }};border-radius:6px;opacity:0.9;display:flex;align-items:center;padding-left:8px;min-width:{{ $stage['count'] > 0 ? '30px' : '0' }};">
                            <span style="color:#fff;font-size:12px;font-weight:700;">{{ $stage['count'] }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Status Breakdown Donut Chart (SVG) --}}
        <div class="chart-block">
            <div class="chart-title">Status Breakdown</div>
            @php
                $zoneTotal = array_sum(array_column($statusGroups, 'count'));
                $zoneNames = array_column($statusGroups, 'zone');
                $zoneCounts = array_column($statusGroups, 'count');
                $zoneColors = array_column($statusGroups, 'color');
                $donutSize = 180;
                $donutR = 75;
                $donutInner = 42;
                $cx = $donutSize / 2;
                $cy = $donutSize / 2;

                // Build pie arc paths
                $arcs = [];
                $total = $zoneTotal > 0 ? $zoneTotal : 1;
                $cumulative = 0;
                foreach ($zoneCounts as $i => $count) {
                    $startAngle = ($cumulative / $total) * 2 * M_PI - M_PI / 2;
                    $cumulative += $count;
                    $endAngle = ($cumulative / $total) * 2 * M_PI - M_PI / 2;

                    $x1 = $cx + $donutR * cos($startAngle);
                    $y1 = $cy + $donutR * sin($startAngle);
                    $x2 = $cx + $donutR * cos($endAngle);
                    $y2 = $cy + $donutR * sin($endAngle);

                    $x1i = $cx + $donutInner * cos($endAngle);
                    $y1i = $cy + $donutInner * sin($endAngle);
                    $x2i = $cx + $donutInner * cos($startAngle);
                    $y2i = $cy + $donutInner * sin($startAngle);

                    $largeArc = ($count / $total) > 0.5 ? 1 : 0;

                    if ($count > 0) {
                        $arcs[] = [
                            'd' => "M $x1 $y1 A $donutR $donutR 0 $largeArc 1 $x2 $y2 L $x1i $y1i A $donutInner $donutInner 0 $largeArc 0 $x2i $y2i Z",
                            'color' => $zoneColors[$i],
                            'label' => $zoneNames[$i] . ' (' . $count . ')',
                        ];
                    }
                }
            @endphp
            <div style="display:flex;flex-direction:column;align-items:center;">
                <div style="position:relative;width:{{ $donutSize }}px;height:{{ $donutSize }}px;">
                    <svg width="{{ $donutSize }}" height="{{ $donutSize }}" viewBox="0 0 {{ $donutSize }} {{ $donutSize }}">
                        @foreach($arcs as $arc)
                        <path d="{{ $arc['d'] }}" fill="{{ $arc['color'] }}" stroke="#fff" stroke-width="1.5" opacity="0.9">
                            <title>{{ $arc['label'] }}</title>
                        </path>
                        @endforeach
                        <text x="{{ $cx }}" y="{{ $cy - 5 }}" text-anchor="middle" font-size="22" font-weight="800" fill="#1e293b">{{ $zoneTotal }}</text>
                        <text x="{{ $cx }}" y="{{ $cy + 14 }}" text-anchor="middle" font-size="11" font-weight="500" fill="#94a3b8">TOTAL</text>
                    </svg>
                </div>
                <div class="legend">
                    @foreach($statusGroups as $group)
                    <span class="legend-item">
                        <span class="legend-dot" style="background:{{ $group['color'] }}"></span>
                        {{ $group['zone'] }} ({{ $group['count'] }})
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Top Positions Bar Chart (SVG) --}}
    <div class="chart-block">
        <div class="chart-title">Top Hiring Positions</div>
        @if(count($positionLabels) > 0)
        @php
            $posMax = max($positionCounts);
            $barHeight = 30;
            $labelWidth = 130;
            $chartMaxWidth = 600;
        @endphp
        <div style="width:100%;max-width:{{ $chartMaxWidth }}px;">
            @foreach($positionLabels as $i => $label)
            @php
                $pct = $posMax > 0 ? round(($positionCounts[$i] / $posMax) * 100) : 0;
            @endphp
            <div style="display:flex;align-items:center;margin-bottom:8px;">
                <div style="width:{{ $labelWidth }}px;font-size:12px;font-weight:600;color:#475569;flex-shrink:0;text-align:right;padding-right:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $label }}
                </div>
                <div style="flex:1;height:{{ $barHeight }}px;background:#f1f5f9;border-radius:6px;overflow:hidden;">
                    <div style="height:100%;width:{{ $pct }}%;background:#29A1C4;border-radius:6px;opacity:0.85;display:flex;align-items:center;padding-left:8px;min-width:{{ $positionCounts[$i] > 0 ? '28px' : '0' }};">
                        <span style="color:#fff;font-size:12px;font-weight:700;">{{ $positionCounts[$i] }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="padding:30px 0;text-align:center;color:#94a3b8;font-size:14px;">No position data yet.</div>
        @endif
    </div>
</div>
@endsection
