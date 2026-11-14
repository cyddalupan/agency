@extends('layouts.app')

@section('title', $record ? 'Edit Welfare' : 'Create Welfare')

@php
    use App\Models\ApplicantWelfareRecord;
    $isEdit = (bool) $record;
@endphp

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('welfare.index') }}" class="link link-secondary text-sm">← Back to Welfare</a>
        <h2 class="text-2xl font-bold mt-2">{{ $isEdit ? '✏️ Edit Welfare' : '➕ Create Welfare' }}</h2>
        <p class="opacity-60 text-sm mt-1">
            {{ $isEdit ? ($record->ofw_full_name ?: 'Welfare record') : 'Log a welfare contact, issue or action taken' }}
        </p>
    </div>

    @if ($errors->any())
        <div role="alert" class="alert alert-error mb-4 text-sm shadow-sm">
            <span>⚠️</span>
            <div>
                <p class="font-semibold">Please fix the following:</p>
                <ul class="list-disc list-inside text-xs mt-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST"
          action="{{ $isEdit ? route('welfare.update', $record) : route('welfare.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <input type="hidden" name="applicant_id" id="applicant_id"
               value="{{ old('applicant_id', $record->applicant_id ?? $applicant->id ?? '') }}">

        {{-- ENCODER / DATE OF CONTACT --}}
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Encoder</span>
                        <input type="text" name="logged_by"
                               value="{{ old('logged_by', $record->encoder ?? $record->logged_by ?? auth()->user()->name) }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Date of Contact</span>
                        <input type="date" name="date_of_contact"
                               value="{{ old('date_of_contact', optional($record->date_of_contact ?? null)->format('Y-m-d') ?? now()->toDateString()) }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                </div>
            </div>
        </div>

        {{-- MIGRANT WORKER PROFILE --}}
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title text-base">🧳 Migrant Worker Profile</h3>

                {{-- Name: type to search all applicants, pick one, fields fill in. --}}
                <div class="relative mt-1 mb-3">
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">
                            Full Name of Applicant
                            @unless($isEdit)<span class="text-error">*</span>@endunless
                        </span>
                        <input type="text" name="ofw_full_name" id="applicant-name" required autocomplete="off"
                               value="{{ old('ofw_full_name', $record->ofw_full_name ?? $applicant->full_name ?? '') }}"
                               placeholder="Type a name to search applicants…"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <div id="applicant-suggestions"
                         class="hidden absolute z-50 left-0 right-0 mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-72 overflow-y-auto"></div>
                    <p class="text-[11px] opacity-50 mt-1">
                        Pick a name from the list to fill in the applicant's passport, country, position and FRA.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Passport Number</span>
                        <input type="text" name="passport_number" id="f-passport"
                               value="{{ old('passport_number', $record->passport_number ?? $applicant->passport->passport_no ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">DMW E-Reg ID</span>
                        <input type="text" name="dmw_ereg_id" value="{{ old('dmw_ereg_id', $record->dmw_ereg_id ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Country</span>
                        <input type="text" name="country_of_destination" id="f-country" list="country-list"
                               value="{{ old('country_of_destination', $record->country_of_destination ?? $applicant->country->name ?? '') }}"
                               class="input input-bordered input-sm w-full">
                        <datalist id="country-list">
                            @foreach($countries as $c)<option value="{{ $c->name }}"></option>@endforeach
                        </datalist>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Job Site City</span>
                        <input type="text" name="job_site_city" value="{{ old('job_site_city', $record->job_site_city ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Position / Designation</span>
                        <input type="text" name="position_designation" id="f-position"
                               value="{{ old('position_designation', $record->position_designation ?? $applicant->position->name ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Contact No. Abroad</span>
                        <input type="text" name="contact_no_abroad" value="{{ old('contact_no_abroad', $record->contact_no_abroad ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                </div>
            </div>
        </div>

        {{-- FOREIGN PRINCIPAL & EMPLOYER DETAILS --}}
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title text-base">🏢 Foreign Principal &amp; Employer Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-1">
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Foreign Recruitment Agency (FRA)</span>
                        <input type="text" name="foreign_recruitment_agency" id="f-fra" list="fra-list"
                               value="{{ old('foreign_recruitment_agency', $record->foreign_recruitment_agency ?? '') }}"
                               class="input input-bordered input-sm w-full">
                        <datalist id="fra-list">
                            @foreach($employers as $e)<option value="{{ $e->name }}"></option>@endforeach
                        </datalist>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Direct Employer Name</span>
                        <input type="text" name="direct_employer_name" value="{{ old('direct_employer_name', $record->direct_employer_name ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Contact Person &amp; Phone Number</span>
                        <input type="text" name="contact_person_phone" value="{{ old('contact_person_phone', $record->contact_person_phone ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                </div>
            </div>
        </div>

        {{-- MANDATORY MONITORING MILESTONES --}}
        @php
            $savedMilestones = old('milestones', is_array($record->milestones ?? null) ? $record->milestones : []);
        @endphp
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title text-base">📅 Mandatory Monitoring Milestones</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-1">
                    @foreach(ApplicantWelfareRecord::MILESTONES as $key => $label)
                        <label class="label cursor-pointer justify-start gap-3 border border-base-300 rounded-lg px-3 py-2">
                            <input type="checkbox" name="milestones[]" value="{{ $key }}" class="checkbox checkbox-sm checkbox-primary"
                                   @checked(in_array($key, (array) $savedMilestones, true))>
                            <span class="label-text text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- WELFARE & WORKING CONDITIONS ASSESSMENT INDICATORS --}}
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title text-base">✅ Welfare &amp; Working Conditions Assessment Indicators</h3>
                <div class="space-y-2 mt-1">
                    @foreach(ApplicantWelfareRecord::INDICATORS as $key => $label)
                        @php
                            $val = old($key, $record->{$key} ?? '');
                            $specify = old($key . '_specify', $record->{$key . '_specify'} ?? '');
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center border-b border-base-200 pb-2">
                            <span class="text-sm">{{ $label }}</span>
                            <div class="flex gap-4 justify-center">
                                <label class="label cursor-pointer gap-1">
                                    <input type="radio" name="{{ $key }}" value="yes" class="radio radio-sm radio-success"
                                           @checked($val === 'yes')>
                                    <span class="label-text text-xs">YES</span>
                                </label>
                                <label class="label cursor-pointer gap-1">
                                    <input type="radio" name="{{ $key }}" value="no" class="radio radio-sm radio-error"
                                           @checked($val === 'no')>
                                    <span class="label-text text-xs">NO</span>
                                </label>
                            </div>
                            <input type="text" name="{{ $key }}_specify" value="{{ $specify }}"
                                   placeholder="Specify / remarks" class="input input-bordered input-xs w-full">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- REMARKS + UPLOAD --}}
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title text-base">📝 Remarks / Issues Identified / Actions Taken</h3>
                <textarea name="remarks" rows="5" class="textarea textarea-bordered w-full mt-1"
                          placeholder="Remarks, issues identified, actions taken (if any)">{{ old('remarks', $record->remarks ?? '') }}</textarea>

                <div class="mt-4 pt-4 border-t border-base-200">
                    <span class="label-text text-xs opacity-60">Upload document</span>
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                           class="file-input file-input-bordered file-input-sm w-full mt-1">
                    <p class="text-[11px] opacity-50 mt-1">PDF, JPG, PNG, DOC or DOCX · up to 10 MB.</p>
                    @if($isEdit && $record->document_path)
                        <div class="flex items-center gap-2 mt-2 text-xs">
                            <span class="badge badge-success badge-sm">Uploaded</span>
                            <a href="{{ route('welfare.document', $record) }}" class="link link-primary truncate" target="_blank">
                                {{ $record->document_name ?: 'document' }}
                            </a>
                            <span class="opacity-40">— choosing a new file replaces it</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 mb-10">
            <a href="{{ route('welfare.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">💾 Save Welfare Record</button>
        </div>
    </form>
</div>

<script>
(function () {
    const input = document.getElementById('applicant-name');
    const box = document.getElementById('applicant-suggestions');
    const hidden = document.getElementById('applicant_id');
    const lookupUrl = @json(route('welfare.lookup'));
    let timer = null;

    function hide() { box.classList.add('hidden'); box.innerHTML = ''; }

    function render(list) {
        if (!list.length) {
            box.innerHTML = '<div class="px-3 py-2 text-xs opacity-60">No matching applicants.</div>';
            box.classList.remove('hidden');
            return;
        }
        box.innerHTML = list.map((a) => `
            <button type="button" class="w-full text-left px-3 py-2 hover:bg-base-200 border-b border-base-100 last:border-0"
                    data-applicant='${JSON.stringify(a).replace(/'/g, '&#39;')}'>
                <div class="text-sm font-medium">${a.name || '—'}</div>
                <div class="text-[11px] opacity-60">${a.passport || 'no passport'} · ${a.country || '—'} · ${a.position || '—'}</div>
            </button>`).join('');
        box.querySelectorAll('button[data-applicant]').forEach((el) => {
            el.addEventListener('click', () => {
                const a = JSON.parse(el.getAttribute('data-applicant'));
                input.value = a.name || '';
                hidden.value = a.id || '';
                document.getElementById('f-passport').value = a.passport || '';
                document.getElementById('f-country').value = a.country || '';
                document.getElementById('f-position').value = a.position || '';
                document.getElementById('f-fra').value = a.fra || '';
                hide();
            });
        });
        box.classList.remove('hidden');
    }

    input.addEventListener('input', function () {
        // Typing over a picked name means it is no longer linked to that applicant.
        hidden.value = '';
        clearTimeout(timer);
        const q = input.value.trim();
        if (q.length < 2) { hide(); return; }
        timer = setTimeout(async () => {
            try {
                const r = await fetch(lookupUrl + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
                if (!r.ok) { hide(); return; }
                const data = await r.json();
                render(data.applicants || []);
            } catch (e) { hide(); }
        }, 250);
    });

    document.addEventListener('click', (e) => {
        if (!box.contains(e.target) && e.target !== input) hide();
    });
    box.addEventListener('click', (e) => e.stopPropagation());
})();
</script>
@endsection
