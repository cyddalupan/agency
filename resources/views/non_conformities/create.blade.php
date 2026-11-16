@extends('layouts.app')

@section('title', 'Record a non-conformity')

@section('content')
<div class="p-4 sm:p-6 space-y-6 max-w-4xl">

    <div class="flex items-center gap-3">
        <a href="{{ route('non_conformities.index') }}" class="btn btn-sm btn-ghost">← Back</a>
        <h1 class="text-2xl font-extrabold">🛠️ Record a non-conformity</h1>
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

    <form method="POST" action="{{ route('non_conformities.store') }}" class="card bg-base-100 shadow">
        @csrf
        <div class="card-body space-y-4">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="form-control">
                    <label class="label" for="ref_no"><span class="label-text font-semibold">Reference</span></label>
                    <input type="text" name="ref_no" id="ref_no" value="{{ old('ref_no', $nextRef) }}"
                           class="input input-bordered input-sm font-mono" required>
                    <label class="label"><span class="label-text-alt opacity-60">Suggested: {{ $nextRef }}</span></label>
                </div>
                <div class="form-control">
                    <label class="label" for="detected_date"><span class="label-text font-semibold">Detected</span></label>
                    <input type="date" name="detected_date" id="detected_date"
                           value="{{ old('detected_date', now()->toDateString()) }}" class="input input-bordered input-sm" required>
                </div>
                <div class="form-control">
                    <label class="label" for="severity"><span class="label-text font-semibold">Severity</span></label>
                    <select name="severity" id="severity" class="select select-bordered select-sm" required>
                        @foreach ($severities as $value => $label)
                            <option value="{{ $value }}" @selected(old('severity', 'minor') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label" for="source"><span class="label-text font-semibold">Found by</span></label>
                    <select name="source" id="source" class="select select-bordered select-sm" required>
                        @foreach ($sources as $value => $label)
                            <option value="{{ $value }}" @selected(old('source', 'audit') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label" for="process_area"><span class="label-text font-semibold">Process area</span></label>
                    <input type="text" name="process_area" id="process_area" value="{{ old('process_area') }}"
                           placeholder="e.g. Payroll, Applicant vetting" class="input input-bordered input-sm">
                </div>
            </div>

            <div class="form-control">
                <label class="label" for="description"><span class="label-text font-semibold">What was found</span></label>
                <textarea name="description" id="description" rows="4" class="textarea textarea-bordered" required
                          placeholder="State the finding factually: what you observed, where, and when.">{{ old('description') }}</textarea>
            </div>

            <div class="form-control">
                <label class="label" for="requirement_breached"><span class="label-text font-semibold">Requirement breached</span></label>
                <textarea name="requirement_breached" id="requirement_breached" rows="2" class="textarea textarea-bordered"
                          placeholder="The clause, procedure or policy that was not met — a finding is only testable against one.">{{ old('requirement_breached') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label" for="owner_id"><span class="label-text font-semibold">Owner</span></label>
                    <select name="owner_id" id="owner_id" class="select select-bordered select-sm">
                        <option value="">Unassigned</option>
                        @foreach ($owners as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('owner_id') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label" for="due_date"><span class="label-text font-semibold">Due date</span></label>
                    <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" class="input input-bordered input-sm">
                </div>
            </div>

            <div class="flex items-center gap-2 justify-end">
                <a href="{{ route('non_conformities.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
                <button type="submit" class="btn btn-primary btn-sm" id="nc-save">Record finding</button>
            </div>
        </div>
    </form>
</div>
@endsection
