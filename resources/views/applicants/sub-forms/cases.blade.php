{{-- Cases sub-form — used for Add (record null) and inline Edit on the applicant profile Cases tab. --}}
<fieldset class="fieldset">
    <legend class="fieldset-legend">Case Number</legend>
    <input type="text" name="case_number" value="{{ old('case_number', $record->case_number ?? '') }}"
           class="input w-full" placeholder="e.g. C-2026-001">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Case Title</legend>
    <input type="text" name="title" value="{{ old('title', $record->title ?? '') }}"
           class="input w-full" placeholder="Case title (required)">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Date Received</legend>
    <input type="date" name="date_received"
           value="{{ old('date_received', $record?->date_received ? $record->date_received->format('Y-m-d') : '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Date Hearing</legend>
    <input type="date" name="date_hearing"
           value="{{ old('date_hearing', $record?->date_hearing ? $record->date_hearing->format('Y-m-d') : '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">FRA/Employer</legend>
    <select name="employer_id" class="select w-full">
        <option value="">— None —</option>
        @foreach ($employers ?? [] as $emp)
            <option value="{{ $emp->id }}" @selected(old('employer_id', $record->employer_id ?? '') == $emp->id)>{{ $emp->name }}</option>
        @endforeach
    </select>
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Court</legend>
    <input type="text" name="court" value="{{ old('court', $record->court ?? '') }}"
           class="input w-full" placeholder="e.g. NLRC Manila">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Status</legend>
    <select name="status" class="select w-full">
        <option value="open" @selected(old('status', $record->status ?? 'open') === 'open')>Open</option>
        <option value="closed" @selected(old('status', $record->status ?? 'open') === 'closed')>Closed</option>
    </select>
</fieldset>
<fieldset class="fieldset md:col-span-2">
    <legend class="fieldset-legend">Message</legend>
    <textarea name="description" rows="3" class="textarea w-full"
              placeholder="Notes / message about this case">{{ old('description', $record->description ?? '') }}</textarea>
</fieldset>
