<fieldset class="fieldset">
    <legend class="fieldset-legend">Name <span class="text-error">*</span></legend>
    <input type="text" name="name" value="{{ old('name', $record->name ?? '') }}"
           class="input w-full" placeholder="Family member full name">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Relation</legend>
    <input type="text" name="relation" value="{{ old('relation', $record->relation ?? '') }}"
           class="input w-full" placeholder="e.g. Mother, Father, Sibling">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Occupation</legend>
    <input type="text" name="occupation" value="{{ old('occupation', $record->occupation ?? '') }}"
           class="input w-full" placeholder="e.g. Teacher, Farmer">
</fieldset>
