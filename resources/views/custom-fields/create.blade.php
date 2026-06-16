@extends('layouts.app')

@section('title', 'New Custom Field')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('custom-fields.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Custom Fields
        </a>
    </div>

    <div class="card bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-indigo-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>⚙️</span> New Custom Field
        </h2>
        <p class="opacity-60 text-sm mt-1">Define a new field for forms and profiles</p>
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

    <form action="{{ route('custom-fields.store') }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📋 Applies To <span class="text-error">*</span></legend>
                <select name="model_type" required class="select w-full">
                    <option value="">Select model type...</option>
                    <option value="Employer" @selected(old('model_type') === 'Employer')>🏢 Employer</option>
                    <option value="Applicant" @selected(old('model_type') === 'Applicant')>👤 Applicant</option>
                    <option value="Bill" @selected(old('model_type') === 'Bill')>🧾 Bill</option>
                </select>
                @error('model_type') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏷️ Field Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="input w-full" placeholder="e.g. TIN Number, Business Type, Website">
                @error('name') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📐 Field Type <span class="text-error">*</span></legend>
                <select name="type" required class="select w-full">
                    <option value="">Select type...</option>
                    <option value="text" @selected(old('type') === 'text')>🔤 Text</option>
                    <option value="textarea" @selected(old('type') === 'textarea')>📝 Textarea</option>
                    <option value="number" @selected(old('type') === 'number')>🔢 Number</option>
                    <option value="date" @selected(old('type') === 'date')>📅 Date</option>
                    <option value="select" @selected(old('type') === 'select')>📋 Dropdown (Select)</option>
                    <option value="checkbox" @selected(old('type') === 'checkbox')>✅ Checkbox</option>
                    <option value="url" @selected(old('type') === 'url')>🔗 URL</option>
                </select>
                @error('type') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset" id="options-fieldset" style="display: none;">
                <legend class="fieldset-legend">📋 Options <span class="text-error">*</span></legend>
                <textarea name="options" class="textarea w-full" rows="5"
                    placeholder="One option per line:&#10;Option 1&#10;Option 2&#10;Option 3">{{ old('options') }}</textarea>
                <p class="text-xs opacity-50 mt-1">Enter each option on a new line</p>
                @error('options') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🔘 Required</legend>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="required" value="0">
                        <input type="checkbox" name="required" value="1" class="checkbox checkbox-sm"
                            @checked(old('required', false))>
                        <span class="text-sm">Make this field mandatory</span>
                    </label>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🔢 Order</legend>
                    <input type="number" name="order" value="{{ old('order', 0) }}"
                        min="0" class="input w-full" placeholder="0">
                </fieldset>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Create Field
                </button>
                <a href="{{ route('custom-fields.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const typeSelect = document.querySelector('select[name="type"]');
    const optionsFieldset = document.getElementById('options-fieldset');

    function toggleOptions() {
        if (typeSelect.value === 'select') {
            optionsFieldset.style.display = 'block';
        } else {
            optionsFieldset.style.display = 'none';
        }
    }

    typeSelect.addEventListener('change', toggleOptions);
    toggleOptions();
</script>
@endpush
@endsection
