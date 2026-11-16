@php
    /**
     * Shared form fields for the risk create and edit screens.
     *
     * $risk is null when creating. The likelihood/impact pickers are explicit
     * number selects (not free text), because the two axes drive the whole
     * register: a typo there silently rebands a risk.
     */
    $current = fn (string $field, $fallback = null) => old($field, $risk->{$field} ?? $fallback);
    $level = $risk ? $risk->risk_level : null;
    $score = $risk ? $risk->risk_score : null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="form-control">
        <label class="label py-1" for="ref_no"><span class="label-text text-xs font-semibold">Reference</span></label>
        <input type="text" name="ref_no" id="ref_no" value="{{ $current('ref_no', $nextRef ?? '') }}"
               class="input input-bordered input-sm font-mono w-full @error('ref_no') input-error @enderror" required>
        @error('ref_no')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label py-1" for="process_area"><span class="label-text text-xs font-semibold">Process area</span></label>
        <input type="text" name="process_area" id="process_area" value="{{ $current('process_area') }}"
               placeholder="e.g. Payroll, Applicant vetting"
               class="input input-bordered input-sm w-full">
    </div>

    <div class="form-control md:col-span-2">
        <label class="label py-1" for="title"><span class="label-text text-xs font-semibold">Risk title</span></label>
        <input type="text" name="title" id="title" value="{{ $current('title') }}"
               class="input input-bordered input-sm w-full @error('title') input-error @enderror" required>
        @error('title')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label py-1" for="description"><span class="label-text text-xs font-semibold">Description</span></label>
        <textarea name="description" id="description" rows="3" class="textarea textarea-bordered textarea-sm w-full">{{ $current('description') }}</textarea>
    </div>

    <div class="form-control">
        <label class="label py-1" for="cause"><span class="label-text text-xs font-semibold">Cause</span></label>
        <textarea name="cause" id="cause" rows="3" class="textarea textarea-bordered textarea-sm w-full">{{ $current('cause') }}</textarea>
    </div>

    <div class="form-control md:col-span-2">
        <label class="label py-1" for="consequence"><span class="label-text text-xs font-semibold">Consequence</span></label>
        <textarea name="consequence" id="consequence" rows="2" class="textarea textarea-bordered textarea-sm w-full">{{ $current('consequence') }}</textarea>
    </div>
</div>

{{-- Likelihood / impact picker --}}
<div class="mt-4 p-4 rounded-lg bg-base-200">
    <div class="flex flex-wrap items-end gap-4">
        <div class="form-control">
            <label class="label py-1" for="likelihood"><span class="label-text text-xs font-semibold">Likelihood (1-5)</span></label>
            <select name="likelihood" id="likelihood" class="select select-bordered select-sm @error('likelihood') select-error @enderror" required>
                @foreach ($scale as $n)
                    <option value="{{ $n }}" @selected((int) $current('likelihood', 3) === $n)>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-control">
            <label class="label py-1" for="impact"><span class="label-text text-xs font-semibold">Impact (1-5)</span></label>
            <select name="impact" id="impact" class="select select-bordered select-sm @error('impact') select-error @enderror" required>
                @foreach ($scale as $n)
                    <option value="{{ $n }}" @selected((int) $current('impact', 3) === $n)>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs opacity-60">Score</span>
            <span class="font-mono font-bold text-xl" id="score-preview">{{ $score ?? 9 }}</span>
            <span class="badge badge-md @if($level) {{ $risk->level_badge }} @endif" id="level-preview">{{ $level ? $risk->level_label : 'Medium' }}</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
    <div class="form-control">
        <label class="label py-1" for="existing_controls"><span class="label-text text-xs font-semibold">Existing controls</span></label>
        <textarea name="existing_controls" id="existing_controls" rows="3" class="textarea textarea-bordered textarea-sm w-full">{{ $current('existing_controls') }}</textarea>
    </div>

    <div class="form-control">
        <label class="label py-1" for="treatment_action"><span class="label-text text-xs font-semibold">Treatment action</span></label>
        <textarea name="treatment_action" id="treatment_action" rows="3" class="textarea textarea-bordered textarea-sm w-full">{{ $current('treatment_action') }}</textarea>
    </div>

    <div class="form-control">
        <label class="label py-1" for="risk_owner_id"><span class="label-text text-xs font-semibold">Risk owner</span></label>
        <select name="risk_owner_id" id="risk_owner_id" class="select select-bordered select-sm">
            <option value="">— none —</option>
            @foreach ($owners as $id => $label)
                <option value="{{ $id }}" @selected((int) $current('risk_owner_id') === $id)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-control">
        <label class="label py-1" for="status"><span class="label-text text-xs font-semibold">Status</span></label>
        <select name="status" id="status" class="select select-bordered select-sm">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($current('status', 'open') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-control">
        <label class="label py-1" for="target_date"><span class="label-text text-xs font-semibold">Target date</span></label>
        <input type="date" name="target_date" id="target_date"
               value="{{ optional($risk->target_date ?? null)->format('Y-m-d') ?? old('target_date') }}"
               class="input input-bordered input-sm w-full">
    </div>

    <div class="form-control">
        <label class="label py-1" for="review_date"><span class="label-text text-xs font-semibold">Review date</span></label>
        <input type="date" name="review_date" id="review_date"
               value="{{ optional($risk->review_date ?? null)->format('Y-m-d') ?? old('review_date') }}"
               class="input input-bordered input-sm w-full">
    </div>
</div>

<script>
(function () {
    // Mirror the model's banding so the picker previews the same level the
    // server will store. Kept in sync with Risk::LEVEL_BANDS.
    var bands = [['critical', 20], ['high', 12], ['medium', 5], ['low', 0]];
    var badges = {
        low: 'badge-success',
        medium: 'badge-warning',
        high: 'badge-error',
        critical: 'badge-error badge-outline'
    };
    var labels = { low: 'Low', medium: 'Medium', high: 'High', critical: 'Critical' };

    function levelFor(score) {
        for (var i = 0; i < bands.length; i++) {
            if (score >= bands[i][1]) return bands[i][0];
        }
        return 'low';
    }

    function refresh() {
        var l = document.getElementById('likelihood');
        var i = document.getElementById('impact');
        if (!l || !i) return;
        var score = parseInt(l.value, 10) * parseInt(i.value, 10);
        var level = levelFor(score);
        var s = document.getElementById('score-preview');
        var p = document.getElementById('level-preview');
        if (s) s.textContent = score;
        if (p) {
            p.textContent = labels[level];
            p.className = 'badge badge-md ' + badges[level];
            p.setAttribute('data-level', level);
        }
    }

    var l = document.getElementById('likelihood');
    var i = document.getElementById('impact');
    if (l) l.addEventListener('change', refresh);
    if (i) i.addEventListener('change', refresh);
})();
</script>
