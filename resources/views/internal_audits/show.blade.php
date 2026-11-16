@extends('layouts.app')

@section('title', $audit->ref_no)

@section('content')
<div class="p-4 sm:p-6 space-y-6 max-w-5xl">

    @if (session('success'))
        <div class="alert alert-success" id="flash-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error" id="flash-error">{{ session('error') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('internal_audits.index') }}" class="btn btn-sm btn-ghost" id="back-to-list">← Register</a>
            <h1 class="text-2xl font-extrabold font-mono">{{ $audit->ref_no }}</h1>
            <span class="badge {{ $audit->status_badge }}" id="audit-status">{{ $audit->status_label }}</span>
        </div>
        <div class="text-xs opacity-60">
            {{ $audit->scheduled_date?->format('d M Y') ?? 'No date' }}
            @if ($audit->leadAuditor) · {{ trim($audit->leadAuditor->surname . ', ' . $audit->leadAuditor->name) }} @endif
        </div>
    </div>

    {{-- The audit itself: what was looked at and against what --}}
    <div class="card bg-base-100 shadow">
        <div class="card-body space-y-3">
            <h2 class="card-title text-lg" id="audit-title">{{ $audit->title }}</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-xs uppercase opacity-60 font-semibold">Scope</div>
                    <div id="audit-scope">{{ $audit->scope ?: 'Not recorded.' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase opacity-60 font-semibold">Criteria</div>
                    <div id="audit-criteria">{{ $audit->criteria ?: 'Not recorded.' }}</div>
                </div>
            </div>

            @if ($audit->summary)
                <div class="text-sm">
                    <div class="text-xs uppercase opacity-60 font-semibold">Summary</div>
                    <div id="audit-summary">{{ $audit->summary }}</div>
                </div>
            @endif

            <div class="flex items-center gap-3 text-xs">
                <span id="audit-finding-total" class="badge badge-sm badge-ghost">{{ $findings->count() }} {{ Str::plural('finding', $findings->count()) }}</span>
                @if ($audit->conducted_date)
                    <span class="opacity-60">Conducted {{ $audit->conducted_date->format('d M Y') }}</span>
                @endif
            </div>
        </div>
    </div>

    @if ($canPlan)
        <details class="card bg-base-100 shadow" @if ($errors->hasAny(['title','scope','criteria','status'])) open @endif>
            <summary class="cursor-pointer px-5 py-3 font-semibold text-sm select-none" id="plan-toggle">✏️ Edit the plan</summary>
            <div class="px-5 pb-5">
                <form method="POST" action="{{ route('internal_audits.update', $audit) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="form-control">
                            <label class="label py-1" for="title"><span class="label-text text-xs font-semibold">Title</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title', $audit->title) }}"
                                   class="input input-bordered input-sm" required>
                        </div>
                        <div class="form-control">
                            <label class="label py-1" for="status"><span class="label-text text-xs font-semibold">Status</span></label>
                            <select name="status" id="status" class="select select-bordered select-sm" required>
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $audit->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label py-1" for="scheduled_date"><span class="label-text text-xs font-semibold">Scheduled</span></label>
                            <input type="date" name="scheduled_date" id="scheduled_date"
                                   value="{{ old('scheduled_date', $audit->scheduled_date?->toDateString()) }}" class="input input-bordered input-sm">
                        </div>
                        <div class="form-control">
                            <label class="label py-1" for="conducted_date"><span class="label-text text-xs font-semibold">Conducted</span></label>
                            <input type="date" name="conducted_date" id="conducted_date"
                                   value="{{ old('conducted_date', $audit->conducted_date?->toDateString()) }}" class="input input-bordered input-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="form-control">
                            <label class="label py-1" for="scope"><span class="label-text text-xs font-semibold">Scope</span></label>
                            <textarea name="scope" id="scope" rows="2" class="textarea textarea-bordered textarea-sm">{{ old('scope', $audit->scope) }}</textarea>
                        </div>
                        <div class="form-control">
                            <label class="label py-1" for="criteria"><span class="label-text text-xs font-semibold">Criteria</span></label>
                            <textarea name="criteria" id="criteria" rows="2" class="textarea textarea-bordered textarea-sm">{{ old('criteria', $audit->criteria) }}</textarea>
                        </div>
                    </div>
                    <input type="hidden" name="lead_auditor_id" value="{{ $audit->lead_auditor_id }}">
                    <div class="form-control">
                        <label class="label py-1" for="summary"><span class="label-text text-xs font-semibold">Summary</span></label>
                        <textarea name="summary" id="summary" rows="2" class="textarea textarea-bordered textarea-sm">{{ old('summary', $audit->summary) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary" id="audit-update">Save changes</button>
                </form>
            </div>
        </details>
    @endif

    {{-- Findings --}}
    <div class="card bg-base-100 shadow">
        <div class="card-body space-y-4">
            <h2 class="card-title text-lg">Findings</h2>

            @forelse ($findings as $finding)
                <div class="border border-base-300 rounded-lg p-4 space-y-2" id="finding-row-{{ $finding->id }}">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono text-xs font-semibold" id="finding-no-{{ $finding->id }}">{{ $finding->finding_no }}</span>
                                <span class="badge badge-sm {{ $finding->type_badge }}" id="finding-type-{{ $finding->id }}">{{ $finding->type_label }}</span>
                                @if ($finding->clause_reference)
                                    <span class="text-xs opacity-60">Clause {{ $finding->clause_reference }}</span>
                                @endif
                            </div>
                            <div class="text-sm mt-1">{{ $finding->description }}</div>
                            @if ($finding->evidence)
                                <div class="text-xs opacity-60 mt-1"><span class="font-semibold">Evidence:</span> {{ $finding->evidence }}</div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            @if ($finding->non_conformity_id)
                                <a href="{{ route('non_conformities.show', $finding->non_conformity_id) }}"
                                   class="badge badge-warning gap-1" id="finding-nc-{{ $finding->id }}">
                                    ↗ {{ $finding->nonConformity?->ref_no }}
                                </a>
                            @elseif ($canConduct)
                                <form method="POST" action="{{ route('internal_audits.findings.raise_nc', [$audit, $finding]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-secondary" id="raise-nc-{{ $finding->id }}">
                                        ⚠️ Raise non-conformity
                                    </button>
                                </form>
                            @endif

                            @if ($canConduct && ! $finding->non_conformity_id)
                                <form method="POST" action="{{ route('internal_audits.findings.destroy', [$audit, $finding]) }}"
                                      onsubmit="return confirm('Remove this finding?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-ghost text-error" id="finding-delete-{{ $finding->id }}">Remove</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-sm opacity-60 py-4 text-center" id="findings-empty">
                    Nothing recorded yet. An audit with no findings is a result too.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Record a finding --}}
    @if ($canConduct && ! $audit->isClosed())
        <div class="card bg-base-100 shadow">
            <div class="card-body space-y-3">
                <h2 class="card-title text-lg">Record a finding</h2>
                <form method="POST" action="{{ route('internal_audits.findings.store', $audit) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="form-control">
                            <label class="label py-1" for="finding_no"><span class="label-text text-xs font-semibold">Number</span></label>
                            <input type="text" id="finding_no" value="{{ $nextFindingNo }}" class="input input-bordered input-sm font-mono" readonly>
                        </div>
                        <div class="form-control">
                            <label class="label py-1" for="type"><span class="label-text text-xs font-semibold">Type</span></label>
                            <select name="type" id="type" class="select select-bordered select-sm" required>
                                @foreach ($findingTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', 'minor_nc') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label py-1" for="clause_reference"><span class="label-text text-xs font-semibold">Clause</span></label>
                            <input type="text" name="clause_reference" id="clause_reference" value="{{ old('clause_reference') }}"
                                   placeholder="e.g. 4.2.1" class="input input-bordered input-sm">
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label py-1" for="description"><span class="label-text text-xs font-semibold">What was found</span></label>
                        <textarea name="description" id="description" rows="2" class="textarea textarea-bordered textarea-sm" required>{{ old('description') }}</textarea>
                    </div>

                    <div class="form-control">
                        <label class="label py-1" for="evidence"><span class="label-text text-xs font-semibold">Evidence</span></label>
                        <textarea name="evidence" id="evidence" rows="2" class="textarea textarea-bordered textarea-sm"
                                  placeholder="What did you look at that shows this?">{{ old('evidence') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary" id="finding-save">Record finding</button>
                </form>
            </div>
        </div>
    @elseif ($audit->isClosed())
        <div class="alert alert-info" id="audit-closed-notice">
            This audit is closed. Its findings stand as they were.
        </div>
    @endif
</div>
@endsection
