@extends('layouts.app')

@section('title', 'New Expense Request')

@section('content')
<div class="max-w-5xl mx-auto">

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <h1 class="text-2xl font-bold">➕ New Expense Request</h1>
            <p class="opacity-80 mt-1">Tab 2 — Save Request (multi-line)</p>
        </div>
    </div>

    @if(in_array(auth()->user()->user_type ?? '', ['super_admin', 'admin', 'billing']))
        <div class="tabs tabs-boxed bg-base-200/60 mb-6 w-fit">
            <a href="{{ route('receivable.index') }}" class="tab text-sm">Tab 1 · Receivable</a>
            <a href="{{ route('expense_request.index') }}" class="tab text-sm">Tab 2 · Expenses &amp; Payments</a>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error shadow-md mb-6">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('expense_request.store') }}" class="card bg-base-100 shadow-md" enctype="multipart/form-data">
        @csrf

        <div class="card-body">
            {{-- Row: Date / Branch --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Date</span></label>
                    <input type="date" name="date" class="input input-bordered" value="{{ old('date', now()->toDateString()) }}">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Branch</span></label>
                    <select name="branch_id" id="branch_id" class="select select-bordered">
                        <option value="">— (all branches) —</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Line items --}}
            <h3 class="font-bold mt-2 mb-2">Line Items</h3>
            <div id="lines" class="space-y-4">
                @php $oldLines = old('lines', []); @endphp
                @foreach($oldLines ? array_keys($oldLines) : [0] as $li)
                    @php $li = (int) $li; @endphp
                    @include('expense_request._line', [
                        'line'            => $oldLines[$li] ?? [],
                        'index'           => $li,
                        'agents'          => $agents,
                        'applicants'      => $applicants,
                        'countries'       => $countries,
                        'officeAccounts'  => $officeAccounts,
                        'agentAccounts'   => $agentAccounts,
                    ])
                @endforeach
            </div>

            <div class="flex gap-2">
                <button type="button" id="addLine" class="btn btn-outline btn-sm">+ Add Line</button>
            </div>

    {{-- Notes --}}
    <div class="form-control mt-4">
        <label class="label"><span class="label-text">Notes</span></label>
        <textarea name="notes" class="textarea textarea-bordered" rows="2" placeholder="Optional">{{ old('notes') }}</textarea>
    </div>

    <div class="card-actions justify-end mt-4">
        <a href="{{ route('expense_request.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
        <button type="submit" class="btn btn-primary btn-sm">Save Request</button>
    </div>
        </div>
    </form>
</div>

{{-- Hidden template for cloning new line items --}}
<template id="lineTemplate">
    @include('expense_request._line', [
        'line' => [],
        'index' => 0,
        'agents' => $agents,
        'applicants' => $applicants,
        'countries' => $countries,
        'officeAccounts' => $officeAccounts,
        'agentAccounts' => $agentAccounts,
    ])
</template>

<script>
(function () {
    const linesBox = document.getElementById('lines');
    const addBtn = document.getElementById('addLine');

    function reindex(box) {
        box.querySelectorAll('.expense-line').forEach(function (el, i) {
            el.dataset.index = i;
            el.querySelectorAll('[name]').forEach(function (input) {
                input.name = input.name.replace(/lines\[[0-9]+\]/, 'lines[' + i + ']');
            });
            const h = el.querySelector('h4');
            if (h) h.textContent = 'Line #' + (i + 1);
        });
    }

    function applyChargeVisibility(line) {
        const charge = line.querySelector('select[name*="[charge]"]')?.value;
        const agentRow = line.querySelector('[data-agent-row]');
        const accountSel = line.querySelector('[data-account-group]');
        if (agentRow) agentRow.style.display = (charge === 'agent') ? '' : 'none';
        if (accountSel) {
            accountSel.querySelectorAll('option[data-offset]').forEach(function (o) {
                o.disabled = (o.dataset.offset !== charge);
            });
            const sel = accountSel.selectedOptions[0];
            if (sel && sel.disabled) accountSel.value = '';
        }
    }

    // Applicant cascades: only show applicants under the selected agent.
    function filterApplicantsByAgent(line) {
        const agentSel = line.querySelector('select[name*="[agent_id]"]');
        const applicantSel = line.querySelector('select[name*="[applicant_id]"]');
        if (!agentSel || !applicantSel) return;
        const agentVal = agentSel.value || '';
        applicantSel.querySelectorAll('option[data-agent]').forEach(function (o) {
            const visible = !agentVal || o.dataset.agent === agentVal;
            o.style.display = visible ? '' : 'none';
        });
        const sel = applicantSel.selectedOptions[0];
        if (sel && sel.style.display === 'none') applicantSel.value = '';
    }

    addBtn.addEventListener('click', function () {
        const tpl = document.getElementById('lineTemplate');
        const node = tpl.content.cloneNode(true);
        linesBox.appendChild(node);
        reindex(linesBox);
        // Re-apply visibility on all lines after reindex
        linesBox.querySelectorAll('.expense-line').forEach(applyChargeVisibility);
        const last = linesBox.lastElementChild;
        if (last) last.scrollIntoView({ block: 'nearest' });
    });

    linesBox.addEventListener('change', function (e) {
        if (e.target.name && e.target.name.includes('[charge]')) {
            applyChargeVisibility(e.target.closest('.expense-line'));
        }
        // Applicant cascades: only show applicants under the selected agent
        if (e.target.name && e.target.name.includes('[agent_id]')) {
            filterApplicantsByAgent(e.target.closest('.expense-line'));
        }
    });

    linesBox.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-line')) {
            const box = linesBox;
            if (box.querySelectorAll('.expense-line').length <= 1) return; // keep at least one
            e.target.closest('.expense-line').remove();
            reindex(box);
        }
    });

    // Initial state (re-applies the agent filter too, so a validation-error
    // re-render keeps applicants of the unselected agents hidden)
    linesBox.querySelectorAll('.expense-line').forEach(function (line) {
        applyChargeVisibility(line);
        filterApplicantsByAgent(line);
    });
})();
</script>
@endsection
