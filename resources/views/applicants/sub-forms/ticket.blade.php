<fieldset class="fieldset">
    <legend class="fieldset-legend">Airline</legend>
    <input type="text" name="airline" value="{{ old('airline', $record->airline ?? '') }}"
           class="input w-full" placeholder="e.g. Saudia">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Flight Date</legend>
    <input type="date" name="flight_date" value="{{ old('flight_date', $record->flight_date ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Flight Time</legend>
    <input type="text" name="flight_time" value="{{ old('flight_time', $record->flight_time ?? '') }}"
           class="input w-full" placeholder="e.g. 10:30">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Flight Remarks</legend>
    <input type="text" name="flight_remarks" value="{{ old('flight_remarks', $record->flight_remarks ?? '') }}"
           class="input w-full" placeholder="e.g. Direct">
</fieldset>
