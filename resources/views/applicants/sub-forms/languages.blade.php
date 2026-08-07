<fieldset class="fieldset">
    <legend class="fieldset-legend">Language <span class="text-error">*</span></legend>
    <select name="name" class="select w-full">
        <option value="">-- Select Language --</option>
        @foreach ($languages ?? [] as $language)
            <option value="{{ $language->name }}" @selected(old('name', $record->name ?? '') === $language->name)>{{ $language->name }}</option>
        @endforeach
    </select>
    <p class="text-xs opacity-60 mt-1">Only languages configured in Settings are allowed.</p>
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
