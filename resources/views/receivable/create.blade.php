@extends('layouts.app')

@section('title', 'New Receivable')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <h1 class="text-2xl font-bold">➕ New Receivable</h1>
            <p class="opacity-80 mt-1">Tab 1 — Save Transaction</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error shadow-md mb-6">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Note: agent -> applicant relationship (Cyd request) --}}
    <div class="bg-info/10 border border-info/40 text-info-content rounded-lg p-4 mb-5 text-sm">
        <p class="font-semibold">ℹ️ Applicant belongs to the selected Agent</p>
        <ul class="list-disc list-inside mt-2 opacity-90">
            <li>Choose an <strong>Agent</strong> first — the <strong>Applicant</strong> dropdown below then lists only the applicants assigned to that Agent.</li>
            <li>If no applicant appears for the chosen Agent, that Agent has no applicants linked yet. An applicant can only be attached once it is assigned to the Agent.</li>
            <li>Change the Agent to reload the matching applicant list.</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('receivable.store') }}" class="card bg-base-100 shadow-md">
        @csrf

        <div class="card-body">
            {{-- Row: Code / Date / Ref# --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Code</span></label>
                    <input type="text" class="input input-bordered font-mono" value="{{ $code }}" disabled>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Date *</span></label>
                    <input type="date" name="date" class="input input-bordered" value="{{ old('date', now()->toDateString()) }}" required>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Ref# / AR#</span></label>
                    <input type="text" name="ref_ar" class="input input-bordered" value="{{ old('ref_ar') }}" placeholder="AR-1001">
                </div>
            </div>

            {{-- Agent / Applicant --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Agent * (all branches)</span></label>
                    <select name="agent_id" id="agent_id" class="select select-bordered" required>
                        <option value="">Select agent…</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" data-branch="{{ $a->branch->name ?? '' }}" {{ old('agent_id') == $a->id ? 'selected' : '' }}>
                                {{ $a->name }}@if($a->branch) ({{ $a->branch->name }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Applicant (from agent)</span></label>
                    <select name="applicant_id" id="applicant_id" class="select select-bordered">
                        <option value="">Select applicant…</option>
                        @foreach($applicants as $ap)
                            <option value="{{ $ap->id }}" data-agent="{{ $ap->agent_id }}" {{ old('applicant_id') == $ap->id ? 'selected' : '' }}>
                                {{ $ap->last_name }}, {{ $ap->first_name }}
                            </option>
                        @endforeach
                    </select>
                    <p id="applicant_empty_note" class="hidden mt-1 text-xs text-warning font-medium">
                        No applicants are assigned to this selected Agent yet.
                    </p>
                </div>
            </div>

            {{-- Amount / Account --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Amount *</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" step="0.01" min="0.01" name="amount" class="input input-bordered flex-1" value="{{ old('amount') }}" required>
                    </div>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Account</span></label>
                    <select name="account" class="select select-bordered">
                        <option value="">Select account…</option>
                        @foreach($accounts as $ac)
                            <option value="{{ $ac }}" {{ old('account') == $ac ? 'selected' : '' }}>{{ $ac }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Payment Information --}}
            <h3 class="font-bold text-sm opacity-70 mt-2 mb-2">Payment Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Deposit / Debit Account</span></label>
                    <select name="debit_account" class="select select-bordered">
                        <option value="">Select…</option>
                        @foreach($debitAccounts as $d)
                            <option value="{{ $d }}" {{ old('debit_account') == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Type</span></label>
                    <select name="type" class="select select-bordered">
                        <option value="">Select…</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ old('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Mode of Payment</span></label>
                    <select name="mode" class="select select-bordered">
                        <option value="">Select…</option>
                        @foreach($modes as $m)
                            <option value="{{ $m }}" {{ old('mode') == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Particular</span></label>
                <textarea name="particular" class="textarea textarea-bordered" rows="2" placeholder="Details…">{{ old('particular') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">💾 Save Transaction</button>
                <a href="{{ route('receivable.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const agentSel = document.getElementById('agent_id');
            const appSel = document.getElementById('applicant_id');
            const emptyNote = document.getElementById('applicant_empty_note');

            // Filter applicant options to the selected agent (agent->applicant cascade)
            function filterApplicants() {
                const agentId = agentSel.value;
                let visible = 0;
                Array.from(appSel.options).forEach(function (opt) {
                    if (!opt.value) return; // keep the placeholder
                    // Only show applicants belonging to the selected agent.
                    // Unassigned applicants (no agent) appear ONLY when no agent is chosen.
                    const shown = agentId === '' || opt.dataset.agent === agentId;
                    opt.hidden = !shown;
                    if (shown) visible++;
                });
                // clear a now-hidden selection (e.g. applicant belonged to a previous agent)
                const sel = appSel.options[appSel.selectedIndex];
                if (sel && sel.selected && sel.hidden) {
                    appSel.value = '';
                }
                // Show empty-state note only when an agent is chosen but has no applicants
                if (agentId && visible === 0) {
                    emptyNote.classList.remove('hidden');
                } else {
                    emptyNote.classList.add('hidden');
                }
            }
            agentSel.addEventListener('change', filterApplicants);
            filterApplicants();
        });
    </script>

</div>
@endsection
