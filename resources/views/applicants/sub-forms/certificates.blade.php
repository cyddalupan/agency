<fieldset class="fieldset">
    <legend class="fieldset-legend">Type <span class="text-error">*</span></legend>
    <select name="type" class="select w-full">
        <option value="">-- Select --</option>
        <option value="tesda" @selected(old('type', $record->type ?? '') === 'tesda')>TESDA</option>
        <option value="medical" @selected(old('type', $record->type ?? '') === 'medical')>Medical</option>
        <option value="insurance" @selected(old('type', $record->type ?? '') === 'insurance')>Insurance</option>
        <option value="pdos" @selected(old('type', $record->type ?? '') === 'pdos')>PDOS</option>
        <option value="nbi" @selected(old('type', $record->type ?? '') === 'nbi')>NBI Clearance</option>
        <option value="other" @selected(old('type', $record->type ?? '') === 'other')>Other</option>
    </select>
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Certificate No.</legend>
    <input type="text" name="certificate_no" value="{{ old('certificate_no', $record->certificate_no ?? '') }}"
           class="input w-full">
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
<fieldset class="fieldset">
    <legend class="fieldset-legend">Remarks</legend>
    <input type="text" name="remarks" value="{{ old('remarks', $record->remarks ?? '') }}"
           class="input w-full" placeholder="Optional notes">
</fieldset>
