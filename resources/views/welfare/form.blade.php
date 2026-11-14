@extends('layouts.app')

@section('title', $record ? 'Edit Welfare' : 'Create Welfare')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('welfare.index') }}" class="link link-secondary text-sm">← Back to Welfare</a>
        <h2 class="text-2xl font-bold mt-2">{{ $record ? '✏️ Edit Welfare' : '➕ Create Welfare' }}</h2>
        <p class="opacity-60 text-sm mt-1">
            {{ $record ? $record->ofw_full_name : 'Log a welfare contact, issue or action taken' }}
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
          action="{{ $record ? route('welfare.update', $record) : route('welfare.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if($record) @method('PUT') @endif
        @if($applicant && !$record)
            <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">
        @endif

        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title text-base">🧳 Applicant details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="form-control md:col-span-2">
                        <span class="label-text text-xs opacity-60 mb-1">Name <span class="text-error">*</span></span>
                        <input type="text" name="ofw_full_name" required
                               value="{{ old('ofw_full_name', $record->ofw_full_name ?? $applicant->full_name ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Passport No.</span>
                        <input type="text" name="passport_number"
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
                        <input type="text" name="country_of_destination" list="country-list"
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
                        <span class="label-text text-xs opacity-60 mb-1">Position</span>
                        <input type="text" name="position_designation"
                               value="{{ old('position_designation', $record->position_designation ?? $applicant->position->name ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">FRA</span>
                        <input type="text" name="foreign_recruitment_agency" list="fra-list"
                               value="{{ old('foreign_recruitment_agency', $record->foreign_recruitment_agency ?? '') }}"
                               class="input input-bordered input-sm w-full">
                        <datalist id="fra-list">
                            @foreach($employers as $e)<option value="{{ $e->name }}"></option>@endforeach
                        </datalist>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Direct Employer</span>
                        <input type="text" name="direct_employer_name" value="{{ old('direct_employer_name', $record->direct_employer_name ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Contact No. Abroad</span>
                        <input type="text" name="contact_no_abroad" value="{{ old('contact_no_abroad', $record->contact_no_abroad ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Contact Person &amp; Phone</span>
                        <input type="text" name="contact_person_phone" value="{{ old('contact_person_phone', $record->contact_person_phone ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Date of Contact</span>
                        <input type="date" name="date_of_contact"
                               value="{{ old('date_of_contact', optional($record->date_of_contact ?? null)->format('Y-m-d') ?? now()->toDateString()) }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Encoder</span>
                        <input type="text" name="logged_by" value="{{ old('logged_by', $record->logged_by ?? auth()->user()->name) }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title text-base">📝 Remarks / Issues / Actions Taken</h3>
                <textarea name="remarks" rows="5" class="textarea textarea-bordered w-full"
                          placeholder="What was reported, what was done">{{ old('remarks', $record->remarks ?? '') }}</textarea>

                {{-- Card: "Add upload document below remarks" --}}
                <div class="mt-4 pt-4 border-t border-base-200">
                    <span class="label-text text-xs opacity-60">Upload document</span>
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                           class="file-input file-input-bordered file-input-sm w-full mt-1">
                    <p class="text-[11px] opacity-50 mt-1">PDF, JPG, PNG, DOC or DOCX · up to 10 MB.</p>
                    @if($record && $record->document_path)
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
            <button type="submit" class="btn btn-primary">{{ $record ? '💾 Save changes' : '💾 Create welfare' }}</button>
        </div>
    </form>
</div>
@endsection
