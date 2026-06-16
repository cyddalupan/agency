@extends('layouts.app')

@section('title', 'Edit ' . $jobPosition->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('employers.job-positions.show', [$employer, $jobPosition]) }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Position
        </a>
    </div>

    <div class="card bg-gradient-to-r from-amber-500/10 to-yellow-500/10 border border-amber-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>✏️</span> Edit Job Position
        </h2>
        <p class="opacity-60 text-sm mt-1">Updating: <strong>{{ $jobPosition->name }}</strong> for 🏢 <a href="{{ route('employers.show', $employer) }}" class="link link-primary">{{ $employer->name }}</a></p>
    </div>

    @if($errors->any())
        <div role="alert" class="alert alert-error mb-6 shadow-sm">
            <span>❌</span>
            <ul class="list-disc pl-4 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('employers.job-positions.update', [$employer, $jobPosition]) }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf @method('PUT')

            <fieldset class="fieldset">
                <legend class="fieldset-legend">💼 Position Title <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name', $jobPosition->name) }}" required
                    class="input w-full">
                @error('name') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📂 Base Position (from list)</legend>
                <select name="position_id" class="select w-full">
                    <option value="">-- Select --</option>
                    @foreach ($basePositions as $pos)
                        <option value="{{ $pos->id }}" @selected(old('position_id', $jobPosition->position_id) == $pos->id)>{{ $pos->name }}</option>
                    @endforeach
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📝 Description / Requirements</legend>
                <textarea name="content" rows="4" class="textarea w-full">{{ old('content', $jobPosition->content) }}</textarea>
            </fieldset>

            <div class="grid grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">⚤ Gender Preference</legend>
                    <select name="gender_preference" class="select w-full">
                        <option value="any" @selected(old('gender_preference', $jobPosition->gender_preference) === 'any')>👥 Any</option>
                        <option value="male" @selected(old('gender_preference', $jobPosition->gender_preference) === 'male')>♂️ Male</option>
                        <option value="female" @selected(old('gender_preference', $jobPosition->gender_preference) === 'female')>♀️ Female</option>
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💵 Currency</legend>
                    <select name="salary_currency" class="select w-full">
                        <option value="PHP" @selected(old('salary_currency', $jobPosition->salary_currency) === 'PHP')>🇵🇭 PHP</option>
                        <option value="USD" @selected(old('salary_currency', $jobPosition->salary_currency) === 'USD')>🇺🇸 USD</option>
                        <option value="SAR" @selected(old('salary_currency', $jobPosition->salary_currency) === 'SAR')>🇸🇦 SAR</option>
                    </select>
                </fieldset>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💵 Salary</legend>
                    <input type="number" step="0.01" name="salary" value="{{ old('salary', $jobPosition->salary) }}"
                        class="input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🎯 Total Slots</legend>
                    <input type="number" min="0" name="total_slots" value="{{ old('total_slots', $jobPosition->total_slots) }}"
                        class="input w-full">
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📊 Status</legend>
                <select name="status" class="select w-full">
                    <option value="open" @selected(old('status', $jobPosition->status) === 'open')>🟢 Open</option>
                    <option value="closed" @selected(old('status', $jobPosition->status) === 'closed')>🔴 Closed</option>
                    <option value="filled" @selected(old('status', $jobPosition->status) === 'filled')>🔵 Filled</option>
                </select>
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Update Job Position
                </button>
                <a href="{{ route('employers.job-positions.index', $employer) }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection