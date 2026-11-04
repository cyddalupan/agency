@extends('layouts.app')

@section('title', ($isEdit ?? false) ? 'Edit Expense Request ' . $record->reference_no : 'New Expense Request')

@section('content')
<div class="w-full">

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <h1 class="text-2xl font-bold">{{ ($isEdit ?? false) ? '✏️ Edit Expense Request ' . $record->reference_no : '➕ New Expense Request' }}</h1>
            <p class="opacity-80 mt-1">{{ ($isEdit ?? false) ? 'Tab 2 — Edit Transaction (multi-line)' : 'Tab 2 — Save Request (multi-line)' }}</p>
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

    <form method="POST" action="{{ ($isEdit ?? false) ? route('expense_request.update', $record) : route('expense_request.store') }}" class="card bg-base-100 shadow-md" enctype="multipart/form-data">
        @csrf
        @if($isEdit ?? false)
            @method('PUT')
        @endif

        <div class="card-body">
            {{-- Row: Date / Branch --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Date</span></label>
                    <input type="date" name="date" class="input input-bordered" value="{{ old('date', isset($record) ? $record->date->toDateString() : now()->toDateString()) }}">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Branch</span></label>
                    @if (auth()->user()->isBranchLocked() && ! auth()->user()->isMainOffice())
                        {{-- Branch account: no dropdown — branch is auto-set to the user's own branch.
                             Main Office users are exempt (they may pick any branch). --}}
                        <input type="hidden" name="branch_id" value="{{ $branches->first()?->id }}">
                        <div class="input input-bordered w-full opacity-80" aria-readonly="true">{{ $branches->first()?->name }}</div>
                    @else
                        <select name="branch_id" id="branch_id" class="select select-bordered">
                            <option value="">- select -</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id', $record->branch_id ?? '') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            {{-- Line items --}}
            <h3 class="font-bold mt-2 mb-2">Line Items</h3>
            <div id="lines" class="space-y-4">
                @php $oldLines = old('lines', $existingLines ?? []); @endphp
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
        <textarea name="notes" class="textarea textarea-bordered" rows="2" placeholder="Optional">{{ old('notes', $record->notes ?? '') }}</textarea>
    </div>

    <div class="card-actions justify-end mt-4">
        <a href="{{ ($isEdit ?? false) ? route('expense_request.show', $record) : route('expense_request.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
        <span id="dupChecking" class="hidden items-center gap-2 text-sm opacity-70 mr-2">
            <span class="loading loading-spinner loading-sm"></span>
            <span>Checking for duplicate transaction…</span>
        </span>
        <button type="submit" id="saveBtn" class="btn btn-primary btn-sm">{{ ($isEdit ?? false) ? 'Save Changes' : 'Save Request' }}</button>
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
    const IS_EDIT = {{ ($isEdit ?? false) ? 'true' : 'false' }};
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
        if (IS_EDIT) return; // editing keeps the original transaction; skip the duplicate check
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

@push('head')
<style>
    /* Searchable Account Type combobox (Toybits 2026-09-23): the flat account list is long,
       so the native <select> is kept for form submission/validation but visually hidden and
       driven by a type-to-filter combobox. Styling is plain CSS (not Tailwind utilities)
       because the app ships a prebuilt CSS bundle that won't contain newly-added classes. */
    .searchable-select { position: relative; }
    .searchable-select .ss-native {
        position: absolute !important;
        width: 1px; height: 1px;
        padding: 0; margin: -1px;
        overflow: hidden;
        clip: rect(0 0 0 0);
        clip-path: inset(50%);
        white-space: nowrap;
        border: 0;
    }
    .searchable-select .ss-input { width: 100%; }
    .searchable-select .ss-menu {
        position: absolute;
        z-index: 2000;                 /* float above the summary/totals blocks below the field */
        left: 0; right: 0;
        top: calc(100% + 4px);
        max-height: 15rem;
        overflow-y: auto;
        padding: 0.25rem;
        background: var(--color-base-100, #ffffff);
        color: var(--color-base-content, #1f2937);
        border: 1px solid rgba(100, 116, 139, 0.28);
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18);
        -webkit-overflow-scrolling: touch;
    }
    .searchable-select .ss-menu.hidden { display: none; }
    .searchable-select .ss-option {
        padding: 0.35rem 0.6rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        cursor: pointer;
        white-space: normal;
        color: inherit;
    }
    .searchable-select .ss-option:hover,
    .searchable-select .ss-option.is-active { background: rgba(100, 116, 139, 0.18); }
    .searchable-select .ss-option[aria-selected="true"] { font-weight: 600; }
    .searchable-select .ss-empty { padding: 0.5rem 0.6rem; font-size: 0.8rem; opacity: 0.6; }
    .searchable-select .ss-input::placeholder { opacity: .5; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    function initSearchable(root) {
        if (!root || root.dataset.ssReady === '1') return;
        const select = root.querySelector('.ss-native');
        const input  = root.querySelector('.ss-input');
        const menu   = root.querySelector('.ss-menu');
        if (!select || !input || !menu) return;
        root.dataset.ssReady = '1';

        let options = [];
        let activeIndex = -1;
        let open = false;

        const norm = (s) => (s || '').toString().toLowerCase().trim();
        const clean = (s) => (s || '').toString().replace(/\s+/g, ' ').trim();

        function readOptions() {
            options = Array.from(select.options).map(function (o) {
                return { value: o.value, label: clean(o.textContent), offset: o.dataset.offset || '' };
            });
        }
        function syncInput() {
            const sel = select.options[select.selectedIndex];
            if (sel && sel.value !== '') {
                input.value = clean(sel.textContent);
                input.dataset.hasValue = '1';
            } else {
                input.value = '';
                delete input.dataset.hasValue;
            }
        }
        function render(filter) {
            readOptions();
            const f = norm(filter);
            const matches = options.filter(function (o) {
                return o.value !== '' && (f === '' || norm(o.label).indexOf(f) !== -1);
            });
            menu.innerHTML = '';
            activeIndex = -1;
            if (matches.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'ss-empty';
                empty.textContent = 'No matching account';
                menu.appendChild(empty);
                return;
            }
            const current = select.value;
            matches.forEach(function (o) {
                const row = document.createElement('div');
                row.className = 'ss-option';
                row.setAttribute('role', 'option');
                row.dataset.value = o.value;
                row.textContent = o.label;
                if (o.value === current) row.setAttribute('aria-selected', 'true');
                row.addEventListener('mousedown', function (e) { e.preventDefault(); choose(o.value); });
                menu.appendChild(row);
            });
        }
        function moveActive(delta) {
            const rows = Array.from(menu.querySelectorAll('.ss-option'));
            if (!rows.length) return;
            rows.forEach(function (r) { r.classList.remove('is-active'); });
            activeIndex = Math.max(0, Math.min(rows.length - 1, activeIndex + delta));
            const el = rows[activeIndex];
            el.classList.add('is-active');
            el.scrollIntoView({ block: 'nearest' });
        }
        function show() {
            if (open) return;
            open = true;
            menu.classList.remove('hidden');
            menu.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }
        function hide() {
            if (!open) return;
            open = false;
            menu.classList.add('hidden');
            menu.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            syncInput();
        }
        function choose(value) {
            select.value = value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            syncInput();
            hide();
            input.blur();
        }

        input.addEventListener('focus', function () { render(''); show(); });
        input.addEventListener('click', function () { if (!open) { render(input.dataset.hasValue ? '' : input.value); show(); } });
        input.addEventListener('input', function () { delete input.dataset.hasValue; render(input.value); show(); });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); if (!open) { render(''); show(); } moveActive(1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); if (!open) { render(''); show(); } moveActive(-1); }
            else if (e.key === 'Enter') {
                const rows = Array.from(menu.querySelectorAll('.ss-option'));
                if (open && rows.length) { e.preventDefault(); choose((rows[activeIndex] || rows[0]).dataset.value); }
            } else if (e.key === 'Escape') { hide(); }
        });
        input.addEventListener('blur', function () { setTimeout(hide, 150); });
        // Forward native (required) validation focus to the visible search box.
        select.addEventListener('focus', function () { input.focus(); show(); });

        // The Charge/Applicant cascade (updateAccountType) rebuilds this select's options;
        // re-render the menu + refresh the displayed value whenever that happens.
        new MutationObserver(function () {
            if (open) render(input.dataset.hasValue ? '' : input.value);
            syncInput();
        }).observe(select, { childList: true });

        syncInput();
    }

    window.ToybitsInitSearchable = initSearchable;

    function boot() {
        document.querySelectorAll('.searchable-select').forEach(initSearchable);
        const linesBox = document.getElementById('lines');
        if (linesBox) {
            new MutationObserver(function (muts) {
                muts.forEach(function (m) {
                    m.addedNodes.forEach(function (n) {
                        if (n.nodeType !== 1) return;
                        if (n.classList && n.classList.contains('searchable-select')) initSearchable(n);
                        if (n.querySelectorAll) n.querySelectorAll('.searchable-select').forEach(initSearchable);
                    });
                });
            }).observe(linesBox, { childList: true });
        }
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
</script>
@endpush
@endsection
