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
                <select name="lines[{{ $index }}][charge]" class="select select-bordered select-sm" required>
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
            {{-- Country --}}
            <div class="form-control">
                <label class="label"><span class="label-text">Country</span></label>
                <select name="lines[{{ $index }}][country_id]" class="select select-bordered select-sm">
                    <option value="">N/A</option>
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

            {{-- Payments (already paid to them); net = amount - payment --}}
            <div class="form-control">
                <label class="label"><span class="label-text">Payments</span></label>
                <input type="number" step="0.01" min="0" name="lines[{{ $index }}][payment]"
                       class="input input-bordered input-sm payment-input"
                       value="{{ $line['payment'] ?? '' }}" placeholder="0.00">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            {{-- Agent Name (shown for both Charge types) --}}
            <div class="form-control">
                <label class="label"><span class="label-text">Agent Name</span></label>
                <select name="lines[{{ $index }}][agent_id]" class="select select-bordered select-sm">
                    <option value="">N/A</option>
                    @foreach($agents as $a)
                        <option value="{{ $a->id }}" {{ ($line['agent_id'] ?? '') == $a->id ? 'selected' : '' }}>
                            {{ $a->name }}{{ $a->branch ? ' (' . $a->branch->name . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Applicant (connected to Agent; cascades by selected Agent) --}}
            <div class="form-control">
                <label class="label"><span class="label-text">Applicant</span></label>
                <select name="lines[{{ $index }}][applicant_id]" class="select select-bordered select-sm">
                    <option value="">N/A</option>
                    @foreach($applicants as $ap)
                        <option value="{{ $ap->id }}" data-agent="{{ $ap->agent_id }}" {{ ($line['applicant_id'] ?? '') == $ap->id ? 'selected' : '' }}>
                            {{ $ap->last_name }}, {{ $ap->first_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Account Type (sub account): gated by Charge; main is auto-derived from charge --}}
        <div class="form-control mt-1">
            <label class="label"><span class="label-text">Account Type *</span></label>
            <select name="lines[{{ $index }}][sub_account_id]" class="select select-bordered select-sm" required>
                <option value="">- select -</option>
                @foreach($allAccounts as $acct)
                    <option value="{{ $acct->id }}" data-offset="{{ $acct->charge_type }}" {{ ($line['sub_account_id'] ?? '') == $acct->id ? 'selected' : '' }}>
                        {{ $acct->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Upload --}}
        <div class="form-control mt-1">
            <label class="label"><span class="label-text">Attachment</span></label>
            <input type="file" name="lines[{{ $index }}][file]" class="file-input file-input-bordered file-input-sm">
        </div>
    </div>
</div>
