<fieldset class="fieldset">
    <legend class="fieldset-legend">Company</legend>
    <input type="text" name="company" value="{{ old('company', $record->company ?? '') }}"
           class="input w-full" placeholder="Company name">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Position</legend>
    <input type="text" name="position" value="{{ old('position', $record->position ?? '') }}"
           class="input w-full" placeholder="Job title">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">From</legend>
    <input type="date" name="from_date" value="{{ old('from_date', $record->from_date ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">To</legend>
    <input type="date" name="to_date" value="{{ old('to_date', $record->to_date ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset fieldset-span-2">
    <legend class="fieldset-legend">Responsibilities</legend>
    <textarea name="responsibilities" class="textarea w-full" rows="2" placeholder="Key responsibilities">{{ old('responsibilities', $record->responsibilities ?? '') }}</textarea>
</fieldset>
