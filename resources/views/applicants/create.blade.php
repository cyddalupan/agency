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

    <form method="POST" action="{{ route('applicants.store') }}" class="card bg-base-100 shadow-sm card-lift">
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
                    <legend class="fieldset-legend">🎂 Birthdate</legend>
                    <input type="date" name="birthdate" value="{{ old('birthdate') }}"
                        class="input w-full">
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏠 Address</legend>
                <textarea name="address" rows="2" class="textarea w-full" placeholder="Complete address">{{ old('address') }}</textarea>
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📱 Source</legend>
                    <select name="source" class="select w-full">
                        <option value="">Select</option>
                        <option value="Facebook" @selected(old('source') === 'Facebook')>📘 Facebook</option>
                        <option value="Referral" @selected(old('source') === 'Referral')>🤝 Referral</option>
                        <option value="Walk-in" @selected(old('source') === 'Walk-in')>🚶 Walk-in</option>
                        <option value="Website" @selected(old('source') === 'Website')>🌐 Website</option>
                        <option value="Other" @selected(old('source') === 'Other')>📌 Other</option>
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📝 Remarks</legend>
                    <textarea name="remarks" rows="2" class="textarea w-full" placeholder="Any notes">{{ old('remarks') }}</textarea>
                </fieldset>
            </div>

            @include('partials.custom-fields-form', ['modelType' => 'Applicant', 'model' => null])

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Save Applicant
                </button>
                <a href="{{ route('applicants.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection