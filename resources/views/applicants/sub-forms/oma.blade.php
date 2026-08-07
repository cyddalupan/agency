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
<fieldset class="fieldset">
    <legend class="fieldset-legend">OMA Released</legend>
    <input type="date" name="released_date" value="{{ old('released_date', $record->released_date ?? '') }}"
           class="input w-full">
</fieldset>
