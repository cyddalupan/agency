<fieldset class="fieldset">
    <legend class="fieldset-legend">Visa No.</legend>
    <input type="text" name="visa_no" value="{{ old('visa_no', $record->visa_no ?? '') }}"
           class="input w-full" placeholder="e.g. VISA-2026-0001">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Visa Type</legend>
    <input type="text" name="visa_type" value="{{ old('visa_type', $record->visa_type ?? '') }}"
           class="input w-full" placeholder="e.g. work">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Visa Received</legend>
    <input type="date" name="received_date" value="{{ old('received_date', $record->received_date ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Visa Stamped</legend>
    <input type="date" name="stamped_date" value="{{ old('stamped_date', $record->stamped_date ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Visa Expiry</legend>
    <input type="date" name="expiry_date" value="{{ old('expiry_date', $record->expiry_date ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Approved Musaned</legend>
    <select name="approved_musaned" class="select w-full">
        <option value="">-- Select --</option>
        <option value="yes" @selected(old('approved_musaned', $record->approved_musaned ?? '') === 'yes')>Yes</option>
        <option value="no" @selected(old('approved_musaned', $record->approved_musaned ?? '') === 'no')>No</option>
    </select>
</fieldset>
