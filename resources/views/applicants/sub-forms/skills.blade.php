<fieldset class="fieldset">
    <legend class="fieldset-legend">Skill Name <span class="text-error">*</span></legend>
    <input type="text" name="skill_name" value="{{ old('skill_name', $record->skill_name ?? '') }}"
           class="input w-full" placeholder="e.g. Caregiving">
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
