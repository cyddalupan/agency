@extends('layouts.app')

@section('title', 'Add Employer')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('employers.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Employers
        </a>
    </div>

    <div class="card bg-gradient-to-br from-secondary/10 to-accent/10 border border-secondary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>➕</span> Add New Employer
        </h2>
        <p class="opacity-60 text-sm mt-1">Register a new client company hiring overseas workers.</p>
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

    <form action="{{ route('employers.store') }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏢 Company Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="input w-full" placeholder="Company name">
                @error('name') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🔖 Company No.</legend>
                    <input type="text" name="company_no" value="{{ old('company_no') }}"
                        class="input w-full" placeholder="e.g. EMP-001">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📊 Status</legend>
                    <select name="status" class="select w-full">
                        <option value="active" @selected(old('status') === 'active')>✅ Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>⏸️ Inactive</option>
                    </select>
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">👤 Contact Person</legend>
                <input type="text" name="contact_person" value="{{ old('contact_person') }}"
                    class="input w-full" placeholder="Full name">
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📱 Phone</legend>
                    <input type="text" name="contact" value="{{ old('contact') }}"
                        class="input w-full" placeholder="Phone number">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">✉️ Email</legend>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="input w-full" placeholder="email@company.com">
                    @error('email') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏠 Address</legend>
                <textarea name="address" rows="3" class="textarea w-full" placeholder="Office address">{{ old('address') }}</textarea>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🌍 Country</legend>
                <select name="country_id" class="select w-full">
                    <option value="">Select Country</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}" @selected(old('country_id') == $country->id)>{{ $country->name }}</option>
                    @endforeach
                </select>
            </fieldset>

            @include('partials.custom-fields-form', ['modelType' => 'Employer', 'model' => null])

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Save Employer
                </button>
                <a href="{{ route('employers.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection