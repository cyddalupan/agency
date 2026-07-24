@extends('layouts.app')

@section('title', 'Edit ' . $applicant->full_name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('applicants.show', $applicant) }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Applicant
        </a>
    </div>

    <div class="card bg-gradient-to-r from-amber-500/10 to-yellow-500/10 border border-amber-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>✏️</span> Edit Applicant
        </h2>
        <p class="opacity-60 text-sm mt-1">Updating: <strong>{{ $applicant->full_name }}</strong></p>
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

    <form method="POST" action="{{ route('applicants.update', $applicant) }}" enctype="multipart/form-data" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">👤 First Name <span class="text-error">*</span></legend>
                    <input type="text" name="first_name" value="{{ old('first_name', $applicant->first_name) }}" required
                        class="input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Middle Name</legend>
                    <input type="text" name="middle_name" value="{{ old('middle_name', $applicant->middle_name) }}"
                        class="input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">👤 Last Name <span class="text-error">*</span></legend>
                    <input type="text" name="last_name" value="{{ old('last_name', $applicant->last_name) }}" required
                        class="input w-full">
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🔤 Suffix</legend>
                    <input type="text" name="suffix" value="{{ old('suffix', $applicant->suffix) }}"
                        class="input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">⚤ Gender</legend>
                    <select name="gender" class="select w-full">
                        <option value="">Select</option>
                        <option value="male" @selected(old('gender', $applicant->gender) === 'male')>♂️ Male</option>
                        <option value="female" @selected(old('gender', $applicant->gender) === 'female')>♀️ Female</option>
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🛂 Passport</legend>
                    <select name="has_passport" class="select w-full">
                        <option value="">-- Select --</option>
                        <option value="with" @selected(old('has_passport', $applicant->has_passport) === 'with')>✅ With Passport</option>
                        <option value="without" @selected(old('has_passport', $applicant->has_passport) === 'without')>❌ Without Passport</option>
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🎂 Birthdate</legend>
                    <input type="date" name="birthdate" value="{{ old('birthdate', $applicant->birthdate?->format('Y-m-d')) }}"
                        class="input w-full">
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🛂 Passport</legend>
                    <select name="has_passport" class="select w-full">
                        <option value="">-- Select --</option>
                        <option value="with" @selected(old('has_passport', $applicant->has_passport) === 'with')>✅ With Passport</option>
                        <option value="without" @selected(old('has_passport', $applicant->has_passport) === 'without')>❌ Without Passport</option>
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📸 2x2 Photo</legend>
                    <input type="file" name="photo" accept="image/jpeg,image/png" class="file-input w-full">
                    @if ($applicant->photo)
                        <div class="mt-2">
                            <img src="{{ $applicant->photo_url }}" class="w-16 h-16 object-cover rounded" alt="Current photo">
                            <p class="text-xs opacity-60 mt-1">Current photo. Upload to replace.</p>
                        </div>
                    @endif
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📸 Full Body Photo <span class="text-xs opacity-60">(for CV)</span></legend>
                    <input type="file" name="full_body_photo" accept="image/jpeg,image/png" class="file-input w-full">
                    @if ($applicant->full_body_photo)
                        <div class="mt-2">
                            <img src="{{ $applicant->full_body_photo_url }}" class="w-24 object-cover rounded" alt="Full body photo">
                            <p class="text-xs opacity-60 mt-1">Current full body photo. Upload to replace.</p>
                        </div>
                    @endif
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">✉️ Email</legend>
                    <input type="email" name="email" value="{{ old('email', $applicant->email) }}"
                        class="input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📱 Contact Number</legend>
                    <input type="text" name="contact" value="{{ old('contact', $applicant->contact) }}"
                        class="input w-full">
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏠 Address</legend>
                <textarea name="address" rows="2" class="textarea w-full">{{ old('address', $applicant->address) }}</textarea>
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🌍 Preferred Country</legend>
                    <select name="country_id" class="select w-full">
                        <option value="">-- Select --</option>
                        @foreach (\App\Models\Country::orderBy('name')->get() as $ctry)
                            <option value="{{ $ctry->id }}" @selected(old('country_id', $applicant->country_id) == $ctry->id)>{{ $ctry->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💼 Preferred Position</legend>
                    <select name="position_id" class="select w-full">
                        <option value="">-- Select --</option>
                        @foreach (\App\Models\Position::orderBy('name')->get() as $pos)
                            <option value="{{ $pos->id }}" @selected(old('position_id', $applicant->position_id) == $pos->id)>{{ $pos->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📊 Status</legend>
                    <select name="status_code" class="select w-full">
                        @foreach (\App\Models\StatusCode::orderBy('sort_order')->get() as $sc)
                            <option value="{{ $sc->code }}" @selected(old('status_code', $applicant->status_code) == $sc->code)>{{ $sc->label }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📱 Source</legend>
                    <select name="source" class="select w-full" id="source-select">
                        <option value="">Select</option>
                        <option value="Facebook" @selected(old('source', $applicant->source) === 'Facebook')>📘 Facebook</option>
                        <option value="Referral" @selected(old('source', $applicant->source) === 'Referral')>🤝 Referral</option>
                        <option value="Walk-in" @selected(old('source', $applicant->source) === 'Walk-in')>🚶 Walk-in</option>
                        <option value="Website" @selected(old('source', $applicant->source) === 'Website')>🌐 Website</option>
                        <option value="Other" @selected(old('source', $applicant->source) === 'Other')>📌 Other</option>
                    </select>
                </fieldset>
                <fieldset class="fieldset agent-dropdown" id="agent-field" style="{{ old('source', $applicant->source) === 'Referral' ? '' : 'display: none;' }}">
                    <legend class="fieldset-legend">🎯 Referred By (Agent)</legend>
                    <select name="agent_id" class="select w-full" id="agent-select">
                        <option value="">-- Select Agent --</option>
                        @foreach (\App\Models\Agent::where('agency_id', resolve_agency_id())->where('status', 'active')->orderBy('name')->get() as $agt)
                            <option value="{{ $agt->id }}" @selected(old('agent_id', $applicant->agent_id) == $agt->id)>{{ $agt->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🏢 Employer</legend>
                    <select name="employer_id" class="select w-full">
                        <option value="">No Employer</option>
                        @foreach (\App\Models\Employer::where('agency_id', auth()->user()->agency_id)->get() as $emp)
                            <option value="{{ $emp->id }}" @selected(old('employer_id', $applicant->employer_id) == $emp->id)>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📝 Remarks</legend>
                    <textarea name="remarks" rows="2" class="textarea w-full">{{ old('remarks', $applicant->remarks) }}</textarea>
                </fieldset>
            </div>

            @include('partials.custom-fields-form', ['modelType' => 'Applicant', 'model' => $applicant])

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Update Applicant
                </button>
                <a href="{{ route('applicants.show', $applicant) }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sourceSelect = document.getElementById('source-select');
    const agentField = document.getElementById('agent-field');

    if (sourceSelect && agentField) {
        function toggleAgentField() {
            if (sourceSelect.value === 'Referral') {
                agentField.style.display = '';
            } else {
                agentField.style.display = 'none';
                document.getElementById('agent-select').value = '';
            }
        }

        sourceSelect.addEventListener('change', toggleAgentField);
        toggleAgentField();
    }
});
</script>
@endpush
@endsection