@extends('layouts.app')

@section('title', 'Add Applicant')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('applicants.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Applicants
        </a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>➕</span> Add New Applicant
        </h2>
        <p class="opacity-60 text-sm mt-1">Fill in the applicant's personal information to get started.</p>
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

    <form method="POST" action="{{ route('applicants.store') }}" enctype="multipart/form-data" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">👤 First Name <span class="text-error">*</span></legend>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                        class="input w-full" placeholder="First name">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Middle Name</legend>
                    <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                        class="input w-full" placeholder="Middle name">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">👤 Last Name <span class="text-error">*</span></legend>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                        class="input w-full" placeholder="Last name">
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🔤 Suffix</legend>
                    <input type="text" name="suffix" value="{{ old('suffix') }}" placeholder="Jr., III"
                        class="input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">⚤ Gender</legend>
                    <select name="gender" class="select w-full">
                        <option value="">Select</option>
                        <option value="male" @selected(old('gender') === 'male')>♂️ Male</option>
                        <option value="female" @selected(old('gender') === 'female')>♀️ Female</option>
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💍 Civil Status</legend>
                    <select name="civil_status_id" class="select w-full">
                        <option value="">Select</option>
                        @foreach ($civilStatuses as $cs)
                            <option value="{{ $cs->id }}" @selected(old('civil_status_id') == $cs->id)>{{ $cs->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🌏 Nationality</legend>
                    <select name="nationality_id" class="select w-full">
                        <option value="">Select</option>
                        @foreach ($nationalities as $n)
                            <option value="{{ $n->id }}" @selected(old('nationality_id') == $n->id)>{{ $n->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">⛪ Religion</legend>
                    <select name="religion_id" class="select w-full">
                        <option value="">Select</option>
                        @foreach ($religions as $r)
                            <option value="{{ $r->id }}" @selected(old('religion_id') == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🎂 Birthdate</legend>
                    <input type="date" name="birthdate" value="{{ old('birthdate') }}"
                        class="input w-full">
                </fieldset>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🛂 Passport <span class="text-xs opacity-60">(has passport?)</span></legend>
                    <select name="has_passport" class="select w-full">
                        <option value="">-- Select --</option>
                        <option value="with" @selected(old('has_passport') === 'with')>✅ With Passport</option>
                        <option value="without" @selected(old('has_passport') === 'without')>❌ Without Passport</option>
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📸 2x2 Photo</legend>
                    <input type="file" name="photo" accept="image/jpeg,image/png" class="file-input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📸 Full Body Photo <span class="text-xs opacity-60">(for CV)</span></legend>
                    <input type="file" name="full_body_photo" accept="image/jpeg,image/png" class="file-input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">✉️ Email</legend>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="input w-full" placeholder="email@example.com">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📱 Contact Number</legend>
                    <input type="text" name="contact" value="{{ old('contact') }}"
                        class="input w-full" placeholder="09XX-XXX-XXXX">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🎓 Education Level</legend>
                    <select name="education_level" class="select w-full">
                        <option value="">-- Select --</option>
                        <option value="high_school" @selected(old('education_level') === 'high_school')>High School</option>
                        <option value="vocational" @selected(old('education_level') === 'vocational')>Vocational / Associate Degree</option>
                        <option value="bachelor" @selected(old('education_level') === 'bachelor')>Bachelor's Degree</option>
                        <option value="master" @selected(old('education_level') === 'master')>Master's Degree</option>
                    </select>
                </fieldset>
            </div>

            {{-- Passport details — shown only when has_passport = with (Cyd 2026-08-31). --}}
            <div id="passport-fields" class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4" style="display: none;">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🛂 Passport Number</legend>
                    <input type="text" name="passport_no" value="{{ old('passport_no') }}" class="input w-full" placeholder="e.g. P1234567A">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📅 Date Issued</legend>
                    <input type="date" name="passport_issue_date" value="{{ old('passport_issue_date') }}" class="input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📍 Place Issued</legend>
                    <input type="text" name="passport_place_of_issue" value="{{ old('passport_place_of_issue') }}" class="input w-full" placeholder="e.g. DFA Manila">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">⏳ Expiration</legend>
                    <input type="date" name="passport_expiry_date" value="{{ old('passport_expiry_date') }}" class="input w-full">
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏠 Address</legend>
                <textarea name="address" rows="2" class="textarea w-full" placeholder="Complete address">{{ old('address') }}</textarea>
            </fieldset>

            {{-- (PI card) Family Information --}}
            <div class="divider mt-6">👪 Family Information</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">👩 Mother's Name</legend>
                    <input type="text" name="mother_name" value="{{ old('mother_name') }}" class="input w-full" placeholder="Mother's name">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">👩 Mother's Occupation</legend>
                    <input type="text" name="mother_occupation" value="{{ old('mother_occupation') }}" class="input w-full" placeholder="Mother's occupation">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">👨 Father's Name</legend>
                    <input type="text" name="father_name" value="{{ old('father_name') }}" class="input w-full" placeholder="Father's name">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">👨 Father's Occupation</legend>
                    <input type="text" name="father_occupation" value="{{ old('father_occupation') }}" class="input w-full" placeholder="Father's occupation">
                </fieldset>
            </div>

            {{-- (PI card) Skills & Languages, restricted to the Settings-configured lists --}}
            <div class="divider mt-6">🛠️ Skills</div>
            <div class="flex flex-wrap gap-3">
                @forelse ($skills ?? [] as $skill)
                    <label class="label cursor-pointer gap-2">
                        <input type="checkbox" name="skills[]" value="{{ $skill->name }}" class="checkbox checkbox-sm"
                            @checked(in_array($skill->name, old('skills', [])))>
                        <span class="label-text">{{ $skill->name }}</span>
                    </label>
                @empty
                    <p class="text-sm opacity-60">No skills configured in Settings yet.</p>
                @endforelse
            </div>
            <p class="text-xs opacity-60 mt-1">Only skills configured in Settings are allowed.</p>

            <div class="divider mt-6">🗣️ Languages</div>
            <div class="flex flex-wrap gap-3">
                @forelse ($languages ?? [] as $language)
                    <label class="label cursor-pointer gap-2">
                        <input type="checkbox" name="languages[]" value="{{ $language->name }}" class="checkbox checkbox-sm"
                            @checked(in_array($language->name, old('languages', [])))>
                        <span class="label-text">{{ $language->name }}</span>
                    </label>
                @empty
                    <p class="text-sm opacity-60">No languages configured in Settings yet.</p>
                @endforelse
            </div>
            <p class="text-xs opacity-60 mt-1">Only languages configured in Settings are allowed.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🌍 Preferred Country</legend>
                    <select name="country_id" class="select w-full">
                        <option value="">-- Select --</option>
                        @foreach (App\Models\Country::orderBy('name')->get() as $ctry)
                            <option value="{{ $ctry->id }}" @selected(old('country_id') == $ctry->id)>{{ $ctry->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💼 Preferred Position</legend>
                    <select name="position_id" class="select w-full">
                        <option value="">-- Select --</option>
                        @foreach ($positions as $pos)
                            <option value="{{ $pos->id }}" @selected(old('position_id') == $pos->id)>{{ $pos->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💵 Salary</legend>
                    <input type="number" step="0.01" min="0" name="expected_salary" value="{{ old('expected_salary') }}" class="input w-full" placeholder="e.g. 25000">
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🏢 FRA/Employer</legend>
                    <select name="employer_id" class="select w-full">
                        <option value="">No Employer</option>
                        @foreach (\App\Models\Employer::where('agency_id', auth()->user()->agency_id)->orderBy('name')->get() as $emp)
                            <option value="{{ $emp->id }}" @selected(old('employer_id') == $emp->id)>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📊 Status</legend>
                    <select name="status_code" class="select w-full">
                        <option value="">-- Select --</option>
                        @foreach ($statusCodes as $sc)
                            <option value="{{ $sc->code }}" @selected(old('status_code') == $sc->code)>{{ $sc->label }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📱 Source</legend>
                    <select name="source" class="select w-full" id="source-select">
                        <option value="">Select</option>
                        @foreach ($sources as $src)
                            <option value="{{ $src }}" @selected(old('source') === $src)>{{ $src }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset" id="branch-field">
                    <legend class="fieldset-legend">🏢 Branch</legend>
                    @if (auth()->user()->isBranchLocked())
                        {{-- Branch account: no dropdown — branch is auto-set to the user's own branch. --}}
                        <input type="hidden" name="branch_id" id="branch-select" value="{{ $defaultBranchId }}">
                        <div class="select select-bordered w-full opacity-80" aria-readonly="true">
                            {{ $branches->firstWhere('id', $defaultBranchId)?->name ?? '' }}
                        </div>
                    @else
                        <select name="branch_id" class="select w-full" id="branch-select">
                            <option value="">-- Select Branch --</option>
                            @foreach ($branches as $br)
                                <option value="{{ $br->id }}" @selected(old('branch_id', $defaultBranchId) == $br->id)>{{ $br->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </fieldset>
                <fieldset class="fieldset" id="agent-field" style="display:none;">
                    <legend class="fieldset-legend">🎯 Select Agent</legend>
                    <select name="agent_id" class="select w-full" id="agent-select">
                        <option value="">-- Select Agent --</option>
                        @foreach ($agents as $agt)
                            <option value="{{ $agt->id }}" data-branch="{{ $agt->branch_id }}" @selected(old('agent_id') == $agt->id)>{{ $agt->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📝 Remarks</legend>
                    <textarea name="remarks" rows="2" class="textarea w-full" placeholder="Any notes">{{ old('remarks') }}</textarea>
                </fieldset>
            </div>

            @if (!empty($defaults['enable_firstimer']))
            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🔄 Firstimer / Ex-Abroad</legend>
                    <select name="firstimer_type" class="select w-full">
                        <option value="">-- Select --</option>
                        @foreach (($defaults['firstimer_options'] ?? []) as $opt)
                            <option value="{{ strtolower($opt) }}" @selected(old('firstimer_type') === strtolower($opt))>{{ $opt }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>
            @endif

            @include('partials.custom-fields-form', ['modelType' => 'Applicant', 'model' => null])

            {{-- Contract / Contract Received moved to the tabbed Personal Information section --}}

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Save Applicant
                </button>
                <a href="{{ route('applicants.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sourceSelect = document.getElementById('source-select');
    const branchField = document.getElementById('branch-field');
    const branchSelect = document.getElementById('branch-select');
    const agentField = document.getElementById('agent-field');
    const agentSelect = document.getElementById('agent-select');
    const agents = Array.from(document.querySelectorAll('#agent-select option')).filter(o => o.value !== '');

    function toggleExtraFields() {
        // (Branch feature) Branch is ALWAYS visible & independent — never clear it.
        const showAgent = sourceSelect && sourceSelect.value !== '';

        if (branchField) branchField.style.display = '';
        if (agentField) agentField.style.display = showAgent ? '' : 'none';

        if (!showAgent && agentSelect) agentSelect.value = '';

        filterAgentsByBranch();
    }

    function filterAgentsByBranch() {
        if (!agentSelect || !branchSelect) return;
        const selectedBranch = branchSelect.value;
        const isBranchMode = sourceSelect && sourceSelect.value === 'Branch';
        agents.forEach(o => {
            const branch = o.getAttribute('data-branch');
            const visible = !isBranchMode || !selectedBranch || branch === selectedBranch;
            o.style.display = visible ? '' : 'none';
        });
        // If current selection hidden by filter, clear it.
        const sel = agentSelect.selectedOptions[0];
        if (sel && sel.style.display === 'none') agentSelect.value = '';
    }

    if (sourceSelect) sourceSelect.addEventListener('change', toggleExtraFields);
    if (branchSelect) branchSelect.addEventListener('change', filterAgentsByBranch);
    toggleExtraFields();

    // (Cyd 2026-08-31) Passport details appear only when "With Passport" is chosen.
    const passportName = 'has_passport';
    const passportSelect = document.querySelector('select[name="' + passportName + '"]');
    const passportFields = document.getElementById('passport-fields');
    if (passportSelect && passportFields) {
        const togglePassport = () => {
            passportFields.style.display = passportSelect.value === 'with' ? '' : 'none';
        };
        passportSelect.addEventListener('change', togglePassport);
        togglePassport();
    }
});
</script>
@endpush
@endsection