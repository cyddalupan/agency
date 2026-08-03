<fieldset class="fieldset">
    <legend class="fieldset-legend">Name <span class="text-error">*</span></legend>
    <input type="text" name="name" value="{{ old('name', $record->name ?? '') }}"
           class="input w-full" placeholder="Contact person full name">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Relationship</legend>
    <input type="text" name="relationship" value="{{ old('relationship', $record->relationship ?? '') }}"
           class="input w-full" placeholder="e.g. Mother, Spouse, Friend">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Contact #</legend>
    <input type="text" name="contact" value="{{ old('contact', $record->contact ?? '') }}"
           class="input w-full" placeholder="Phone number">
</fieldset>
