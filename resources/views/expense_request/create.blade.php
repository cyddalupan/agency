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
                        <option value="">- select -</option>
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
                        'mains'           => $mains,
                        'allAccounts'     => $allAccounts,
                    ])
                @endforeach
            </div>

            <div class="flex gap-2">
                <button type="button" id="addLine" class="btn btn-outline btn-sm">+ Add Line</button>
            </div>

            {{-- Real-time totals: Amount − Payments = Net --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
                <div class="bg-base-200/60 border border-base-300 rounded-lg p-3">
                    <p class="text-xs opacity-60 uppercase tracking-wider font-semibold">Total Amount</p>
                    <p class="text-xl font-extrabold mt-1" id="totalAmount">₱ 0.00</p>
                </div>
                <div class="bg-base-200/60 border border-base-300 rounded-lg p-3">
                    <p class="text-xs opacity-60 uppercase tracking-wider font-semibold">Total Payments</p>
                    <p class="text-xl font-extrabold mt-1" id="totalPayment">₱ 0.00</p>
                </div>
                <div class="bg-primary/10 border border-primary/40 rounded-lg p-3">
                    <p class="text-xs opacity-60 uppercase tracking-wider font-semibold">Net (Amount − Payments)</p>
                    <p class="text-xl font-extrabold mt-1" id="totalNet">₱ 0.00</p>
                </div>
            </div>

    {{-- Notes --}}
    <div class="form-control mt-4">
        <label class="label"><span class="label-text">Notes</span></label>
        <textarea name="notes" class="textarea textarea-bordered" rows="2" placeholder="Optional">{{ old('notes') }}</textarea>
    </div>

    <div class="card-actions justify-end mt-4">
        <a href="{{ route('expense_request.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
        <span id="dupChecking" class="hidden items-center gap-2 text-sm opacity-70 mr-2">
            <span class="loading loading-spinner loading-sm"></span>
            <span>Checking for duplicate transaction…</span>
        </span>
        <button type="submit" id="saveBtn" class="btn btn-primary btn-sm">Save Request</button>
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
        'mains' => $mains,
        'allAccounts' => $allAccounts,
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

    function updateAccountType(line) {
        const charge = line.querySelector('select[name*="[charge]"]')?.value;
        const applicantSel = line.querySelector('select[name*="[applicant_id]"]');
        const subSel = line.querySelector('select[name*="[sub_account_id]"]');
        if (!subSel) return;

        // Cache the full option list once (placeholder + all accounts).
        if (!subSel.dataset.fullOptions) {
            const opts = [];
            subSel.querySelectorAll('option').forEach(function (o) {
                opts.push({ value: o.value, text: o.textContent, offset: o.dataset.offset || '' });
            });
            subSel.dataset.fullOptions = JSON.stringify(opts);
        }

        // Toybits 2026-08-16: one group per line, depending on Charge + Applicant:
        //   Charge = agent                -> agent accounts only
        //   Charge = office, no applicant -> office accounts only
        //   Charge = office + applicant   -> applicant accounts only
        let group = 'office';
        if (charge === 'agent') group = 'agent';
        else if (applicantSel && applicantSel.value) group = 'applicant';

        const all = JSON.parse(subSel.dataset.fullOptions);
        const selected = subSel.value;

        // Rebuild: keep the placeholder (empty value) + options matching the group.
        subSel.innerHTML = '';
        all.forEach(function (o) {
            if (o.value === '' || o.offset === group) {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.textContent = o.text;
                if (o.offset) opt.dataset.offset = o.offset;
                subSel.appendChild(opt);
            }
        });

        // Restore the previous selection if it still belongs to the group.
        if (selected && Array.from(subSel.options).some(function (o) { return o.value === selected; })) {
            subSel.value = selected;
        } else {
            subSel.value = '';
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
        linesBox.querySelectorAll('.expense-line').forEach(updateAccountType);
        const last = linesBox.lastElementChild;
        if (last) last.scrollIntoView({ block: 'nearest' });
    });

    // Real-time totals: Amount − Payments = Net (Toybits 2026-08-29)
    function calcTotals() {
        let amount = 0, payment = 0;
        linesBox.querySelectorAll('.expense-line').forEach(function (line) {
            const amt = line.querySelector('input[name*="[amount]"]');
            const pay = line.querySelector('input[name*="[payment]"]');
            amount  += parseFloat(amt && amt.value ? amt.value : 0) || 0;
            payment += parseFloat(pay && pay.value ? pay.value : 0) || 0;
        });
        const fmt = function (n) {
            return '₱ ' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        document.getElementById('totalAmount').textContent  = fmt(amount);
        document.getElementById('totalPayment').textContent = fmt(payment);
        document.getElementById('totalNet').textContent     = fmt(amount - payment);
    }

    linesBox.addEventListener('input', function (e) {
        if (e.target.name && (e.target.name.includes('[amount]') || e.target.name.includes('[payment]'))) {
            calcTotals();
        }
    });

    linesBox.addEventListener('change', function (e) {
        if (e.target.name && e.target.name.includes('[charge]')) {
            updateAccountType(e.target.closest('.expense-line'));
        }
        // Applicant cascades: only show applicants under the selected agent
        if (e.target.name && e.target.name.includes('[agent_id]')) {
            filterApplicantsByAgent(e.target.closest('.expense-line'));
        }
        // Account Type group depends on the applicant (office + applicant -> applicant)
        if (e.target.name && e.target.name.includes('[applicant_id]')) {
            updateAccountType(e.target.closest('.expense-line'));
        }
    });

    linesBox.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-line')) {
            const box = linesBox;
            if (box.querySelectorAll('.expense-line').length <= 1) return; // keep at least one
            e.target.closest('.expense-line').remove();
            reindex(box);
            calcTotals();
        }
    });

    // Initial state (re-applies the agent filter too, so a validation-error
    // re-render keeps applicants of the unselected agents hidden)
    linesBox.querySelectorAll('.expense-line').forEach(function (line) {
        filterApplicantsByAgent(line);
        updateAccountType(line);
    });
    calcTotals();

    // Duplicate check on save (Toybits 2026-08-16): intercept submit, show a
    // loading message while checking, then confirm if a duplicate is found.
    const form = document.querySelector('form[action*="expense-request"]');
    const saveBtn = document.getElementById('saveBtn');
    const dupChecking = document.getElementById('dupChecking');
    let checking = false;

    function collectLines() {
        const lines = [];
        linesBox.querySelectorAll('.expense-line').forEach(function (line) {
            const applicantSel = line.querySelector('select[name*="[applicant_id]"]');
            const amountInput = line.querySelector('input[name*="[amount]"]');
            const amount = amountInput ? amountInput.value.trim() : '';
            if (amount === '') return; // skip empty lines
            lines.push({
                applicant_id: applicantSel ? applicantSel.value : '',
                amount: amount,
            });
        });
        return lines;
    }

    form.addEventListener('submit', function (e) {
        if (checking) {
            e.preventDefault();
            return;
        }
        const lines = collectLines();
        if (lines.length === 0) return; // let normal validation handle it

        e.preventDefault();
        checking = true;
        saveBtn.disabled = true;
        const shownAt = Date.now();
        dupChecking.classList.remove('hidden');
        dupChecking.classList.add('flex');

        fetch('{{ route('expense_request.check_duplicates') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            },
            body: JSON.stringify({ lines: lines }),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            // Keep the "Checking for duplicate transaction…" message visible for
            // at least ~600ms so users actually see it (the check is near-instant).
            const remain = 600 - (Date.now() - shownAt);
            setTimeout(function () {
                checking = false;
                saveBtn.disabled = false;
                dupChecking.classList.add('hidden');
                dupChecking.classList.remove('flex');

                if (data.duplicate) {
                    const ok = confirm('This transaction is the same as the last transaction. Do you want to continue?');
                    if (!ok) return; // abort save
                }
                form.submit();
            }, Math.max(0, remain));
        })
        .catch(function () {
            // Network hiccup — don't block saving, just submit normally.
            checking = false;
            saveBtn.disabled = false;
            dupChecking.classList.add('hidden');
            dupChecking.classList.remove('flex');
            form.submit();
        });
    });
})();
</script>
@endsection
