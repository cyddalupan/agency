<fieldset class="fieldset">
    <legend class="fieldset-legend">Name <span class="text-error">*</span></legend>
    <input type="text" name="name" value="{{ old('name', $record->name ?? '') }}"
           class="input w-full" placeholder="Full name">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Contact</legend>
    <input type="text" name="contact" value="{{ old('contact', $record->contact ?? '') }}"
           class="input w-full" placeholder="Phone number">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Relation</legend>
    <input type="text" name="relation" value="{{ old('relation', $record->relation ?? '') }}"
           class="input w-full" placeholder="e.g. Former Employer">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Position</legend>
    <input type="text" name="position" value="{{ old('position', $record->position ?? '') }}"
           class="input w-full" placeholder="e.g. Manager">
</fieldset>
