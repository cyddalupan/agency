<fieldset class="fieldset">
    <legend class="fieldset-legend">Clinic Name</legend>
    <input type="text" name="clinic_name" value="{{ old('clinic_name', $record->clinic_name ?? '') }}"
           class="input w-full" placeholder="e.g. ABC Medical Clinic">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Issue Date</legend>
    <input type="date" name="issue_date" value="{{ old('issue_date', optional($record)->issue_date?->format('Y-m-d')) }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Expire Date</legend>
    <input type="date" name="expiry_date" value="{{ old('expiry_date', optional($record)->expiry_date?->format('Y-m-d')) }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Remarks</legend>
    <input type="text" name="remarks" value="{{ old('remarks', $record->remarks ?? '') }}"
           class="input w-full" placeholder="Optional notes">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Upload Medical {{ isset($record) && $record->file_path ? '(replace)' : '' }}</legend>
    <input type="file" name="file" class="file-input file-input-bordered w-full" accept="image/*,.pdf">
    <label class="fieldset-label">JPG, PNG, WebP, GIF, or PDF (max 2MB)</label>
</fieldset>
