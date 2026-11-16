@extends('layouts.app')

@section('title', 'Log a Complaint')

@section('content')
<div class="p-4 sm:p-6 space-y-6 max-w-4xl">

    <div class="flex items-center gap-3">
        <a href="{{ route('complaints.index') }}" class="btn btn-sm btn-ghost">← Back</a>
        <h1 class="text-2xl font-extrabold">📣 Log a Complaint</h1>
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

    <div class="alert alert-info" id="sla-notice">
        <span class="text-lg">⏱️</span>
        <div class="text-sm">
            This agency's response windows:
            acknowledge within <strong id="sla-ack">{{ $sla['ack_days'] }}</strong> day{{ $sla['ack_days'] === 1 ? '' : 's' }}
            and resolve within <strong id="sla-resolve">{{ $sla['resolve_days'] }}</strong> day{{ $sla['resolve_days'] === 1 ? '' : 's' }}.
            The deadlines are worked out from the date received and then stay fixed.
        </div>
    </div>

    <form method="POST" action="{{ route('complaints.store') }}" class="card bg-base-100 shadow">
        @csrf
        <div class="card-body space-y-4">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label" for="ref_no"><span class="label-text font-semibold">Reference</span></label>
                    <input type="text" name="ref_no" id="ref_no" value="{{ old('ref_no', $nextRef) }}"
                           class="input input-bordered input-sm font-mono" required>
                    <label class="label"><span class="label-text-alt opacity-60">Suggested: {{ $nextRef }}</span></label>
                </div>

                <div class="form-control">
                    <label class="label" for="date_received"><span class="label-text font-semibold">Date received</span></label>
                    <input type="date" name="date_received" id="date_received"
                           value="{{ old('date_received', now()->toDateString()) }}"
                           class="input input-bordered input-sm" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label" for="complainant_name"><span class="label-text font-semibold">Complainant</span></label>
                    <input type="text" name="complainant_name" id="complainant_name" value="{{ old('complainant_name') }}"
                           placeholder="Name of the person raising it" class="input input-bordered input-sm" required>
                </div>

                <div class="form-control">
                    <label class="label" for="complainant_type"><span class="label-text font-semibold">Raised by</span></label>
                    <select name="complainant_type" id="complainant_type" class="select select-bordered select-sm" required>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('complainant_type', \App\Models\Complaint::TYPE_APPLICANT) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label" for="category"><span class="label-text font-semibold">Category</span></label>
                    <input type="text" name="category" id="category" value="{{ old('category') }}"
                           placeholder="e.g. late payroll, document delay" class="input input-bordered input-sm">
                </div>

                <div class="form-control">
                    <label class="label" for="assigned_to"><span class="label-text font-semibold">Assign to</span></label>
                    <select name="assigned_to" id="assigned_to" class="select select-bordered select-sm">
                        <option value="">Unassigned</option>
                        @foreach ($assignees as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('assigned_to') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-control">
                <label class="label" for="description"><span class="label-text font-semibold">What happened</span></label>
                <textarea name="description" id="description" rows="6" class="textarea textarea-bordered"
                          placeholder="Record the complaint in the complainant's own terms, then note what you verified."
                          required>{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center gap-2 justify-end">
                <a href="{{ route('complaints.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
                <button type="submit" class="btn btn-primary btn-sm" id="complaint-save">Log complaint</button>
            </div>
        </div>
    </form>
</div>
@endsection
