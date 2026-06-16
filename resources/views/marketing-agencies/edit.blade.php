@extends('layouts.app')

@section('title', 'Edit ' . $marketingAgency->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('marketing-agencies.show', $marketingAgency) }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Agency
        </a>
    </div>

    <div class="card bg-gradient-to-r from-amber-500/10 to-yellow-500/10 border border-amber-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>✏️</span> Edit Marketing Agency
        </h2>
        <p class="opacity-60 text-sm mt-1">Updating: <strong>{{ $marketingAgency->name }}</strong></p>
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

    <form action="{{ route('marketing-agencies.update', $marketingAgency) }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf @method('PUT')

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📢 Agency Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name', $marketingAgency->name) }}" required
                    class="input w-full">
                @error('name') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">👤 Contact Person</legend>
                <input type="text" name="contact_person" value="{{ old('contact_person', $marketingAgency->contact_person) }}"
                    class="input w-full">
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📱 Phone</legend>
                    <input type="text" name="contact" value="{{ old('contact', $marketingAgency->contact) }}"
                        class="input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">✉️ Email</legend>
                    <input type="email" name="email" value="{{ old('email', $marketingAgency->email) }}"
                        class="input w-full">
                    @error('email') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏠 Address</legend>
                <textarea name="address" rows="3" class="textarea w-full">{{ old('address', $marketingAgency->address) }}</textarea>
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💰 Commission Rate (%)</legend>
                    <input type="number" name="commission_rate" value="{{ old('commission_rate', $marketingAgency->commission_rate) }}"
                        class="input w-full" min="0" max="100" step="0.01">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📊 Status</legend>
                    <select name="status" class="select w-full">
                        <option value="active" @selected(old('status', $marketingAgency->status) === 'active')>✅ Active</option>
                        <option value="inactive" @selected(old('status', $marketingAgency->status) === 'inactive')>⏸️ Inactive</option>
                    </select>
                </fieldset>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Update Agency
                </button>
                <a href="{{ route('marketing-agencies.show', $marketingAgency) }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
