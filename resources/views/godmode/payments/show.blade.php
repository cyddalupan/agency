@extends('godmode.layouts.app')

@section('title', 'Payment ledger · '.$agency->name)

@php
    use App\Models\AgencyBillingPayment;

    // Card 6b07f3667e9a74a10e2fe55f — the COMPLETE payment record for one
    // agency. The village modal's billing summary shows the last rows and a
    // total; that is the right size for a modal, but reconciling a bank
    // statement needs everything. This screen is read-only by design: money is
    // recorded and voided in the village modal, never here.
    $typeLabel = fn (string $t) => $t === AgencyBillingPayment::TYPE_INITIAL ? 'Initial' : 'Monthly';

    // One service instance for the whole render: resolving it inside the row
    // loop would rebuild it per payment for a single badge class.
    $ledgerService = app(\App\Services\GodMode\PaymentLedgerService::class);

    $money = fn (float|string $v) => number_format((float) $v, 2);

    // Filter state carried into every link so sorting/paging never drops it.
    $base = array_filter([
        'type'   => $filters['type'] !== 'all' ? $filters['type'] : null,
        'voided' => $filters['voided'] ? 1 : null,
        'from'   => $filters['from'],
        'to'     => $filters['to'],
        'per'    => $filters['per'] !== 25 ? $filters['per'] : null,
    ], fn ($v) => $v !== null && $v !== '');

    $isFiltered = $base !== [];
@endphp

@section('content')
{{-- ── header ─────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-extrabold">{{ $agency->name }}</h1>
            <span class="badge badge-outline badge-sm">Payment ledger</span>
        </div>
        <p class="text-sm opacity-70 mt-1">
            Everything this agency has paid Landas, oldest agreement to now.
            @if (filled($agency->subdomain))
                <code class="text-xs">{{ $agency->subdomain }}.landas.fixitautoservices.com</code>
            @endif
        </p>
        <p class="text-sm opacity-80 mt-2 max-w-2xl" data-readonly-note="payment-ledger">
            <span class="font-semibold">Read-only view.</span>
            Payments are recorded and voided from the village, next to the agency's
            agreement. This screen only reads the ledger — it has no add, edit or void control.
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('godmode.payments.index') }}" class="btn btn-sm btn-ghost">← All agencies</a>
        <a href="{{ route('godmode.dashboard') }}" class="btn btn-sm btn-ghost">Village</a>
    </div>
</div>

{{-- ── agreement context ──────────────────────────────────────────── --}}
<div class="mt-5 card bg-base-100 shadow-sm">
    <div class="card-body p-4">
        @if ($plan)
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                <span class="opacity-60">Current agreement</span>
                <span>Initial <strong class="tabular-nums">₱{{ $money($plan->initial_payment) }}</strong>
                    <span class="opacity-60">× {{ $plan->initial_terms }}</span></span>
                <span>Monthly <strong class="tabular-nums">₱{{ $money($plan->monthly_amount) }}</strong>
                    <span class="opacity-60">× {{ $plan->terms }}</span></span>
                <span class="opacity-60">Contract
                    <strong class="tabular-nums">₱{{ $money($summary['total_contract']) }}</strong></span>
                @if ($plan->starts_on)
                    <span class="opacity-60">Starts {{ $plan->starts_on->format('d/m/Y') }}</span>
                @endif
            </div>
        @else
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <span class="badge badge-warning badge-sm">No billing plan</span>
                <span class="opacity-70">This agency has no agreement set up yet, so there is nothing to pay against.</span>
            </div>
        @endif
    </div>
</div>

{{-- ── filters ────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('godmode.payments.show', $agency) }}"
      class="mt-4 flex flex-wrap items-end gap-3" data-filter-form="payment-ledger">
    <label class="form-control">
        <span class="label-text text-xs opacity-70">Type</span>
        <select name="type" class="select select-bordered select-sm">
            @foreach ($typeFilters as $t)
                <option value="{{ $t }}" @selected($filters['type'] === $t)>
                    {{ $t === 'all' ? 'All types' : $typeLabel($t) }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-control">
        <span class="label-text text-xs opacity-70">From</span>
        <input type="date" name="from" value="{{ $filters['from'] }}" class="input input-bordered input-sm">
    </label>

    <label class="form-control">
        <span class="label-text text-xs opacity-70">To</span>
        <input type="date" name="to" value="{{ $filters['to'] }}" class="input input-bordered input-sm">
    </label>

    <label class="form-control">
        <span class="label-text text-xs opacity-70">Rows</span>
        <select name="per" class="select select-bordered select-sm">
            @foreach ($perOptions as $per)
                <option value="{{ $per }}" @selected($filters['per'] === $per)>{{ $per }}</option>
            @endforeach
        </select>
    </label>

    <label class="label cursor-pointer gap-2 py-2">
        <input type="checkbox" name="voided" value="1" class="checkbox checkbox-sm"
               @checked($filters['voided']) data-filter="include-voided">
        <span class="label-text">Include voided</span>
    </label>

    <button type="submit" class="btn btn-sm btn-primary">Apply</button>

    @if ($isFiltered)
        <a href="{{ route('godmode.payments.show', $agency) }}" class="btn btn-sm btn-ghost">Reset</a>
    @endif
</form>

{{-- ── table ──────────────────────────────────────────────────────── --}}
<div class="mt-4 card bg-base-100 shadow-sm overflow-x-auto">
    @if ($payments->isEmpty())
        {{-- AC7: an empty ledger is a normal state, never an error. --}}
        <div class="p-10 text-center" data-empty-state="payment-ledger">
            @if ($isFiltered)
                <p class="font-semibold">No payments match these filters.</p>
                <p class="text-sm opacity-70 mt-1">
                    The agency does have payment history — widen the date range or clear the type filter.
                </p>
                <a href="{{ route('godmode.payments.show', $agency) }}" class="btn btn-sm btn-ghost mt-3">Clear filters</a>
            @elseif (! $hasAny)
                <p class="font-semibold">No payments recorded yet.</p>
                <p class="text-sm opacity-70 mt-1">
                    This agency has never paid Landas. Record one from the village modal to start the ledger.
                </p>
            @else
                <p class="font-semibold">No active payments.</p>
                <p class="text-sm opacity-70 mt-1">
                    Every payment on this account has been voided. Tick "Include voided" to see them.
                </p>
            @endif
        </div>
    @else
        <table class="table table-sm" data-table="payment-ledger">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th class="text-right">Amount</th>
                    <th>Mode</th>
                    <th>Reference</th>
                    <th>Particular</th>
                    <th>Recorded by</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $p)
                    @php
                        $isVoid = $p->deleted_at !== null;
                        $audit = $voidAudit[$p->id] ?? null;
                    @endphp
                    <tr class="{{ $isVoid ? 'opacity-50' : '' }}" data-payment-row="{{ $p->id }}"
                        data-voided="{{ $isVoid ? '1' : '0' }}">
                        <td class="whitespace-nowrap tabular-nums">
                            {{ $p->paid_on?->format('d/m/Y') }}
                        </td>
                        <td>
                            <span class="badge badge-sm {{ $p->payment_type === 'initial' ? 'badge-primary' : 'badge-ghost' }}">
                                {{ $typeLabel($p->payment_type) }}
                            </span>
                        </td>
                        <td class="text-right tabular-nums whitespace-nowrap {{ $isVoid ? 'line-through' : '' }}">
                            ₱{{ $money($p->amount) }}
                        </td>
                        <td>
                            <span class="badge badge-sm {{ $ledgerService->modeBadge($p->mode) }}">
                                {{ $p->mode }}
                            </span>
                        </td>
                        <td class="text-xs">{{ $p->reference ?: '—' }}</td>
                        <td class="text-xs max-w-[16rem] truncate" title="{{ $p->particular }}">
                            {{ $p->particular ?: '—' }}
                        </td>
                        <td class="text-xs">{{ $p->recorder?->name ?? '—' }}</td>
                        <td class="text-xs">
                            @if ($isVoid)
                                <span class="badge badge-sm badge-error badge-outline">Voided</span>
                                {{-- Attribution does not live on the payment row; it
                                     is read from the void's audit event. Labelled so
                                     nobody assumes the column came from the row. --}}
                                <div class="opacity-70 mt-1">
                                    @if ($audit)
                                        by {{ $audit['by'] ?? 'unknown' }}<br>
                                        <span class="opacity-70">{{ $audit['at'] }}</span>
                                    @else
                                        <span title="The void audit event was not found">attribution unavailable</span>
                                    @endif
                                </div>
                            @else
                                <span class="badge badge-sm badge-success badge-outline">Active</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                {{-- AC4: these totals come from the same filtered rows the table
                     renders, so they can never disagree with the page above. --}}
                <tr class="font-semibold">
                    <td colspan="2">Total active ({{ number_format($totals['active_count']) }})</td>
                    <td class="text-right tabular-nums">₱{{ $money($totals['active']) }}</td>
                    <td colspan="5"></td>
                </tr>
                @if ($filters['voided'])
                    <tr class="font-semibold opacity-60">
                        <td colspan="2">Total voided ({{ number_format($totals['voided_count']) }})</td>
                        <td class="text-right tabular-nums line-through">₱{{ $money($totals['voided']) }}</td>
                        <td colspan="5"></td>
                    </tr>
                    <tr class="font-semibold">
                        <td colspan="2">Net</td>
                        <td class="text-right tabular-nums" data-total="net">₱{{ $money($totals['net']) }}</td>
                        <td colspan="5"></td>
                    </tr>
                @endif
            </tfoot>
        </table>
    @endif
</div>

@if ($payments->hasPages())
    <div class="mt-4">{{ $payments->links() }}</div>
@endif
@endsection
