@extends('godmode.layouts.app')

@section('title', 'Dashboard')

@php
    $service = app(\App\Services\GodMode\GodReceivableService::class);

    // Same service, same definitions as /godmode/payments. Two windows:
    // the current month for the KPI strip, rolling 12 for the tile + chart.
    $tiles = [
        [
            'key'   => 'month',
            'label' => 'Collected this month',
            'value' => $service->formatMoney($summaryThis['collected']),
            'hint'  => now()->format('F Y'),
        ],
        [
            'key'   => 'rolling',
            'label' => 'Collected, last 12 months',
            'value' => $service->formatMoney($summaryRolling['collected']),
            'hint'  => $months[0].' to '.end($months),
        ],
        [
            'key'   => 'outstanding',
            'label' => 'Outstanding',
            'value' => $service->formatMoney($summaryRolling['outstanding']),
            'hint'  => 'Pending across all agencies, 12 months',
        ],
        [
            'key'   => 'agencies',
            'label' => 'Agencies with collections',
            'value' => number_format($summaryRolling['agency_count']),
            'hint'  => 'At least one row in 12 months',
        ],
    ];
@endphp

@section('content')
<div class="flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold">God Mode</h1>
        <p class="text-sm opacity-70">Control plane. Cross-tenant, read-only where money is involved.</p>
    </div>
</div>

@include('godmode.payments._kpis', ['tiles' => $tiles, 'kpiId' => 'dashboard-kpis'])

@include('godmode.payments._chart', [
    'months' => $months,
    'series' => $series,
    'service' => $service,
    'chartId' => 'dashboard-chart',
    'chartTitle' => 'Collected by month',
])

<div class="card bg-base-100 shadow">
    <div class="card-body p-4">
        <div class="flex items-baseline justify-between">
            <h2 class="font-semibold">Top 5 agencies by collected</h2>
            <a href="{{ route('godmode.payments.index') }}" class="text-xs link link-hover">All payments →</a>
        </div>
        <table class="table table-sm" id="dashboard-top-agencies">
            <thead>
                <tr>
                    <th>Agency</th>
                    <th class="text-right">Collected</th>
                    <th class="text-right">Outstanding</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($top as $row)
                    <tr data-agency-id="{{ $row->id }}">
                        <td class="font-semibold">
                            <a href="{{ route('godmode.payments.show', $row->id) }}" class="link link-hover">{{ $row->name }}</a>
                        </td>
                        <td class="text-right" data-collected="{{ $row->collected }}">{{ $service->formatMoney($row->collected) }}</td>
                        <td class="text-right">{{ $service->formatMoney($row->outstanding) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center py-6 opacity-60">No collections in the last 12 months.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <a href="{{ route('godmode.agencies.index') }}"
       class="block card bg-base-100 shadow hover:shadow-lg transition" id="dashboard-agencies-entry">
        <div class="card-body p-6">
            <h2 class="text-lg font-semibold">Agencies</h2>
            <p class="text-sm opacity-70">Every client on the platform: status, size and subdomain. Create, edit, archive.</p>
        </div>
    </a>
    <a href="{{ route('godmode.payments.index') }}"
       class="block card bg-base-100 shadow hover:shadow-lg transition" id="dashboard-payments-entry">
        <div class="card-body p-6">
            <h2 class="text-lg font-semibold">Payments</h2>
            <p class="text-sm opacity-70">Cross-tenant receivables: collected, outstanding and what is ageing. Read-only.</p>
        </div>
    </a>
</div>
@endsection
