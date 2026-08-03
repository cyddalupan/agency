<fieldset class="fieldset">
    <legend class="fieldset-legend">Partner's Name <span class="text-error">*</span></legend>
    <input type="text" name="partner_name" value="{{ old('partner_name', $record->partner_name ?? '') }}"
           class="input w-full" placeholder="Spouse / partner full name">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Number of Children</legend>
    <input type="number" name="number_of_children" min="0" value="{{ old('number_of_children', $record->number_of_children ?? '') }}"
           class="input w-full" placeholder="e.g. 2">
</fieldset>
