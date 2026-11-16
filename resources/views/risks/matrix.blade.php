@extends('layouts.app')

@section('title', 'Risk Matrix')

@section('content')
@php
    // Colour per level, matching the badge vocabulary used elsewhere.
    $cellClass = [
        'low'      => 'bg-success/20 border-success/40',
        'medium'   => 'bg-warning/25 border-warning/50',
        'high'     => 'bg-error/30 border-error/50',
        'critical' => 'bg-error/60 border-error text-error-content',
    ];
@endphp

<div class="p-4 sm:p-6 space-y-6">

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">▦</span>
                <div>
                    <h1 class="text-2xl font-extrabold">Risk Matrix</h1>
                    <p class="opacity-70 text-sm mt-0.5">Likelihood × impact. Closed risks are excluded.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0">
                    <span class="font-bold" id="matrix-count">{{ $total }}</span>
                    {{ $total === 1 ? 'open risk' : 'open risks' }}
                </span>
                <a href="{{ route('risks.index') }}" class="btn btn-sm btn-ghost" id="matrix-list-link">☰ Register</a>
                <a href="{{ route('risks.create') }}" class="btn btn-sm btn-secondary" id="matrix-new">➕ New Risk</a>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table table-fixed" id="risk-matrix">
                    <thead>
                        <tr>
                            <th class="w-28 text-xs">Likelihood ↓ / Impact →</th>
                            @foreach ($scale as $impact)
                                <th class="text-center text-xs">
                                    <div class="font-bold">{{ $impact }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scale as $likelihood)
                            <tr>
                                <th class="text-xs">
                                    <div class="font-bold">{{ $likelihood }}</div>
                                </th>
                                @foreach ($scale as $impact)
                                    @php
                                        $level = $levelFor($likelihood, $impact);
                                        $cellRisks = $grid[$likelihood][$impact] ?? [];
                                    @endphp
                                    <td class="align-top p-1">
                                        <div class="rounded-lg border min-h-24 p-1.5 {{ $cellClass[$level] }}"
                                             id="cell-{{ $likelihood }}-{{ $impact }}"
                                             data-cell="{{ $likelihood }}x{{ $impact }}"
                                             data-level="{{ $level }}"
                                             data-count="{{ count($cellRisks) }}">
                                            <div class="flex items-center justify-between text-[10px] uppercase tracking-wide opacity-70">
                                                <span class="font-semibold">{{ \App\Models\Risk::LEVEL_LABELS[$level] }}</span>
                                                <span class="font-mono">{{ $likelihood * $impact }}</span>
                                            </div>
                                            @foreach ($cellRisks as $risk)
                                                <a href="{{ route('risks.edit', $risk) }}"
                                                   class="mt-1 block rounded bg-base-100/90 px-1.5 py-1 text-[11px] leading-tight hover:shadow"
                                                   title="{{ $risk->title }}"
                                                   data-risk-ref="{{ $risk->ref_no }}">
                                                    <span class="font-mono font-bold">{{ $risk->ref_no }}</span>
                                                    <span class="block truncate">{{ $risk->title }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center gap-4 mt-4 text-xs">
                <span class="font-semibold uppercase tracking-wide opacity-60">Legend</span>
                @foreach ($cellClass as $level => $class)
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block w-4 h-4 rounded border {{ $class }}"></span>
                        {{ \App\Models\Risk::LEVEL_LABELS[$level] }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
