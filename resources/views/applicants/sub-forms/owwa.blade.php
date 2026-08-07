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
    <legend class="fieldset-legend">OWWA Released</legend>
    <input type="date" name="released_date" value="{{ old('released_date', $record->released_date ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Local Flight</legend>
    <input type="date" name="local_flight_date" value="{{ old('local_flight_date', $record->local_flight_date ?? '') }}"
           class="input w-full">
</fieldset>
