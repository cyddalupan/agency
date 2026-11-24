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

    // F3: the three money/row numbers in the strip come from ONE row set
    // (summary()), so `Rows` is the number of rows the table below lists.
    $rowsValue = $summary['row_count'] === null ? '—' : number_format($summary['row_count']);

    $tiles = [
        ['key' => 'collected', 'label' => 'Collected', 'value' => $service->formatMoney($summary['collected']), 'hint' => $bounds['from'].' to '.$bounds['to']],
        ['key' => 'outstanding', 'label' => 'Outstanding', 'value' => $service->formatMoney($summary['outstanding']), 'hint' => 'Pending, not yet received'],
        ['key' => 'agencies', 'label' => 'Agencies with collections', 'value' => $summary['agency_count'] === null ? '—' : number_format($summary['agency_count']), 'hint' => $summary['agency_count'] !== null ? 'At least one row in this window' : ($filters['status'] === 'pending' ? 'No agency has pending rows in this filter' : 'Nothing measured in this window')],
        ['key' => 'rows', 'label' => 'Rows', 'value' => $rowsValue, 'hint' => number_format($summary['voided_count']).' voided'],
    ];

    // Card 6b055e29. "Why is this list short?" — an all-time fact about the
    // agencies in scope, not a windowed one, so a thin month never looks like
    // a broken screen. `coverage` comes from the controller (service method
    // agencyCoverage); it is deliberately NOT derived from $perAgency, whose
    // inner join drops exactly the agencies with no rows.
    if ($filters['agency']) {
        $namedAgency = $coverage['agency']; // null when ?agency= does not exist
        if ($namedAgency === null) {
            $coverageLine = 'No agency matches that filter, so this view measures nothing.';
        } elseif ($coverage['with_receivables'] > 0) {
            $coverageLine = 'This agency has recorded at least one receivable. Only what falls inside the window above is listed.';
        } else {
            $coverageLine = 'This agency has never recorded a receivable. Table below stays empty until it enters its own money.';
        }
    } elseif ($coverage['in_scope'] === 0) {
        $coverageLine = 'No agencies exist yet.';
    } else {
        $coverageLine = number_format($coverage['without_receivables']).' of '.number_format($coverage['in_scope'])
            .' agencies have never recorded a receivable; '.number_format($coverage['with_receivables'])
            .' have. Only agencies that have recorded payments appear in the table below.';
    }
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

{{-- Card 6b055e29, item 1: say the read-only nature out loud, without a click.
     Wording is fenced: the rendered HTML of this page is grepped by
     `no_screen_offers_an_edit_or_approve_control_on_a_receivable` for the
     strings Delete/delete/destroy/forceDelete/Approve/approve, so this note
     must never promise or deny those actions by name. --}}
<div class="alert mt-3" data-readonly-note="payments-index">
    <div class="text-sm">
        <p class="font-semibold">This screen is a read-only view of money the agencies recorded.</p>
        <p class="opacity-80">
            Payments are entered by the agency itself, inside its own app, not from here. That is why
            this page has no add, edit or void control: the agency's entry is the record, and an
            operator change would break the trail that explains every amount.
        </p>
    </div>
</div>

{{-- Filters. `from` > `to` is swapped server-side, never a 422. --}}
<form method="GET" action="{{ route('godmode.payments.index') }}" class="card bg-base-100 shadow" id="payments-filters">
    {{-- Card 6b055e5e36204c86a704b2be. Every control group is a flex column
         (`label.flex.flex-col.gap-1`) with the caption as a direct flex child,
         which is the one thing the old markup was missing: daisyUI 4's
         `.form-control` / `.label-text` do not exist in daisyUI 5, so each
         label stayed `display: block` and the row had no real width to place
         (it packed into the right edge of the card). Captions are `text-sm`
         (14px, ~20px line box) not `text-xs`, per the legibility criterion. --}}
    <div class="card-body p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-3">
        <label class="flex flex-col gap-1 min-w-36 max-w-40">
            <span class="text-sm font-semibold">From</span>
            <input type="month" name="from" value="{{ $filters['from'] }}" class="input input-bordered input-sm" id="payments-from">
        </label>

        <label class="flex flex-col gap-1 min-w-36 max-w-40">
            <span class="text-sm font-semibold">To</span>
            <input type="month" name="to" value="{{ $filters['to'] }}" class="input input-bordered input-sm" id="payments-to">
        </label>

        <label class="flex flex-col gap-1 min-w-36 max-w-40">
            <span class="text-sm font-semibold">Agency</span>
            <select name="agency" class="select select-bordered select-sm" id="payments-agency">
                <option value="all" @selected(! $filters['agency'])>All agencies</option>
                @foreach ($agencies as $id => $name)
                    <option value="{{ $id }}" @selected((int) $filters['agency'] === (int) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1 min-w-36 max-w-40">
            <span class="text-sm font-semibold">Account</span>
            <select name="account" class="select select-bordered select-sm" id="payments-account">
                <option value="all" @selected(! $filters['account'])>All accounts</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account }}" @selected($filters['account'] === $account)>{{ $account }}</option>
                @endforeach
                <option value="{{ $unspecified }}" @selected($filters['account'] === $unspecified)>{{ $unspecified }}</option>
            </select>
        </label>

        <label class="flex flex-col gap-1 min-w-36 max-w-40">
            <span class="text-sm font-semibold">Status</span>
            {{-- data-status-option is the rendered state, so a test can read
                 which filter the page believes it is showing. --}}
            <select name="status" class="select select-bordered select-sm" id="payments-status" data-status-selected="{{ $filters['status'] }}">
                <option value="received" @selected($filters['status'] === 'received') data-status-option="received">Collected</option>
                <option value="pending" @selected($filters['status'] === 'pending') data-status-option="pending">Outstanding</option>
                <option value="all" @selected($filters['status'] === 'all') data-status-option="all">All</option>
            </select>
        </label>

        <label class="flex flex-col gap-1 flex-1 min-w-[12rem]">
            <span class="text-sm font-semibold">Search</span>
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Reference, particular, code or applicant"
                   class="input input-bordered input-sm" autocomplete="off" id="payments-q">
        </label>

        <label class="flex flex-col gap-1 min-w-36 max-w-40">
            <span class="text-sm font-semibold">Per page</span>
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

{{-- Item 2: the reason a short table is short, directly above the table it explains. --}}
<div class="mt-4 mb-2" data-coverage-note="payments-index">
    <p class="text-sm"><span class="font-semibold">Coverage:</span> {{ $coverageLine }}</p>
    <p class="text-xs opacity-60">All-time, across every date: the date window above does not change this count.</p>
</div>

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
            {{-- Ageing is about outstanding money, always: pending rows only. --}}
            <h2 class="font-semibold">Outstanding by age</h2>
            <p class="text-xs opacity-60">Pending rows not yet received, by days since their date.</p>
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
                <tr data-agency-id="{{ $row->id }}" data-agency-name="{{ $row->name }}">
                    <td class="font-semibold">{{ $row->name }}</td>
                    <td class="text-right" data-collected="{{ $row->collected }}">{{ $service->formatMoney($row->collected) }}</td>
                    <td class="text-right" data-outstanding="{{ $row->outstanding }}">{{ $service->formatMoney($row->outstanding) }}</td>
                    <td class="text-right" data-row-count="{{ $row->row_count }}">{{ number_format($row->row_count) }}</td>
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
