<fieldset class="fieldset">
    <legend class="fieldset-legend">RFP</legend>
    <input type="text" name="rfp" value="{{ old('rfp', $record->rfp ?? '') }}"
           class="input w-full" placeholder="e.g. RFP-001">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Sponsor</legend>
    <input type="text" name="sponsor" value="{{ old('sponsor', $record->sponsor ?? '') }}"
           class="input w-full" placeholder="Sponsor name">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Sponsor ID#</legend>
    <input type="text" name="sponsor_id" value="{{ old('sponsor_id', $record->sponsor_id ?? '') }}"
           class="input w-full" placeholder="e.g. SP-9">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Contact#</legend>
    <input type="text" name="contact" value="{{ old('contact', $record->contact ?? '') }}"
           class="input w-full" placeholder="Contact number">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Address</legend>
    <input type="text" name="address" value="{{ old('address', $record->address ?? '') }}"
           class="input w-full" placeholder="Sponsor address">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Contract Received</legend>
    <input type="date" name="contract_received" value="{{ old('contract_received', $record->contract_received ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Contract Signed</legend>
    <input type="date" name="contract_signed" value="{{ old('contract_signed', $record->contract_signed ?? '') }}"
           class="input w-full">
</fieldset>
