@extends('layouts.app')

@section('title', 'Edit Report Template')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('report-templates.index') }}" class="text-sm opacity-70 hover:opacity-100">
            &larr; Back to Templates
        </a>
        <h1 class="text-2xl font-bold mt-2">Edit Template</h1>
    </div>

    <form action="{{ route('report-templates.update', $template) }}" method="POST" class="card bg-base-100 shadow-sm">
        @csrf
        @method('PUT')

        <div class="card-body space-y-4">
            {{-- Name --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Template Name</span>
                </label>
                <input type="text" name="name" class="input input-bordered @error('name') input-error @enderror"
                       value="{{ old('name', $template->name) }}" required>
                @error('name')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            {{-- Type --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Report Type</span>
                </label>
                <select name="type" class="select select-bordered @error('type') select-error @enderror" required>
                    <option value="">Select type...</option>
                    <option value="applicant_report" @selected(old('type', $template->type) === 'applicant_report')>Applicant Report</option>
                    <option value="statistics" @selected(old('type', $template->type) === 'statistics')>Statistics Dashboard</option>
                    <option value="transactions" @selected(old('type', $template->type) === 'transactions')>Transactions</option>
                </select>
                @error('type')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            {{-- Config --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Configuration (JSON)</span>
                    <span class="label-text-alt">Optional</span>
                </label>
                <textarea name="config" rows="8" class="textarea textarea-bordered font-mono text-xs @error('config') textarea-error @enderror"
                          placeholder='{
    "columns": ["name", "status", "country", "created_at"],
    "group_by": "status",
    "sort_by": "created_at",
    "sort_order": "desc",
    "date_preset": "this_month"
}'>{{ old('config', json_encode($template->config, JSON_PRETTY_PRINT)) }}</textarea>
                @error('config')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            {{-- Is Active --}}
            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <input type="checkbox" name="is_active" class="checkbox checkbox-primary" value="1"
                           {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                    <span class="label-text">Active</span>
                </label>
            </div>

            <div class="card-actions justify-end pt-2">
                <a href="{{ route('report-templates.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Template</button>
            </div>
        </div>
    </form>
</div>
@endsection
