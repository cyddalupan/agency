@extends('layouts.app')

@section('title', 'Expenses & Payments')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6 flex flex-row items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">💸 Expenses &amp; Payments</h1>
                <p class="opacity-80 mt-1">Track and manage expenses</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('expense_request.bulk') }}" class="btn btn-outline btn-sm shadow-md" title="Bulk upload expense requests from CSV">
                    <span>📥</span> Bulk Upload
                </a>
                <a href="{{ route('expense_request.create') }}" class="btn btn-secondary btn-sm shadow-md">+ New Expense Request</a>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">🧾 Requests</p>
                <p class="text-2xl font-bold">{{ $allRequests->count() }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">💰 PHP Total</p>
                <p class="text-2xl font-bold">₱{{ number_format($phpTotal, 2) }}</p>
                <p class="text-xs opacity-60">USD: ${{ number_format($usdTotal, 2) }} ≈ ₱{{ number_format($totalAmount, 2) }}</p>
                @php
                    $statusIcons = [
                        'pending'       => '⏳',
                        'approved'      => '🟡',
                        'for_releasing' => '🟣',
                        'released'      => '✅',
                        'cancelled'     => '❌',
                    ];
                @endphp
                <div class="mt-2 pt-2 border-t border-base-200 text-xs opacity-70 space-y-0.5">
                    {{-- Toybits 2026-09-23: one line per status (now incl. Cancelled);
                         the right-side selector shows/hides each by data-status-summary. --}}
                    @foreach($statusTotals as $statusKey => $st)
                        <p class="status-summary" data-status-summary="{{ $statusKey }}">
                            {{ $statusIcons[$statusKey] }} {{ \App\Models\ExpenseRequest::STATUS_LABELS[$statusKey] }}:
                            ₱{{ number_format($st['PHP'], 2) }}
                            <span class="opacity-50">(USD ${{ number_format($st['USD'], 2) }})</span>
                            <span class="badge badge-xs badge-ghost">{{ $st['count'] }}</span>
                        </p>
                    @endforeach
                    <p class="pt-1">🏢 Office: ₱{{ number_format($chargeTotals['office'] ?? 0, 2) }} · 🧑 Agent: ₱{{ number_format($chargeTotals['agent'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">✅ Released</p>
                <p class="text-2xl font-bold text-success">{{ $allRequests->where('status', 'released')->count() }}</p>
            </div>
        </div>
    </div>

    {{-- Success flash --}}
    @if(session('success'))
        <div class="alert alert-success shadow-md mb-4">
            <span>✅ {{ session('success') }}</span>
        </div>
    @endif

    {{-- Status tabs (Toybits 2026-08-18): filter the table by payment status --}}
    <div class="status-tabs mb-4">
        <div class="card bg-base-100 shadow-md border border-base-200">
            <div class="card-body p-2.5">
                <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                    <div class="flex flex-nowrap items-center gap-1.5 overflow-x-auto lg:flex-1" role="tablist">
                    {{-- All --}}
                    <a href="{{ route('expense_request.index', array_filter(['encoder' => $activeEncoder])) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-sm font-semibold whitespace-nowrap shrink-0 transition-all duration-200 {{ $activeStatus === null ? 'bg-[#0f1724] text-white shadow-md ring-2 ring-primary/60' : 'bg-base-200/70 text-base-content/70 hover:bg-base-200 hover:text-base-content' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        All
                        <span class="badge badge-sm {{ $activeStatus === null ? 'badge-primary' : 'badge-ghost' }}">{{ $allRequests->count() }}</span>
                    </a>

                    @php
                        $tabStyles = [
                            'pending'       => ['active' => 'bg-amber-500 text-white shadow-md shadow-amber-500/40 ring-2 ring-amber-300/70',          'badge' => 'badge-warning'],
                            'approved'      => ['active' => 'bg-sky-500 text-white shadow-md shadow-sky-500/40 ring-2 ring-sky-300/70',             'badge' => 'badge-info'],
                            'for_releasing' => ['active' => 'bg-violet-500 text-white shadow-md shadow-violet-500/40 ring-2 ring-violet-300/70',    'badge' => 'badge-primary'],
                            'released'      => ['active' => 'bg-emerald-500 text-white shadow-md shadow-emerald-500/40 ring-2 ring-emerald-300/70', 'badge' => 'badge-success'],
                            'cancelled'     => ['active' => 'bg-rose-500 text-white shadow-md shadow-rose-500/40 ring-2 ring-rose-300/70',          'badge' => 'badge-error'],
                        ];
                        $tabIcons = [
                            'pending'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/><circle cx="12" cy="12" r="9"/>',
                            'approved'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>',
                            'for_releasing' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/>',
                            'released'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 10h18M3 14h18M3 18h18"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 2l2 2M17 2l2 2"/>',
                            'cancelled'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/>',
                        ];
                    @endphp

                    @foreach(\App\Models\ExpenseRequest::STATUSES as $statusKey)
                        @php
                            $isActive = $activeStatus === $statusKey;
                        @endphp
                        <a href="{{ route('expense_request.index', array_filter(['status' => $statusKey, 'encoder' => $activeEncoder])) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-sm font-semibold whitespace-nowrap shrink-0 transition-all duration-200 {{ $isActive ? $tabStyles[$statusKey]['active'] : 'bg-base-200/70 text-base-content/70 hover:bg-base-200 hover:text-base-content' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">{!! $tabIcons[$statusKey] !!}</svg>
                            {{ \App\Models\ExpenseRequest::STATUS_LABELS[$statusKey] }}
                            <span class="badge badge-sm {{ $isActive ? 'bg-white/20 text-white border border-white/30' : $tabStyles[$statusKey]['badge'] }}">{{ $statusCounts[$statusKey] }}</span>
                        </a>
                    @endforeach
                    </div>

                    {{-- Dashboard status selector (Toybits 2026-09-23): tick which statuses show in the summary card above. --}}
                    <div class="shrink-0 lg:border-l lg:border-base-200 lg:pl-4 flex flex-wrap items-center gap-x-3 gap-y-2">
                        <span class="text-xs font-semibold opacity-60 whitespace-nowrap">📊 Dashboard totals:</span>
                        @foreach(\App\Models\ExpenseRequest::STATUSES as $statusKey)
                            <label class="inline-flex items-center gap-2 text-xs font-medium cursor-pointer whitespace-nowrap rounded-full border border-base-300 bg-base-100 px-3 py-1 hover:bg-base-200 transition-colors" title="Show {{ \App\Models\ExpenseRequest::STATUS_LABELS[$statusKey] }} in the summary">
                                <input type="checkbox" class="checkbox checkbox-xs checkbox-primary status-filter" data-status="{{ $statusKey }}" checked>
                                {{ \App\Models\ExpenseRequest::STATUS_LABELS[$statusKey] }}
                            </label>
                        @endforeach
                    </div>

                    {{-- Encoder filter (Toybits 2026-10-07): narrow to one encoder, keeps the active status tab. --}}
                    @if($encoders->count())
                        <div class="shrink-0 lg:border-l lg:border-base-200 lg:pl-4 flex flex-wrap items-center gap-2">
                            <form method="GET" action="{{ route('expense_request.index') }}" class="flex items-center gap-2">
                                @if($activeStatus)
                                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                                @endif
                                <label for="encoder-filter" class="text-xs font-semibold opacity-60 whitespace-nowrap">🔎 Encoder:</label>
                                <select name="encoder" id="encoder-filter" class="select select-xs select-bordered" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    @foreach($encoders as $encoder)
                                        <option value="{{ $encoder->id }}" @selected((string) $activeEncoder === (string) $encoder->id)>{{ $encoder->name }}</option>
                                    @endforeach
                                </select>
                                @if($activeEncoder)
                                    <a href="{{ route('expense_request.index', array_filter(['status' => $activeStatus])) }}" class="btn btn-ghost btn-xs" title="Clear encoder filter">✕</a>
                                @endif
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Requests table --}}
    <div class="card bg-base-100 shadow-md">
        <div class="card-body">
            <h3 class="font-bold mb-3">Expense Requests</h3>
            @if($requests->count())
                @if(auth()->user()->canChangeExpenseStatus())
                    {{-- Batch status update toolbar (Toybits 2026-08-31) --}}
                    <form method="POST" action="{{ route('expense_request.bulk_status') }}" id="bulk-status-form" class="mb-3">
                        @csrf
                        <div class="flex flex-wrap items-center gap-2 bg-base-200/60 rounded-lg px-3 py-2">
                            <span class="text-sm font-semibold">⚡ Bulk update:</span>
                            <select name="status" class="select select-sm select-bordered" required>
                                <option value="" disabled selected>Change status to…</option>
                                @foreach(\App\Models\ExpenseRequest::STATUSES as $statusKey)
                                    <option value="{{ $statusKey }}">{{ \App\Models\ExpenseRequest::STATUS_LABELS[$statusKey] }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary" id="bulk-apply-btn" disabled>Apply to selected</button>
                            <span id="bulk-selected-count" class="text-sm opacity-60 ml-auto">0 selected</span>
                        </div>
                        {{-- Live per-status totals for the ticked transactions (Toybits 2026-09-23). --}}
                        <div id="selected-status-totals" class="hidden mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2 text-sm">
                            <span class="font-semibold text-emerald-700">🧮 Selected totals:</span>
                            @foreach(\App\Models\ExpenseRequest::STATUSES as $statusKey)
                                <span class="sel-status-total hidden" data-sel-status="{{ $statusKey }}" data-label="{{ \App\Models\ExpenseRequest::STATUS_LABELS[$statusKey] }}">{{ \App\Models\ExpenseRequest::STATUS_LABELS[$statusKey] }}: ₱0.00</span>
                            @endforeach
                        </div>
                    </form>
                @endif
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr class="bg-base-200/70">
                                @if(auth()->user()->canChangeExpenseStatus())
                                    <th class="w-8"><input type="checkbox" id="select-all" class="checkbox checkbox-sm checkbox-primary" title="Select all"></th>
                                @endif
                                <th>Ref#</th>
                                <th>Date</th>
                                <th>User</th>
                                <th>Status</th>
                                <th>Offices</th>
                                <th>Applicant</th>
                                <th>Agent</th>
                                <th>Currency</th>
                                <th class="text-right">Amount</th>
                                <th>Account</th>
                                <th>Country</th>
                                <th>Particular / Description</th>
                                <th>Charge</th>
                                @if(in_array(auth()->user()->user_type, ['super_admin', 'admin', 'billing']))
                                    <th>Review</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                                @foreach($request->items as $item)
                                    @php
                                        $dupKey = number_format((float) ($item->amount - ($item->payment ?? 0)), 2) . '|' . ($item->applicant_id ?? 'null');
                                        $isDup = in_array($dupKey, $duplicateKeys, true);
                                    @endphp
                                    <tr class="{{ $isDup ? 'duplicate-row bg-warning/25' : '' }}">
                                        {{-- Select checkbox spans the whole request's rows, so only emit it on the first line item. --}}
                                        @if($loop->first && auth()->user()->canChangeExpenseStatus())
                                            <td rowspan="{{ $request->items->count() }}">
                                                <input type="checkbox" name="ids[]" value="{{ $request->id }}" form="bulk-status-form" class="checkbox checkbox-sm checkbox-primary request-checkbox"
                                                       data-status="{{ $request->status }}"
                                                       data-php="{{ (float) $request->items->where('currency', 'PHP')->sum('amount') }}"
                                                       data-usd="{{ (float) $request->items->where('currency', 'USD')->sum('amount') }}"
                                                       title="Select {{ $request->reference_no }}">
                                            </td>
                                        @endif
                                        @if($loop->first)
                                            <td class="font-mono" rowspan="{{ $request->items->count() }}">{{ $request->reference_no }}</td>
                                            <td rowspan="{{ $request->items->count() }}">{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                                            <td rowspan="{{ $request->items->count() }}">{{ $request->user?->name ?? $request->user?->username ?? '—' }}</td>
                                            <td rowspan="{{ $request->items->count() }}">
                                                <span class="badge badge-sm {{ $request->statusBadge() }}">{{ $request->statusLabel() }}</span>
                                            </td>
                                        @endif
                                        @if($loop->first)
                                            <td rowspan="{{ $request->items->count() }}">{{ $request->branch?->name ?? '—' }}</td>
                                        @endif
                                        <td>{{ $item->applicant ? $item->applicant->last_name . ', ' . $item->applicant->first_name : '—' }}
                                            @if($isDup)
                                                <span class="badge badge-sm badge-warning ml-1" title="Same amount + applicant as another transaction">Duplicate</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->agent?->name ?? '—' }}</td>
                                        <td>{{ $item->currency }}</td>
                                        <td class="text-right font-semibold">
                                            {{ $item->currency === 'USD' ? '$' : '₱' }}{{ number_format((float) ($item->amount - ($item->payment ?? 0)), 2) }}
                                            @if((float) ($item->payment ?? 0) > 0)
                                                <span class="block text-xs font-normal opacity-60" title="Original {{ number_format((float) $item->amount, 2) }} less payment {{ number_format((float) $item->payment, 2) }}">
                                                    gross {{ number_format((float) $item->amount, 2) }} − pay {{ number_format((float) $item->payment, 2) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $item->account?->name ?? '—' }}</td>
                                        <td>{{ $item->country?->name ?? '—' }}</td>
                                        <td>{{ $item->particular ?? '—' }}</td>
                                        <td>
                                            <span class="badge badge-sm {{ $item->charge === 'office' ? 'badge-ghost' : 'badge-info' }}">
                                                {{ $item->charge }}
                                            </span>
                                        </td>
                                        @if(in_array(auth()->user()->user_type, ['super_admin', 'admin', 'billing']) && $loop->first)
                                            <td rowspan="{{ $request->items->count() }}">
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('expense_request.show', $request) }}" class="link link-primary">Review</a>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                @if($request->items->isEmpty())
                                    <tr>
                                        @if(auth()->user()->canChangeExpenseStatus())
                                            <td>
                                                <input type="checkbox" name="ids[]" value="{{ $request->id }}" form="bulk-status-form" class="checkbox checkbox-sm checkbox-primary request-checkbox"
                                                       data-status="{{ $request->status }}" data-php="0" data-usd="0"
                                                       title="Select {{ $request->reference_no }}">
                                            </td>
                                        @endif
                                        <td class="font-mono">{{ $request->reference_no }}</td>
                                        <td>{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                                        <td>{{ $request->user?->name ?? $request->user?->username ?? '—' }}</td>
                                        <td colspan="{{ in_array(auth()->user()->user_type, ['super_admin', 'admin', 'billing']) ? 11 : 10 }}" class="opacity-50">No line items</td>
                                    </tr>
                                @endif

                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="opacity-60">No expense requests yet. <a href="{{ route('expense_request.create') }}" class="link link-primary">Create one</a>.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Dashboard status selector (all users): tick which statuses show in the summary above. --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusFilters = document.querySelectorAll('.status-filter');

    window.__enabledStatuses = function () {
        const set = {};
        statusFilters.forEach(function (f) { set[f.dataset.status] = f.checked; });
        return set;
    };
    window.__applyStatusFilter = function () {
        const enabled = window.__enabledStatuses();
        document.querySelectorAll('[data-status-summary]').forEach(function (el) {
            el.classList.toggle('hidden', !enabled[el.dataset.statusSummary]);
        });
        if (window.__updateSelectedTotals) window.__updateSelectedTotals();
    };
    statusFilters.forEach(function (f) { f.addEventListener('change', window.__applyStatusFilter); });
    window.__applyStatusFilter();
});
</script>
@if(auth()->user()->canChangeExpenseStatus())
<script>
// Live per-status totals for the ticked transactions (Toybits 2026-09-23).
// Batch status update select-all + live count (Toybits 2026-08-31).
document.addEventListener('DOMContentLoaded', function () {
    const boxes = document.querySelectorAll('.request-checkbox');

    function fmtPeso(n) {
        return '\u20b1' + (n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function fmtUsd(n) {
        return 'USD $' + (n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Totals of the ticked transactions, grouped by status. Respects the
    // dashboard status selector (only enabled statuses are shown).
    window.__updateSelectedTotals = function () {
        const enabled = window.__enabledStatuses ? window.__enabledStatuses() : {};
        const php = {}, usd = {};
        document.querySelectorAll('.request-checkbox:checked').forEach(function (b) {
            const s = b.dataset.status;
            php[s] = (php[s] || 0) + parseFloat(b.dataset.php || '0');
            usd[s] = (usd[s] || 0) + parseFloat(b.dataset.usd || '0');
        });
        let any = false;
        document.querySelectorAll('.sel-status-total').forEach(function (el) {
            const s = el.dataset.selStatus;
            if (enabled[s]) {
                const p = php[s] || 0, u = usd[s] || 0;
                el.textContent = el.dataset.label + ': ' + fmtPeso(p) + (u ? ' (' + fmtUsd(u) + ')' : '');
                el.classList.remove('hidden');
                if (p > 0 || u > 0) any = true;
            } else {
                el.classList.add('hidden');
            }
        });
        const panel = document.getElementById('selected-status-totals');
        if (panel) panel.classList.toggle('hidden', !any);
    };

    const selectAll = document.getElementById('select-all');
    function updateBulk() {
        const checked = document.querySelectorAll('.request-checkbox:checked').length;
        const countEl = document.getElementById('bulk-selected-count');
        const applyBtn = document.getElementById('bulk-apply-btn');
        if (countEl) countEl.textContent = checked + ' selected';
        if (applyBtn) applyBtn.disabled = checked === 0;
        if (selectAll) {
            selectAll.checked = checked === boxes.length && boxes.length > 0;
            selectAll.indeterminate = checked > 0 && checked < boxes.length;
        }
    }
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            boxes.forEach(function (b) { b.checked = selectAll.checked; });
            updateBulk();
            window.__updateSelectedTotals();
        });
    }
    boxes.forEach(function (b) {
        b.addEventListener('change', function () { updateBulk(); window.__updateSelectedTotals(); });
    });

    updateBulk();
    window.__updateSelectedTotals();
});
</script>
@endif
@endpush
