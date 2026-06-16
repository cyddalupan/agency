<fieldset class="fieldset">
    <legend class="fieldset-legend">Passport No.</legend>
    <input type="text" name="passport_no" value="{{ old('passport_no', $record->passport_no ?? '') }}"
           class="input w-full" placeholder="e.g. P12345678">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Issue Date</legend>
    <input type="date" name="issue_date" value="{{ old('issue_date', $record->issue_date ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Expiry Date</legend>
    <input type="date" name="expiry_date" value="{{ old('expiry_date', $record->expiry_date ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Place of Issue</legend>
    <input type="text" name="place_of_issue" value="{{ old('place_of_issue', $record->place_of_issue ?? '') }}"
           class="input w-full" placeholder="e.g. DFA Manila">
</fieldset>
