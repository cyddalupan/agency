@extends('layouts.app')

@section('title', ($isEdit ?? false) ? 'Edit Receivable ' . $record->code : 'New Receivable')

@section('content')
<div class="w-full">

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <h1 class="text-2xl font-bold">{{ ($isEdit ?? false) ? '✏️ Edit Receivable ' . $record->code : '➕ New Receivable' }}</h1>
            <p class="opacity-80 mt-1">{{ ($isEdit ?? false) ? 'Tab 1 — Edit Transaction' : 'Tab 1 — Save Transaction' }}</p>
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

    <form method="POST" action="{{ ($isEdit ?? false) ? route('receivable.update', $record) : route('receivable.store') }}" class="card bg-base-100 shadow-md" enctype="multipart/form-data">
        @csrf
        @if($isEdit ?? false)
            @method('PUT')
        @endif

        <div class="card-body space-y-6">
            {{-- Row: Code / Date / Ref# --}}
            <div>
                <h3 class="font-bold text-sm opacity-70 uppercase tracking-wider mb-3 pb-2 border-b border-base-200">📋 Transaction Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Code</span></label>
                        <input type="text" class="input input-bordered w-full font-mono" value="{{ $code }}" disabled>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Date *</span></label>
                        <input type="date" name="date" class="input input-bordered w-full" value="{{ old('date', isset($record) ? $record->date->toDateString() : now()->toDateString()) }}" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Ref# / AR#</span></label>
                        <input type="text" name="ref_ar" class="input input-bordered w-full" value="{{ old('ref_ar', $record->ref_ar ?? '') }}" placeholder="AR-1001">
                    </div>
                </div>
            </div>

            {{-- Agent / Applicant --}}
            <div>
                <h3 class="font-bold text-sm opacity-70 uppercase tracking-wider mb-3 pb-2 border-b border-base-200">👤 Agent & Applicant</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Agent *{{ auth()->user()->isBranchLocked() ? ' (your branch)' : ' (all branches)' }}</span></label>
                        <select name="agent_id" id="agent_id" class="select select-bordered w-full"
                                @if(! ($isEdit ?? false) || ($record->agent_id ?? null)) required @endif>
                            <option value="">Select agent…</option>
                            @foreach($agents as $a)
                                <option value="{{ $a->id }}" data-branch="{{ $a->branch->name ?? '' }}" {{ old('agent_id', $record->agent_id ?? '') == $a->id ? 'selected' : '' }}>
                                    {{ $a->name }}@if($a->branch) ({{ $a->branch->name }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Applicant (from agent)</span></label>
                        <select name="applicant_id" id="applicant_id" class="select select-bordered w-full">
                            <option value="">Select applicant…</option>
                            @foreach($applicants as $ap)
                                <option value="{{ $ap->id }}" data-agent="{{ $ap->agent_id }}" {{ old('applicant_id', $record->applicant_id ?? '') == $ap->id ? 'selected' : '' }}>
                                    {{ $ap->last_name }}, {{ $ap->first_name }}
                                </option>
                            @endforeach
                        </select>
                        <p id="applicant_empty_note" class="hidden mt-1 text-xs text-warning font-medium">
                            No applicants are assigned to this selected Agent yet.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Amount / Account --}}
            <div>
                <h3 class="font-bold text-sm opacity-70 uppercase tracking-wider mb-3 pb-2 border-b border-base-200">💰 Amount & Account</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Amount *</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm opacity-60 font-semibold">₱</span>
                            <input type="number" step="0.01" min="0.01" name="amount" class="input input-bordered w-full pl-8" value="{{ old('amount', $record->amount ?? '') }}" required>
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Account</span></label>
                        <select name="account" class="select select-bordered w-full">
                            <option value="">Select account…</option>
                            @foreach($accounts as $ac)
                                <option value="{{ $ac }}" {{ old('account', $record->account ?? '') == $ac ? 'selected' : '' }}>{{ $ac }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Payment Information --}}
            <div>
                <h3 class="font-bold text-sm opacity-70 uppercase tracking-wider mb-3 pb-2 border-b border-base-200">💳 Payment Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Deposit / Debit Account</span></label>
                        <select name="debit_account" class="select select-bordered w-full">
                            <option value="">Select…</option>
                            @foreach($debitAccounts as $d)
                                <option value="{{ $d }}" {{ old('debit_account', $record->debit_account ?? '') == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Type</span></label>
                        <select name="type" class="select select-bordered w-full">
                            <option value="">Select…</option>
                            @foreach($types as $t)
                                <option value="{{ $t }}" {{ old('type', $record->type ?? '') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Mode of Payment</span></label>
                        <select name="mode" class="select select-bordered w-full">
                            <option value="">Select…</option>
                            @foreach($modes as $m)
                                <option value="{{ $m }}" {{ old('mode', $record->mode ?? '') == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text">Particular</span></label>
                <textarea name="particular" class="textarea textarea-bordered w-full" rows="2" placeholder="Details…">{{ old('particular', $record->particular ?? '') }}</textarea>
            </div>

            {{-- Multi-file attachments (Cyd 2026-11-09, "Upload and Edit") --}}
            @php
                $existingAttachments = isset($record) && ($isEdit ?? false) ? $record->attachments : collect();
                $remainingSlots = 5 - $existingAttachments->count();
            @endphp
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Attachments <span class="opacity-60">(up to 5, PDF/JPG/PNG/DOC/XLS, 5MB each)</span></span>
                </label>

                @if($existingAttachments->isNotEmpty())
                    <ul class="mb-2 space-y-1 text-sm">
                        @foreach($existingAttachments as $attachment)
                            <li>📎 <a href="{{ \App\Support\RequestAttachments::url($attachment->path) }}" target="_blank" class="link link-primary">{{ $attachment->original_name ?? basename($attachment->path) }}</a></li>
                        @endforeach
                    </ul>
                @endif

                @if($remainingSlots > 0)
                    <input type="file" name="attachments[]" multiple class="file-input file-input-bordered file-input-sm w-full">
                    <p class="text-xs opacity-60 mt-1">{{ ($isEdit ?? false) ? 'Uploading here ADDS files to the transaction' : 'Optional' }} — {{ $remainingSlots }} slot(s) left.</p>
                @else
                    <p class="text-xs opacity-70">This transaction already has the maximum of 5 files.</p>
                @endif
                @error('attachments') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                @error('attachments.*') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">{{ ($isEdit ?? false) ? '💾 Save Changes' : '💾 Save Transaction' }}</button>
                @if($isEdit ?? false)
                    <a href="{{ route('receivable.show', $record) }}" class="btn btn-ghost">Cancel</a>
                @else
                    <a href="{{ route('receivable.index') }}" class="btn btn-ghost">Cancel</a>
                @endif
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
