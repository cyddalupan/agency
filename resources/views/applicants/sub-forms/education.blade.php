<fieldset class="fieldset">
    <legend class="fieldset-legend">Level</legend>
    <select name="level" class="select w-full">
        <option value="">-- Select --</option>
        <option value="mba" @selected(old('level', $record->level ?? '') === 'mba')>MBA / Post Graduate</option>
        <option value="college" @selected(old('level', $record->level ?? '') === 'college')>College</option>
        <option value="high_school" @selected(old('level', $record->level ?? '') === 'high_school')>High School</option>
    </select>
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">School</legend>
    <input type="text" name="school" value="{{ old('school', $record->school ?? '') }}"
           class="input w-full" placeholder="School name">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Degree / Course</legend>
    <input type="text" name="degree" value="{{ old('degree', $record->degree ?? '') }}"
           class="input w-full" placeholder="e.g. BS Nursing">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Year Graduated</legend>
    <input type="text" name="year_graduated" value="{{ old('year_graduated', $record->year_graduated ?? '') }}"
           class="input w-full" placeholder="e.g. 2020">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Remarks</legend>
    <input type="text" name="remarks" value="{{ old('remarks', $record->remarks ?? '') }}"
           class="input w-full" placeholder="Optional notes">
</fieldset>
