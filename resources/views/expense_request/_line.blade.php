@php $idx = (int) $index; @endphp
<div class="card bg-base-200/50 border border-base-300 expense-line" data-index="{{ $index }}">
    <div class="card-body p-4">
        <div class="flex items-center justify-between mb-2">
            <h4 class="font-bold text-sm">Line #{{ $idx + 1 }}</h4>
            <button type="button" class="btn btn-ghost btn-xs remove-line">✕ Remove</button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            {{-- Charge --}}
            <div class="form-control">
                <label class="label"><span class="label-text">Charge *</span></label>
                <select name="lines[{{ $index }}][charge]" class="select select-bordered select-sm">
                    <option value="office" {{ ($line['charge'] ?? '') === 'office' ? 'selected' : '' }}>Office</option>
                    <option value="agent" {{ ($line['charge'] ?? '') === 'agent' ? 'selected' : '' }}>Agent</option>
                </select>
            </div>

            {{-- Currency --}}
            <div class="form-control">
                <label class="label"><span class="label-text">Currency *</span></label>
                <select name="lines[{{ $index }}][currency]" class="select select-bordered select-sm">
                    <option value="PHP" {{ ($line['currency'] ?? 'PHP') === 'PHP' ? 'selected' : '' }}>PHP</option>
                    <option value="USD" {{ ($line['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD</option>
                </select>
            </div>

            {{-- Amount --}}
            <div class="form-control">
                <label class="label"><span class="label-text">Amount *</span></label>
                <input type="number" step="0.01" min="0.01" name="lines[{{ $index }}][amount]"
                       class="input input-bordered input-sm" value="{{ $line['amount'] ?? '' }}" required>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            {{-- Main Account (single picker — sub-account field removed) --}}
            <div class="form-control">
                <label class="label"><span class="label-text">Main Account *</span></label>
                <select name="lines[{{ $index }}][main_account_id]" data-main-group="1" class="select select-bordered select-sm">
                    <option value="">— Select Main —</option>
                    @foreach($mains as $m)
                        <option value="{{ $m->id }}" data-offset="{{ $m->charge_type ?? 'office' }}"
                                {{ ($line['main_account_id'] ?? '') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Country --}}
            <div class="form-control">
                <label class="label"><span class="label-text">Country</span></label>
                <select name="lines[{{ $index }}][country_id]" class="select select-bordered select-sm">
                    <option value="">—</option>
                    @foreach($countries as $c)
                        <option value="{{ $c->id }}" {{ ($line['country_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Particular --}}
            <div class="form-control">
                <label class="label"><span class="label-text">Particular</span></label>
                <input type="text" name="lines[{{ $index }}][particular]" class="input input-bordered input-sm"
                       value="{{ $line['particular'] ?? '' }}" placeholder="Description">
            </div>
        </div>

        {{-- Agent (only for charge=agent) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" data-agent-row="{{ $index }}">
            <div class="form-control">
                <label class="label"><span class="label-text">Agent Name</span></label>
                <select name="lines[{{ $index }}][agent_id]" class="select select-bordered select-sm">
                    <option value="">—</option>
                    @foreach($agents as $a)
                        <option value="{{ $a->id }}" {{ ($line['agent_id'] ?? '') == $a->id ? 'selected' : '' }}>
                            {{ $a->name }}{{ $a->branch ? ' (' . $a->branch->name . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-control">
                <label class="label"><span class="label-text">Applicant (under agent)</span></label>
                <select name="lines[{{ $index }}][applicant_id]" class="select select-bordered select-sm">
                    <option value="">—</option>
                    @foreach($applicants as $ap)
                        <option value="{{ $ap->id }}" data-agent="{{ $ap->agent_id }}" {{ ($line['applicant_id'] ?? '') == $ap->id ? 'selected' : '' }}>
                            {{ $ap->last_name }}, {{ $ap->first_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Upload --}}
        <div class="form-control mt-1">
            <label class="label"><span class="label-text">Attachment</span></label>
            <input type="file" name="lines[{{ $index }}][file]" class="file-input file-input-bordered file-input-sm">
        </div>
    </div>
</div>
