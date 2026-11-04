@extends('layouts.app')

@section('title', 'Receivable Module')

@section('content')
<div class="w-full">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6 flex flex-row items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">🧾 Receivable</h1>
                <p class="opacity-80 mt-1">Track and manage receivables</p>
            </div>
            <a href="{{ route('receivable.bulk') }}" class="btn btn-outline btn-sm shadow-md" title="Bulk upload receivables from CSV">📥 Bulk CSV</a>
            <a href="{{ route('receivable.create') }}" class="btn btn-secondary btn-sm shadow-md">+ New Receivable</a>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">📄 Transactions</p>
                <p class="text-2xl font-bold">{{ $receivables->count() }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">💰 Total Amount</p>
                <p class="text-2xl font-bold">₱{{ number_format($totalAmount, 2) }}</p>
                <div class="mt-2 pt-2 border-t border-base-200 text-xs opacity-70 space-y-0.5">
                    <p>⏳ Pending: ₱{{ number_format($pendingTotal, 2) }}</p>
                    <p>✅ Received: ₱{{ number_format($receivedTotal, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">✅ Received</p>
                <p class="text-2xl font-bold text-success">{{ $receivables->where('status', 'received')->count() }}</p>
            </div>
        </div>
    </div>

    {{-- Receivables table --}}
    <div class="card bg-base-100 shadow-md">
        <div class="card-body">
            <h3 class="font-bold mb-3">Transactions</h3>
            {{-- Search + encoder filter (Cyd 2026-11-04 card).
                 One box searches every column of the list — code/AR#, agent,
                 applicant, particular, account, mode, type, deposit account,
                 encoder, date and amount. --}}
            <form method="GET" action="{{ route('receivable.index') }}" class="flex flex-wrap items-center gap-2 mb-3">
                <div class="join">
                    <input type="search" name="q" value="{{ $activeSearch }}" class="input input-sm input-bordered join-item w-64"
                           placeholder="🔎 Search code, AR#, agent, applicant, particular…" autocomplete="off">
                    <button type="submit" class="btn btn-sm btn-primary join-item">Search</button>
                </div>
                @if($encoders->count())
                    <label for="encoder-filter" class="text-sm font-semibold ml-1">Encoder:</label>
                    <select name="encoder" id="encoder-filter" class="select select-sm select-bordered" onchange="this.form.submit()">
                        <option value="">All encoders</option>
                        @foreach($encoders as $encoder)
                            <option value="{{ $encoder->id }}" @selected((string) $activeEncoder === (string) $encoder->id)>{{ $encoder->name }}</option>
                        @endforeach
                    </select>
                @endif
                @if($activeSearch !== '' || $activeEncoder)
                    <a href="{{ route('receivable.index') }}" class="btn btn-ghost btn-sm">✕ Clear</a>
                @endif
                @if($activeSearch !== '')
                    <span class="text-xs opacity-60">{{ $receivables->count() }} transaction(s) match “{{ $activeSearch }}”</span>
                @endif
            </form>
            @if($receivables->count())
                @if(in_array(auth()->user()->user_type, ['super_admin', 'admin']))
                    {{-- Batch status update toolbar (Toybits 2026-08-31) --}}
                    <form method="POST" action="{{ route('receivable.bulk_status') }}" id="bulk-status-form" class="mb-3">
                        @csrf
                        <div class="flex flex-wrap items-center gap-2 bg-base-200/60 rounded-lg px-3 py-2">
                            <span class="text-sm font-semibold">⚡ Bulk update:</span>
                            <select name="status" class="select select-sm select-bordered" required>
                                <option value="" disabled selected>Change status to…</option>
                                <option value="{{ \App\Models\Receivable::STATUS_PENDING }}">Pending</option>
                                <option value="{{ \App\Models\Receivable::STATUS_RECEIVED }}">Received</option>
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
                                <th>Code</th>
                                <th>Date</th>
                                <th>User</th>
                                <th>Status</th>
                                <th>Ref#/AR#</th>
                                <th>Applicant</th>
                                <th>Agent</th>
                                <th class="text-right">Amount</th>
                                <th>Account</th>
                                <th>Particular</th>
                                <th>Mode</th>
                                <th>Type</th>
                                <th>Deposit Account</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receivables as $r)
                            <tr class="{{ $r->status === 'received' ? 'opacity-70' : '' }}">
                                @if(in_array(auth()->user()->user_type, ['super_admin', 'admin']))
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $r->id }}" form="bulk-status-form" class="checkbox checkbox-sm checkbox-primary request-checkbox" title="Select {{ $r->code }}">
                                    </td>
                                @endif
                                <td class="font-mono font-semibold">{{ $r->code }}</td>
                                <td>{{ $r->date->format('M d, Y') }}</td>
                                <td>{{ $r->encoder->name ?? '—' }}</td>
                                <td>
                                    @if($r->status === 'received')
                                        <span class="badge badge-success badge-sm">RECEIVED</span>
                                    @else
                                        <span class="badge badge-warning badge-sm">PENDING</span>
                                    @endif
                                </td>
                                <td>{{ $r->ref_ar ?? '—' }}</td>
                                <td>
                                    @if($r->applicant)
                                        {{ $r->applicant->first_name }} {{ $r->applicant->last_name }}
                                    @else —
                                    @endif
                                </td>
                                <td>{{ $r->agent->name ?? '—' }}</td>
                                <td class="text-right font-semibold">₱{{ number_format($r->amount, 2) }}</td>
                                <td>{{ $r->account ?? '—' }}</td>
                                <td class="max-w-[16rem] truncate" title="{{ $r->particular }}">{{ $r->particular ?? '—' }}</td>
                                <td>{{ $r->mode ?? '—' }}</td>
                                <td>{{ $r->type ?? '—' }}</td>
                                <td>{{ $r->debit_account ?? '—' }}</td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('receivable.show', $r->id) }}" class="btn btn-xs btn-ghost whitespace-nowrap inline-flex items-center gap-1">Review →</a>
                                        {{-- Admin + Accounting may edit (Cyd 2026-11-01) --}}
                                        @if(auth()->user()->canEditTransactions())
                                            <a href="{{ route('receivable.edit', $r->id) }}" class="btn btn-xs btn-outline whitespace-nowrap">✏️ Edit</a>
                                        @endif
                                        {{-- Admin-only delete (Cyd 2026-10-24) --}}
                                        @if(in_array(auth()->user()->user_type, ['super_admin', 'admin'], true))
                                            <button type="button" class="btn btn-xs btn-error btn-outline"
                                                    onclick="document.getElementById('delete-receivable-{{ $r->id }}').showModal()">Delete</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                @if($activeSearch !== '')
                    <p class="opacity-50 text-sm py-6 text-center">No transactions match “{{ $activeSearch }}”. <a href="{{ route('receivable.index') }}" class="link link-primary">Clear search</a></p>
                @else
                    <p class="opacity-50 text-sm py-6 text-center">No receivable transactions yet.</p>
                @endif
            @endif

            {{-- Admin-only delete dialogs (Cyd 2026-10-24): reason goes into the receivable history. --}}
            @if(in_array(auth()->user()->user_type, ['super_admin', 'admin'], true))
                @foreach($receivables as $r)
                    <dialog id="delete-receivable-{{ $r->id }}" class="modal">
                        <div class="modal-box">
                            <h3 class="font-bold text-lg">Delete {{ $r->code }}?</h3>
                            <p class="text-sm opacity-70 mt-1">The receivable is hidden from every list (soft delete). Tell us why.</p>
                            <form method="POST" action="{{ route('receivable.destroy', $r) }}" class="mt-4 space-y-3">
                                @csrf
                                @method('DELETE')
                                <textarea name="reason" class="textarea textarea-bordered w-full" rows="3" required placeholder="Reason for deleting this receivable"></textarea>
                                <div class="modal-action">
                                    <button type="button" class="btn btn-ghost" onclick="this.closest('dialog').close()">Cancel</button>
                                    <button type="submit" class="btn btn-error">Delete</button>
                                </div>
                            </form>
                        </div>
                    </dialog>
                @endforeach
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
