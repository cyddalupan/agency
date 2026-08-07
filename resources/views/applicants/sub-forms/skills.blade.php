<fieldset class="fieldset">
    <legend class="fieldset-legend">Skill Name <span class="text-error">*</span></legend>
    <select name="skill_name" class="select w-full">
        <option value="">-- Select Skill --</option>
        @foreach ($skills ?? [] as $skill)
            <option value="{{ $skill->name }}" @selected(old('skill_name', $record->skill_name ?? '') === $skill->name)>{{ $skill->name }}</option>
        @endforeach
    </select>
    <p class="text-xs opacity-60 mt-1">Only skills configured in Settings are allowed.</p>
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
