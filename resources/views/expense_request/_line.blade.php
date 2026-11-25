@php
    $idx = (int) $index;
    // Toybits 2026-11-04: the Payments field is only relevant when the transaction is
    // charged to an Agent account of type Partial / Full / Fit to Work / Contract / Deployed.
    // Everything else (Office charges, Agent Return / Cash advance, no account yet) hides it.
    $paymentAccountNames = ['partial', 'full', 'fit to work', 'contract', 'deployed'];
    $paymentSubName = '';
    if (!empty($line['sub_account_id'])) {
        $paymentAccount = collect($allAccounts ?? [])->firstWhere('id', $line['sub_account_id']);
        // Names are rendered as "MAIN → Sub"; only the sub part decides eligibility.
        $paymentSubName = $paymentAccount
            ? mb_strtolower(trim(last(explode('→', $paymentAccount->name))))
            : '';
    }
    $showPayment = (($line['charge'] ?? 'office') === 'agent')
        && in_array($paymentSubName, $paymentAccountNames, true);
@endphp
<div class="card bg-base-200/50 border border-base-300 expense-line" data-index="{{ $index }}">
    <div class="card-body p-4">
        @if(!empty($line['id']))
            <input type="hidden" name="lines[{{ $index }}][id]" value="{{ $line['id'] }}">
        @endif
        <div class="flex items-center justify-between mb-2">
            <h4 class="font-bold text-sm">Line #{{ $idx + 1 }}</h4>
            <button type="button" class="btn btn-ghost btn-xs remove-line">✕ Remove</button>
        </div>

        @if($showLineBranch ?? false)
            {{-- Branch per line (CYD 2026-11-04): Main Office / Main Branch users file
                 expenses for several branches in one request, so each line carries
                 its own branch. Branch-locked users never see this field. --}}
            <div class="form-control mb-3">
                <label class="label"><span class="label-text">Branch</span></label>
                @php
                    $lineBranch = old("lines.$index.branch_id", $line['branch_id'] ?? ($record->branch_id ?? ''));
                @endphp
                <select name="lines[{{ $index }}][branch_id]" class="select select-bordered select-sm line-branch">
                    <option value="">- select -</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ (string) $lineBranch === (string) $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            {{-- Charge --}}
            <div class="form-control">
                <label class="label"><span class="label-text">Charge *</span></label>
                <select name="lines[{{ $index }}][charge]" class="select select-bordered select-sm" required>
                    <option value="office" {{ ($line['charge'] ?? '') === 'office' ? 'selected' : '' }}>Office</option>
                    <option value="agent" {{ ($line['charge'] ?? '') === 'agent' ? 'selected' : '' }}>{{ agent_term() }}</option>
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
            {{-- Toybits 2026-11-04: shown only for Charge = Agent + Partial/Full/Fit to Work/Contract/Deployed --}}
            <div class="form-control payment-field" @if(!$showPayment) style="display:none" @endif>
                <label class="label"><span class="label-text">Payments</span></label>
                <input type="number" step="0.01" min="0" name="lines[{{ $index }}][payment]"
                       class="input input-bordered input-sm payment-input"
                       value="{{ $line['payment'] ?? '' }}" placeholder="0.00"
                       data-account-name="{{ $paymentSubName }}">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            {{-- Agent Name (shown for both Charge types) --}}
            <div class="form-control">
                <label class="label"><span class="label-text">{{ agent_term() }} Name</span></label>
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
                            {{-- Card 6af82fef: full name (First Middle Last Suffix). --}}
                            {{ $ap->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Account Type (sub account): gated by Charge; main is auto-derived from charge --}}
        {{-- Toybits 2026-09-23: wrapped in a searchable combobox (the flat account list is long). --}}
        <div class="form-control mt-1">
            <label class="label"><span class="label-text">Account Type *</span></label>
            <div class="searchable-select relative" data-ss data-search-placeholder="Search account type…">
                <input type="text" class="input input-bordered input-sm ss-input w-full"
                       placeholder="Search account type…" autocomplete="off" autocapitalize="off" spellcheck="false"
                       role="combobox" aria-expanded="false" aria-autocomplete="list">
                <div class="ss-menu hidden" role="listbox" hidden></div>
                {{-- Card 6b05a95b: no bare `required` on a visually hidden (1px) control.
                     Account Type is enforced by the form's submit check, which names
                     the line in a visible message. --}}
                <select name="lines[{{ $index }}][sub_account_id]" class="ss-native select select-bordered select-sm">
                    <option value="">- select -</option>
                    @php $storedListed = false; @endphp
                    @foreach($allAccounts as $acct)
                        @php if (($line['sub_account_id'] ?? '') == $acct->id) { $storedListed = true; } @endphp
                        <option value="{{ $acct->id }}" data-offset="{{ $acct->charge_type }}" {{ ($line['sub_account_id'] ?? '') == $acct->id ? 'selected' : '' }}>
                            {{ $acct->name }}
                        </option>
                    @endforeach
                    {{-- Card 6b05a95b: an existing row may be booked to a top-level
                         account (e.g. 40822 -> APPLICANT), which the option list omits.
                         Show it so the saved value is visible and resubmitted, not blanked. --}}
                    @if(!empty($line['sub_account_id']) && ! $storedListed && !empty($line['account_name']))
                        <option value="{{ $line['sub_account_id'] }}" data-offset="{{ $line['account_charge_type'] ?? '' }}" selected>
                            {{ $line['account_name'] }}
                        </option>
                    @endif
                </select>
            </div>
            {{-- Card 6b05a95b: visible home for the "Line #N: choose an Account Type" message. --}}
            <p class="line-error text-error text-xs mt-1 hidden"></p>
        </div>

        {{-- Upload --}}
        <div class="form-control mt-1">
            <label class="label"><span class="label-text">Attachment</span></label>
            <input type="file" name="lines[{{ $index }}][file]" class="file-input file-input-bordered file-input-sm">
            @if(!empty($line['has_file']))
                <p class="text-xs opacity-60 mt-1">A file is already attached — upload a new one to replace it.</p>
            @endif
        </div>
    </div>
</div>
