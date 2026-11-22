@extends('godmode.layouts.app')

@section('title', 'Payments')

@php
    // Every filter lives in the URL so a copied link reproduces the screen,
    // including the sort. `page` is dropped when a filter changes so a stale
    // page number cannot land on an empty page.
    $base = array_filter([
        'from'    => $filters['from'],
        'to'      => $filters['to'],
        'agency'  => $filters['agency'] ?: null,
        'account' => $filters['account'] ?: null,
        'status'  => $filters['status'] !== 'received' ? $filters['status'] : null,
        'q'       => $filters['q'] ?: null,
        'per'     => $filters['per'] !== 25 ? $filters['per'] : null,
        'sort'    => $filters['sort'] !== 'collected' ? $filters['sort'] : null,
        'dir'     => $filters['dir'] !== 'desc' ? $filters['dir'] : null,
    ], fn ($v) => $v !== null && $v !== '');

    $sortLink = function (string $column) use ($base, $filters) {
        $dir = ($filters['sort'] === $column && $filters['dir'] !== 'desc') ? 'desc' : 'asc';

        return route('godmode.payments.index', array_merge($base, ['sort' => $column, 'dir' => $dir]));
    };
    $arrow = function (string $column) use ($filters) {
        if ($filters['sort'] !== $column) {
            return '';
        }

        return $filters['dir'] === 'desc' ? ' ↓' : ' ↑';
    };

    $service = app(\App\Services\GodMode\GodReceivableService::class);

    $tiles = [
        ['key' => 'collected', 'label' => 'Collected', 'value' => $service->formatMoney($summary['collected']), 'hint' => $bounds['from'].' to '.$bounds['to']],
        ['key' => 'outstanding', 'label' => 'Outstanding', 'value' => $service->formatMoney($summary['outstanding']), 'hint' => 'Pending, not yet received'],
        ['key' => 'agencies', 'label' => 'Agencies with collections', 'value' => number_format($summary['agency_count']), 'hint' => 'At least one row in this window'],
        ['key' => 'rows', 'label' => 'Rows', 'value' => number_format($summary['row_count']), 'hint' => number_format($summary['voided_count']).' voided'],
    ];
@endphp

@section('content')
<div class="flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold">Payments</h1>
        <p class="text-sm opacity-70">
            Every agency's receivables, read-only. Collected is money an agency has received;
            outstanding is money it has billed and not been paid.
        </p>
    </div>
</div>

{{-- Filters. `from` > `to` is swapped server-side, never a 422. --}}
<form method="GET" action="{{ route('godmode.payments.index') }}" class="card bg-base-100 shadow" id="payments-filters">
    <div class="card-body p-4 flex flex-wrap items-end gap-3">
        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">From</span>
            <input type="month" name="from" value="{{ $filters['from'] }}" class="input input-bordered input-sm" id="payments-from">
        </label>

        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">To</span>
            <input type="month" name="to" value="{{ $filters['to'] }}" class="input input-bordered input-sm" id="payments-to">
        </label>

        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">Agency</span>
            <select name="agency" class="select select-bordered select-sm" id="payments-agency">
                <option value="all" @selected(! $filters['agency'])>All agencies</option>
                @foreach ($agencies as $id => $name)
                    <option value="{{ $id }}" @selected((int) $filters['agency'] === (int) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </label>

        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">Account</span>
            <select name="account" class="select select-bordered select-sm" id="payments-account">
                <option value="all" @selected(! $filters['account'])>All accounts</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account }}" @selected($filters['account'] === $account)>{{ $account }}</option>
                @endforeach
                <option value="{{ $unspecified }}" @selected($filters['account'] === $unspecified)>{{ $unspecified }}</option>
            </select>
        </label>

        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">Status</span>
            <select name="status" class="select select-bordered select-sm" id="payments-status">
                <option value="received" @selected($filters['status'] === 'received')>Collected</option>
                <option value="pending" @selected($filters['status'] === 'pending')>Outstanding</option>
                <option value="all" @selected($filters['status'] === 'all')>All</option>
            </select>
        </label>

        <label class="form-control flex-1 min-w-[12rem]">
            <span class="label-text text-xs font-semibold mb-1">Search</span>
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Reference, particular, code or applicant"
                   class="input input-bordered input-sm" autocomplete="off" id="payments-q">
        </label>

        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">Per page</span>
            <select name="per" class="select select-bordered select-sm" onchange="this.form.submit()">
                @foreach ($perOptions as $option)
                    <option value="{{ $option }}" @selected((int) ($filters['per'] ?: 25) === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </label>

        @if ($filters['sort'] !== 'collected' || $filters['dir'] !== 'desc')
            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
            <input type="hidden" name="dir" value="{{ $filters['dir'] }}">
        @endif

        <button type="submit" class="btn btn-sm btn-neutral">Apply</button>
        <a href="{{ route('godmode.payments.index') }}" class="btn btn-sm btn-ghost">Clear</a>
    </div>
</form>

@include('godmode.payments._kpis', ['tiles' => $tiles, 'kpiId' => 'payments-kpis'])

@include('godmode.payments._chart', ['months' => $bounds['months'], 'series' => $series, 'service' => $service])

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
            <h2 class="font-semibold">Account mix</h2>
            <table class="table table-xs" id="payments-accounts">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th class="text-right">Collected</th>
                        <th class="text-right">Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($byAccount as $row)
                        <tr data-account="{{ $row['account'] }}">
                            <td>{{ $row['account'] }}</td>
                            <td class="text-right" data-collected="{{ $row['collected'] }}">{{ $service->formatMoney($row['collected']) }}</td>
                            <td class="text-right">{{ $service->formatMoney($row['outstanding']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
            <h2 class="font-semibold">Outstanding by age</h2>
            <table class="table table-xs" id="payments-ageing">
                <thead>
                    <tr>
                        <th>Age</th>
                        <th class="text-right">Rows</th>
                        <th class="text-right">Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ageing as $bucket => $row)
                        <tr data-bucket="{{ $bucket }}">
                            <td>{{ $bucket }} days</td>
                            <td class="text-right">{{ number_format($row['count']) }}</td>
                            <td class="text-right">{{ $service->formatMoney($row['amount']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-sm" id="payments-agencies-table">
        <thead>
            <tr>
                <th>Agency</th>
                <th class="text-right"><a href="{{ $sortLink('collected') }}" class="link link-hover">Collected{{ $arrow('collected') }}</a></th>
                <th class="text-right"><a href="{{ $sortLink('outstanding') }}" class="link link-hover">Outstanding{{ $arrow('outstanding') }}</a></th>
                <th class="text-right">Rows</th>
                <th><a href="{{ $sortLink('last') }}" class="link link-hover">Last payment{{ $arrow('last') }}</a></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perAgency as $row)
                <tr data-agency-id="{{ $row->id }}">
                    <td class="font-semibold">{{ $row->name }}</td>
                    <td class="text-right" data-collected="{{ $row->collected }}">{{ $service->formatMoney($row->collected) }}</td>
                    <td class="text-right" data-outstanding="{{ $row->outstanding }}">{{ $service->formatMoney($row->outstanding) }}</td>
                    <td class="text-right">{{ number_format($row->received_count) }}</td>
                    <td>{{ $row->last_payment_date ? \Illuminate\Support\Carbon::parse($row->last_payment_date)->format('d/m/Y') : '—' }}</td>
                    <td class="text-right">
                        <a href="{{ route('godmode.payments.show', ['agency' => $row->id] + $base) }}" class="btn btn-xs btn-ghost">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-8 opacity-60">No agencies match these filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($summary['voided_count'] > 0)
    <p class="text-xs opacity-70" id="payments-voided">
        {{ number_format($summary['voided_count']) }} voided row(s) (cancelled or deleted) are excluded from
        collected and outstanding. Open an agency to see them with a badge.
    </p>
@endif
@endsection
