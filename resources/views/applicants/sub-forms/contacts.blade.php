<fieldset class="fieldset">
    <legend class="fieldset-legend">Contact Number</legend>
    <input type="text" name="contact" value="{{ old('contact', $record->contact ?? '') }}"
           class="input w-full" placeholder="e.g. 09171234567">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Type (optional)</legend>
    <input type="text" name="type" value="{{ old('type', $record->type ?? '') }}"
           class="input w-full" placeholder="e.g. Mobile, Landline, Work">
</fieldset>
