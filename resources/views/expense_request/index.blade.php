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
            <a href="{{ route('expense_request.create') }}" class="btn btn-secondary btn-sm shadow-md">+ New Expense Request</a>
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
                <div class="mt-2 pt-2 border-t border-base-200 text-xs opacity-70 space-y-0.5">
                    <p>⏳ Pending: ₱{{ number_format($pendingPhpTotal, 2) }} <span class="opacity-50">(USD ${{ number_format($pendingUsdTotal, 2) }})</span></p>
                    <p>🟡 Approved: ₱{{ number_format($approvedPhpTotal, 2) }} <span class="opacity-50">(USD ${{ number_format($approvedUsdTotal, 2) }})</span></p>
                    <p>🟣 For Releasing: ₱{{ number_format($forReleasingPhpTotal, 2) }} <span class="opacity-50">(USD ${{ number_format($forReleasingUsdTotal, 2) }})</span></p>
                    <p>✅ Released: ₱{{ number_format($releasedPhpTotal, 2) }} <span class="opacity-50">(USD ${{ number_format($releasedUsdTotal, 2) }})</span></p>
                    <p>🏢 Office: ₱{{ number_format($chargeTotals['office'] ?? 0, 2) }} · 🧑 Agent: ₱{{ number_format($chargeTotals['agent'] ?? 0, 2) }}</p>
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
                <div class="flex flex-nowrap items-center gap-1.5 overflow-x-auto" role="tablist">
                    {{-- All --}}
                    <a href="{{ route('expense_request.index') }}"
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
                        <a href="{{ route('expense_request.index', ['status' => $statusKey]) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-sm font-semibold whitespace-nowrap shrink-0 transition-all duration-200 {{ $isActive ? $tabStyles[$statusKey]['active'] : 'bg-base-200/70 text-base-content/70 hover:bg-base-200 hover:text-base-content' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">{!! $tabIcons[$statusKey] !!}</svg>
                            {{ \App\Models\ExpenseRequest::STATUS_LABELS[$statusKey] }}
                            <span class="badge badge-sm {{ $isActive ? 'bg-white/20 text-white border border-white/30' : $tabStyles[$statusKey]['badge'] }}">{{ $statusCounts[$statusKey] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Requests table --}}
    <div class="card bg-base-100 shadow-md">
        <div class="card-body">
            <h3 class="font-bold mb-3">Expense Requests</h3>
            @if($requests->count())
                @if(in_array(auth()->user()->user_type, ['super_admin', 'admin']))
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
                    </form>
                @endif
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr class="bg-base-200/70">
                                @if(in_array(auth()->user()->user_type, ['super_admin', 'admin']))
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
                                        @if(in_array(auth()->user()->user_type, ['super_admin', 'admin']))
                                            <td rowspan="{{ $request->items->count() }}">
                                                <input type="checkbox" name="ids[]" value="{{ $request->id }}" form="bulk-status-form" class="checkbox checkbox-sm checkbox-primary request-checkbox" title="Select {{ $request->reference_no }}">
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
                                        @if(in_array(auth()->user()->user_type, ['super_admin', 'admin']))
                                            <td>
                                                <input type="checkbox" name="ids[]" value="{{ $request->id }}" form="bulk-status-form" class="checkbox checkbox-sm checkbox-primary request-checkbox" title="Select {{ $request->reference_no }}">
                                            </td>
                                        @endif
                                        <td class="font-mono">{{ $request->reference_no }}</td>
                                        <td>{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                                        <td>{{ $request->user?->name ?? $request->user?->username ?? '—' }}</td>
                                        <td colspan="9" class="opacity-50">No line items</td>
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
@if(in_array(auth()->user()->user_type, ['super_admin', 'admin']))
<script>
// Batch status update (Toybits 2026-08-31): select-all toggle + live selected count.
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    if (! selectAll) return;

    const boxes = document.querySelectorAll('.request-checkbox');
    const countEl = document.getElementById('bulk-selected-count');
    const applyBtn = document.getElementById('bulk-apply-btn');

    function update() {
        const checked = document.querySelectorAll('.request-checkbox:checked').length;
        if (countEl) countEl.textContent = checked + ' selected';
        if (applyBtn) applyBtn.disabled = checked === 0;
        if (selectAll) {
            selectAll.checked = checked === boxes.length && boxes.length > 0;
            selectAll.indeterminate = checked > 0 && checked < boxes.length;
        }
    }

    selectAll.addEventListener('change', function () {
        boxes.forEach(function (b) { b.checked = selectAll.checked; });
        update();
    });
    boxes.forEach(function (b) { b.addEventListener('change', update); });
    update();
});
</script>
@endif
@endpush
