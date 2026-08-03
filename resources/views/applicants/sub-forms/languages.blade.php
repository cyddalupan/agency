<fieldset class="fieldset">
    <legend class="fieldset-legend">Language <span class="text-error">*</span></legend>
    <input type="text" name="name" value="{{ old('name', $record->name ?? '') }}"
           class="input w-full" placeholder="e.g. English, Filipino, Arabic">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Proficiency</legend>
    <select name="proficiency" class="select w-full">
        <option value="">-- Select --</option>
        <option value="beginner" @selected(old('proficiency', $record->proficiency ?? '') === 'beginner')>Beginner</option>
        <option value="intermediate" @selected(old('proficiency', $record->proficiency ?? '') === 'intermediate')>Intermediate</option>
        <option value="expert" @selected(old('proficiency', $record->proficiency ?? '') === 'expert')>Expert</option>
    </select>
</fieldset>
