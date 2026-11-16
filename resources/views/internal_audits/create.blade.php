@extends('layouts.app')

@section('title', 'Plan an internal audit')

@section('content')
<div class="p-4 sm:p-6 space-y-6 max-w-4xl">

    <div class="flex items-center gap-3">
        <a href="{{ route('internal_audits.index') }}" class="btn btn-sm btn-ghost" id="back-to-audits">← Back</a>
        <h1 class="text-2xl font-extrabold">📋 Plan an internal audit</h1>
    </div>

    @if ($errors->any())
        <div class="alert alert-error" id="form-errors">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('internal_audits.store') }}" class="card bg-base-100 shadow">
        @csrf
        <div class="card-body space-y-4">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="form-control">
                    <label class="label" for="ref_no"><span class="label-text font-semibold">Reference</span></label>
                    <input type="text" name="ref_no" id="ref_no" value="{{ old('ref_no', $suggestedRef) }}"
                           class="input input-bordered input-sm font-mono" required>
                    <label class="label"><span class="label-text-alt opacity-60">Suggested: {{ $suggestedRef }}</span></label>
                </div>
                <div class="form-control">
                    <label class="label" for="scheduled_date"><span class="label-text font-semibold">Scheduled</span></label>
                    <input type="date" name="scheduled_date" id="scheduled_date"
                           value="{{ old('scheduled_date', now()->toDateString()) }}" class="input input-bordered input-sm">
                </div>
                <div class="form-control">
                    <label class="label" for="conducted_date"><span class="label-text font-semibold">Conducted</span></label>
                    <input type="date" name="conducted_date" id="conducted_date"
                           value="{{ old('conducted_date') }}" class="input input-bordered input-sm">
                    <label class="label"><span class="label-text-alt opacity-60">Leave blank until the audit runs.</span></label>
                </div>
            </div>

            <div class="form-control">
                <label class="label" for="title"><span class="label-text font-semibold">Title</span></label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                       placeholder="e.g. Payroll process audit" class="input input-bordered input-sm" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label" for="scope"><span class="label-text font-semibold">Scope</span></label>
                    <textarea name="scope" id="scope" rows="3" class="textarea textarea-bordered textarea-sm"
                              placeholder="What is being looked at?">{{ old('scope') }}</textarea>
                </div>
                <div class="form-control">
                    <label class="label" for="criteria"><span class="label-text font-semibold">Criteria</span></label>
                    <textarea name="criteria" id="criteria" rows="3" class="textarea textarea-bordered textarea-sm"
                              placeholder="Which procedure or standard is it being held to?">{{ old('criteria') }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label" for="lead_auditor_id"><span class="label-text font-semibold">Lead auditor</span></label>
                    <select name="lead_auditor_id" id="lead_auditor_id" class="select select-bordered select-sm">
                        <option value="">Unassigned</option>
                        @foreach ($auditors as $id => $label)
                            <option value="{{ $id }}" @selected((string) old('lead_auditor_id') === (string) $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label" for="status"><span class="label-text font-semibold">Status</span></label>
                    <select name="status" id="status" class="select select-bordered select-sm" required>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'planned') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-control">
                <label class="label" for="summary"><span class="label-text font-semibold">Summary</span></label>
                <textarea name="summary" id="summary" rows="2" class="textarea textarea-bordered textarea-sm"
                          placeholder="Optional. What did the audit conclude?">{{ old('summary') }}</textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="btn btn-primary btn-sm" id="audit-save">Save audit</button>
                <a href="{{ route('internal_audits.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
