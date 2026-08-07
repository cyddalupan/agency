<fieldset class="fieldset">
    <legend class="fieldset-legend">NBI No.</legend>
    <input type="text" name="nbi_no" value="{{ old('nbi_no', $record->nbi_no ?? '') }}"
           class="input w-full" placeholder="e.g. NBI-2026-12345">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Issue Date</legend>
    <input type="date" name="issue_date" value="{{ old('issue_date', $record->issue_date ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Expiry Date</legend>
    <input type="date" name="expiry_date" value="{{ old('expiry_date', $record->expiry_date ?? '') }}"
           class="input w-full">
</fieldset>
