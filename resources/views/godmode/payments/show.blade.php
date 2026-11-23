@extends('godmode.layouts.app')

@section('title', 'Payments · '.$agency->name)

@php
    $service = app(\App\Services\GodMode\GodReceivableService::class);

    $base = array_filter([
        'from'    => $filters['from'],
        'to'      => $filters['to'],
        'account' => $filters['account'] ?: null,
        'status'  => $filters['status'] !== 'received' ? $filters['status'] : null,
        'q'       => $filters['q'] ?: null,
        'per'     => $filters['per'] !== 25 ? $filters['per'] : null,
    ], fn ($v) => $v !== null && $v !== '');

    // The KPIs follow the status filter, like the table below them (B2). The
    // dashboard never filters by status, so its tiles are unchanged.
    // The money and row numbers come from summary(), the same row set as the
    // table below (F3); the Rows tile is the number of rows listed.
    $rowsValue = $summary['row_count'] === null ? '—' : number_format($summary['row_count']);

    $tiles = [
        ['key' => 'collected', 'label' => 'Collected', 'value' => $service->formatMoney($summary['collected']), 'hint' => $bounds['from'].' to '.$bounds['to']],
        ['key' => 'outstanding', 'label' => 'Outstanding', 'value' => $service->formatMoney($summary['outstanding']), 'hint' => 'Pending, not yet received'],
        ['key' => 'rows', 'label' => 'Rows', 'value' => $rowsValue, 'hint' => number_format($summary['voided_count']).' voided'],
        ['key' => 'last', 'label' => 'Last payment', 'value' => $rows->total() > 0 && $rows->first()->date ? $rows->first()->date->format('d/m/Y') : '—', 'hint' => 'Most recent row shown'],
    ];
@endphp

@section('content')
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-extrabold">{{ $header['name'] }}</h1>
            @include('godmode.agencies._status', ['status' => $header['status']])
        </div>
        <p class="text-sm opacity-70">
            @if (filled($header['subdomain']))
                <code class="text-xs">{{ $header['subdomain'] }}.landas.fixitautoservices.com</code>
            @else
                <span class="badge badge-sm badge-error badge-outline" data-badge="no-subdomain">no subdomain</span>
            @endif
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('godmode.payments.index') }}" class="btn btn-sm btn-ghost">← All agencies</a>
        <a href="{{ route('godmode.agencies.show', $agency) }}" class="btn btn-sm btn-ghost">Agency console</a>
    </div>
</div>

<form method="GET" action="{{ route('godmode.payments.show', $agency) }}" class="card bg-base-100 shadow" id="payments-filters">
    <div class="card-body p-4 flex flex-wrap items-end gap-3">
        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">From</span>
            <input type="month" name="from" value="{{ $filters['from'] }}" class="input input-bordered input-sm">
        </label>
        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">To</span>
            <input type="month" name="to" value="{{ $filters['to'] }}" class="input input-bordered input-sm">
        </label>
        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">Account</span>
            <select name="account" class="select select-bordered select-sm">
                <option value="all" @selected(! $filters['account'])>All accounts</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account }}" @selected($filters['account'] === $account)>{{ $account }}</option>
                @endforeach
                <option value="{{ $unspecified }}" @selected($filters['account'] === $unspecified)>{{ $unspecified }}</option>
            </select>
        </label>
        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">Status</span>
            <select name="status" class="select select-bordered select-sm" data-status-selected="{{ $filters['status'] }}">
                <option value="received" @selected($filters['status'] === 'received') data-status-option="received">Collected</option>
                <option value="pending" @selected($filters['status'] === 'pending') data-status-option="pending">Outstanding</option>
                <option value="all" @selected($filters['status'] === 'all') data-status-option="all">All</option>
            </select>
        </label>
        <label class="form-control flex-1 min-w-[12rem]">
            <span class="label-text text-xs font-semibold mb-1">Search</span>
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Reference, particular, code or applicant"
                   class="input input-bordered input-sm" autocomplete="off">
        </label>
        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">Per page</span>
            <select name="per" class="select select-bordered select-sm" onchange="this.form.submit()">
                @foreach ($perOptions as $option)
                    <option value="{{ $option }}" @selected((int) ($filters['per'] ?: 25) === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="btn btn-sm btn-neutral">Apply</button>
        <a href="{{ route('godmode.payments.show', $agency) }}" class="btn btn-sm btn-ghost">Clear</a>
    </div>
</form>

@include('godmode.payments._kpis', ['tiles' => $tiles, 'kpiId' => 'payments-kpis'])

@include('godmode.payments._chart', ['months' => $bounds['months'], 'series' => $series, 'service' => $service, 'chartId' => 'payments-chart-agency'])

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

<div class="card bg-base-100 shadow overflow-x-auto">
    <div class="px-4 pt-4">
        {{-- The heading names exactly the rows below it (B2): `pending` is
             outstanding money only, `received` is collected money only. --}}
        <h2 class="font-semibold" data-rows-heading="{{ $filters['status'] }}">
            {{ $filters['status'] === 'pending' ? 'Outstanding rows' : ($filters['status'] === 'received' ? 'Collected rows' : 'All receivables') }}
        </h2>
        <p class="text-xs opacity-60">
            {{ number_format($rows->total()) }} of {{ $summary['row_count'] === null ? '—' : number_format($summary['row_count']) }} row(s) in this window.
            Money: {{ $service->formatMoney($filters['status'] === 'pending' ? $summary['outstanding'] : $summary['collected']) }}.
        </p>
    </div>
    <table class="table table-sm" id="payments-receivables">
        <thead>
            <tr>
                <th>Code</th>
                <th>Date</th>
                <th>Account</th>
                <th>Type</th>
                <th>Mode</th>
                <th>Ref / AR</th>
                <th>Applicant</th>
                <th>Particular</th>
                <th class="text-right">Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr data-receivable-id="{{ $row->id }}" data-receivable-page="live">
                    <td><code class="text-xs">{{ $row->code }}</code></td>
                    <td>{{ $row->date?->format('d/m/Y') }}</td>
                    <td>{{ $row->account ?? $unspecified }}</td>
                    <td>{{ $row->type ?? $unspecified }}</td>
                    <td>{{ $row->mode ?? $unspecified }}</td>
                    <td>{{ $row->ref_ar ?: '—' }}</td>
                    {{-- Applicant name is shown HERE only, never on the aggregates (§4.5). --}}
                    <td>{{ trim(implode(' ', array_filter([$row->first_name, $row->middle_name, $row->last_name]))) ?: '—' }}</td>
                    <td class="max-w-[16rem] truncate" title="{{ $row->particular }}">{{ $row->particular ?: '—' }}</td>
                    <td class="text-right" data-amount="{{ $row->amount }}">{{ $service->formatMoney($row->amount) }}</td>
                    <td>
                        {{-- This table is live money only, so the badge is the
                             reason it is live: Collected or Outstanding. --}}
                        <span class="badge badge-sm {{ $row->status === 'received' ? 'badge-success' : 'badge-warning' }}" data-receivable-status="{{ $row->status }}">
                            {{ $row->status === 'received' ? 'Collected' : 'Outstanding' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-8 opacity-60">No receivables match these filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="flex items-center justify-between gap-4 p-4">
        <span class="text-xs opacity-60">
            @if ($rows->total() > 0)
                Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }}
            @else
                No rows
            @endif
        </span>
        {{ $rows->links() }}
    </div>
</div>

@if ($voided->isNotEmpty() || $summary['voided_count'] > 0)
    <div class="card bg-base-100 shadow overflow-x-auto" id="payments-voided">
        <div class="px-4 pt-4">
            <h2 class="font-semibold">Voided money</h2>
            <p class="text-xs opacity-60">
                {{ number_format($summary['voided_count']) }} voided row(s), excluded from every total above.
                Excluded from totals: cancelled and deleted rows stay visible so a voucher never silently disappears.
            </p>
        </div>
        <table class="table table-sm" id="payments-voided-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Ref / AR</th>
                    <th class="text-right">Amount</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($voided as $row)
                    {{-- One badge, lowercase, the word an operator reads and
                         QA can match: `cancelled` or `deleted` (B3). --}}
                    @php $reason = $row->deleted_at ? 'deleted' : (string) $row->status; @endphp
                    <tr data-voided-id="{{ $row->id }}" data-voided-reason="{{ $reason }}">
                        <td><code class="text-xs">{{ $row->code }}</code></td>
                        <td>{{ $row->date?->format('d/m/Y') }}</td>
                        <td>{{ $row->account ?? $unspecified }}</td>
                        <td>{{ $row->ref_ar ?: '—' }}</td>
                        <td class="text-right">{{ $service->formatMoney($row->amount) }}</td>
                        <td>
                            <span class="badge badge-sm {{ $reason === 'deleted' ? 'badge-error' : 'badge-ghost' }}" data-badge="{{ $reason }}">{{ ucfirst($reason) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
