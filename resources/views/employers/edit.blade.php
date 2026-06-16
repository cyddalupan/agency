@extends('layouts.app')

@section('title', 'Edit ' . $employer->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('employers.show', $employer) }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Employer
        </a>
    </div>

    <div class="card bg-gradient-to-r from-amber-500/10 to-yellow-500/10 border border-amber-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>✏️</span> Edit Employer
        </h2>
        <p class="opacity-60 text-sm mt-1">Updating: <strong>{{ $employer->name }}</strong></p>
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

    <form action="{{ route('employers.update', $employer) }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf @method('PUT')

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏢 Company Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name', $employer->name) }}" required
                    class="input w-full">
                @error('name') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🔖 Company No.</legend>
                    <input type="text" name="company_no" value="{{ old('company_no', $employer->company_no) }}"
                        class="input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📊 Status</legend>
                    <select name="status" class="select w-full">
                        <option value="active" @selected(old('status', $employer->status) === 'active')>✅ Active</option>
                        <option value="inactive" @selected(old('status', $employer->status) === 'inactive')>⏸️ Inactive</option>
                    </select>
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">👤 Contact Person</legend>
                <input type="text" name="contact_person" value="{{ old('contact_person', $employer->contact_person) }}"
                    class="input w-full">
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📱 Phone</legend>
                    <input type="text" name="contact" value="{{ old('contact', $employer->contact) }}"
                        class="input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">✉️ Email</legend>
                    <input type="email" name="email" value="{{ old('email', $employer->email) }}"
                        class="input w-full">
                    @error('email') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏠 Address</legend>
                <textarea name="address" rows="3" class="textarea w-full">{{ old('address', $employer->address) }}</textarea>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🌍 Country</legend>
                <select name="country_id" class="select w-full">
                    <option value="">Select Country</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}" @selected(old('country_id', $employer->country_id) == $country->id)>{{ $country->name }}</option>
                    @endforeach
                </select>
            </fieldset>

            @include('partials.custom-fields-form', ['modelType' => 'Employer', 'model' => $employer])

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Update Employer
                </button>
                <a href="{{ route('employers.show', $employer) }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection