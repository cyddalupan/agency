{{-- Status tab sub-form (PI 6) — renders the fields for route applicants.status --}}
{{--
    Repat checkbox + Repat Date removed 2026-08-10 (Toybits report):
    setting the status to Repatriated (35) in the dropdown already covers it.
    The repat boolean was never read anywhere in the app.
--}}
<fieldset class="fieldset">
    <legend class="fieldset-legend">Applicant# <span class="opacity-60">(optional)</span></legend>
    <input type="text" name="applicant_no" id="pi6_applicant_no"
           value="{{ old('applicant_no', $applicant->applicant_no ?? '') }}"
           placeholder="e.g. LN-2026-0042" class="input w-full">
</fieldset>

<fieldset class="fieldset">
    <legend class="fieldset-legend">FRA/Employer</legend>
    <select name="employer_id" id="pi6_employer_id" class="select w-full">
        <option value="">— None —</option>
        @foreach ($employers ?? [] as $emp)
            <option value="{{ $emp->id }}" @selected(old('employer_id', $applicant->employer_id ?? '') == $emp->id)>{{ $emp->name }}</option>
        @endforeach
    </select>
</fieldset>

<fieldset class="fieldset">
    <legend class="fieldset-legend">Status</legend>
    <select name="status_code" id="pi6_status_code" class="select w-full" required>
        @forelse ($statusCodes ?? [] as $code)
            <option value="{{ $code->code }}" @selected(old('status_code', $applicant->status_code) == $code->code)>
                {{ $code->label }}
            </option>
        @empty
            <option value="0">Pending</option>
        @endforelse
    </select>
</fieldset>

<fieldset class="fieldset">
    <legend class="fieldset-legend">Status Date</legend>
    <input type="date" name="status_date" id="pi6_status_date"
           value="{{ old('status_date', $applicant->status_date?->format('Y-m-d') ?? '') }}"
           class="input w-full">
</fieldset>

<fieldset class="fieldset">
    <legend class="fieldset-legend">Remarks</legend>
    <textarea name="remarks" id="pi6_remarks" rows="2"
              class="textarea w-full"
              placeholder="Notes about this status change">{{ old('remarks', $applicant->remarks ?? '') }}</textarea>
</fieldset>
