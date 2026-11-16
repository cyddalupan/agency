@extends('layouts.app')

@section('title', $nc->ref_no)

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    @if (session('success'))
        <div class="alert alert-success" id="flash-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error" id="flash-error">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error" id="form-errors">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-center gap-3 flex-wrap">
        <a href="{{ route('non_conformities.index') }}" class="btn btn-sm btn-ghost">← Register</a>
        <h1 class="text-2xl font-extrabold font-mono">{{ $nc->ref_no }}</h1>
        <span class="badge badge-lg {{ $nc->status_badge }}" id="nc-status">{{ $nc->status_label }}</span>
        <span class="badge badge-lg {{ $nc->severity_badge }}" id="nc-severity">{{ $nc->severity_label }}</span>
        @if ($nc->is_overdue)
            <span class="badge badge-lg badge-error gap-1" id="badge-overdue">⏰ overdue</span>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            {{-- The finding itself --}}
            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-4">
                    <div>
                        <div class="text-xs uppercase tracking-wide opacity-60">Finding</div>
                        <p class="whitespace-pre-line text-sm mt-1" id="nc-description">{{ $nc->description }}</p>
                    </div>

                    @if ($nc->requirement_breached)
                        <div class="divider my-0"></div>
                        <div>
                            <div class="text-xs uppercase tracking-wide opacity-60">Requirement breached</div>
                            <p class="whitespace-pre-line text-sm mt-1" id="nc-requirement">{{ $nc->requirement_breached }}</p>
                        </div>
                    @endif

                    <div class="divider my-0"></div>
                    <div>
                        <div class="text-xs uppercase tracking-wide opacity-60">Containment</div>
                        <p class="whitespace-pre-line text-sm mt-1 {{ $nc->containment ? '' : 'opacity-50 italic' }}" id="nc-containment">
                            {{ $nc->containment ?: 'Not recorded yet — what was done immediately to stop it getting worse.' }}
                        </p>
                    </div>

                    <div class="divider my-0"></div>
                    <div>
                        <div class="text-xs uppercase tracking-wide opacity-60">Root cause</div>
                        <p class="whitespace-pre-line text-sm mt-1 {{ $nc->root_cause ? '' : 'opacity-50 italic' }}" id="nc-root-cause">
                            {{ $nc->root_cause ?: 'Not recorded yet — corrective action without a root cause treats the symptom.' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Corrective actions --}}
            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold text-lg">Corrective actions</h2>
                        <span class="badge badge-ghost" id="nc-action-total">{{ $nc->correctiveActions->count() }}</span>
                    </div>

                    @forelse ($nc->correctiveActions as $action)
                        <div class="border border-base-300 rounded-lg p-4 space-y-3" id="action-row-{{ $action->id }}">
                            <div class="flex items-start justify-between gap-3 flex-wrap">
                                <div class="flex-1">
                                    <div class="font-medium text-sm">{{ $action->action_text }}</div>
                                    <div class="text-xs opacity-60 mt-1">
                                        {{ $action->owner ? trim($action->owner->surname . ', ' . $action->owner->name) : 'Unassigned' }}
                                        @if ($action->due_date) · due {{ $action->due_date->format('d M Y') }} @endif
                                        @if ($action->completed_date) · completed {{ $action->completed_date->format('d M Y') }} @endif
                                    </div>
                                </div>
                                <span class="badge badge-sm {{ $action->status_badge }}" id="action-status-{{ $action->id }}">{{ $action->status_label }}</span>
                            </div>

                            {{-- The two sign-offs, shown as the gate sees them --}}
                            <div class="flex items-center gap-4 text-xs flex-wrap">
                                <span class="{{ $action->is_verified ? 'text-success font-semibold' : 'text-error' }}" id="action-verified-{{ $action->id }}">
                                    {{ $action->is_verified ? '✓ verified ' . $action->verified_at->format('d M Y') : '✗ not verified' }}
                                </span>
                                <span class="{{ $action->is_effective ? 'text-success font-semibold' : 'text-error' }}" id="action-effective-{{ $action->id }}">
                                    {{ $action->is_effective ? '✓ effectiveness reviewed ' . $action->effectiveness_verified_at->format('d M Y') : '✗ effectiveness not reviewed' }}
                                </span>
                            </div>

                            @if ($action->verification_notes)
                                <div class="text-xs"><span class="opacity-60">Verification:</span> {{ $action->verification_notes }}</div>
                            @endif
                            @if ($action->effectiveness_notes)
                                <div class="text-xs"><span class="opacity-60">Effectiveness:</span> {{ $action->effectiveness_notes }}</div>
                            @endif

                            @if ($canManage && ! $nc->is_closed)
                                <details class="text-xs">
                                    <summary class="cursor-pointer font-semibold select-none" id="action-toggle-{{ $action->id }}">Record progress</summary>
                                    <form method="POST" action="{{ route('non_conformities.actions.update', [$nc, $action]) }}" class="space-y-2 mt-3">
                                        @csrf @method('PUT')

                                        <div class="form-control">
                                            <label class="label py-1" for="verification_notes_{{ $action->id }}"><span class="label-text text-xs">Verification notes — was it actually carried out?</span></label>
                                            <textarea name="verification_notes" id="verification_notes_{{ $action->id }}" rows="1" class="textarea textarea-bordered textarea-xs">{{ $action->verification_notes }}</textarea>
                                        </div>

                                        <div class="form-control">
                                            <label class="label py-1" for="effectiveness_notes_{{ $action->id }}"><span class="label-text text-xs">Effectiveness notes — did it stop the recurrence?</span></label>
                                            <textarea name="effectiveness_notes" id="effectiveness_notes_{{ $action->id }}" rows="1" class="textarea textarea-bordered textarea-xs">{{ $action->effectiveness_notes }}</textarea>
                                        </div>

                                        <div class="flex items-center gap-4 flex-wrap">
                                            <label class="label cursor-pointer gap-2 py-1">
                                                <input type="checkbox" name="complete" value="1" class="checkbox checkbox-xs" id="action-complete-{{ $action->id }}" @checked($action->completed_date !== null)>
                                                <span class="label-text text-xs">Complete</span>
                                            </label>
                                            <label class="label cursor-pointer gap-2 py-1">
                                                <input type="checkbox" name="verify" value="1" class="checkbox checkbox-xs" id="action-verify-{{ $action->id }}" @checked($action->is_verified)>
                                                <span class="label-text text-xs">Verified</span>
                                            </label>
                                            <label class="label cursor-pointer gap-2 py-1">
                                                <input type="checkbox" name="effectiveness" value="1" class="checkbox checkbox-xs" id="action-effectiveness-{{ $action->id }}" @checked($action->is_effective)>
                                                <span class="label-text text-xs">Effectiveness reviewed</span>
                                            </label>
                                            <button type="submit" class="btn btn-xs btn-primary" id="action-save-{{ $action->id }}">Save</button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('non_conformities.actions.destroy', [$nc, $action]) }}" class="mt-1"
                                          onsubmit="return confirm('Remove this corrective action?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-ghost text-error" id="action-delete-{{ $action->id }}">Remove action</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-6" id="nc-no-actions">
                            <p class="font-medium text-sm">No corrective actions yet.</p>
                            <p class="text-xs opacity-60">Without one there is nothing to verify, so this cannot be closed.</p>
                        </div>
                    @endforelse

                    @if ($canManage && ! $nc->is_closed)
                        <form method="POST" action="{{ route('non_conformities.actions.store', $nc) }}" class="border-t border-base-300 pt-4 space-y-2">
                            @csrf
                            <label class="label py-1" for="action_text"><span class="label-text font-semibold text-sm">Add a corrective action</span></label>
                            <textarea name="action_text" id="action_text" rows="2" class="textarea textarea-bordered w-full"
                                      placeholder="What will be done about the root cause, not just the symptom." required></textarea>
                            <div class="flex items-end gap-3 flex-wrap">
                                <div class="form-control">
                                    <label class="label py-1" for="action_owner"><span class="label-text text-xs">Owner</span></label>
                                    <select name="owner_id" id="action_owner" class="select select-bordered select-xs">
                                        <option value="">Unassigned</option>
                                        @foreach ($owners as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-control">
                                    <label class="label py-1" for="action_due"><span class="label-text text-xs">Due</span></label>
                                    <input type="date" name="due_date" id="action_due" class="input input-bordered input-xs">
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary" id="action-add">Add action</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Closure --}}
            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-3">
                    <h2 class="font-bold text-lg">Closure</h2>

                    @if ($nc->is_closed)
                        <div class="alert alert-success" id="nc-closed-notice">
                            <span>✅</span>
                            <span class="text-sm">Closed {{ $nc->closed_at?->format('d M Y H:i') }} — every action was verified and its effectiveness reviewed.</span>
                        </div>
                    @elseif (! $canManage)
                        <p class="text-sm opacity-60" id="closure-readonly">Your role can audit this record but not close it.</p>
                    @elseif ($blockers === [])
                        <div class="alert alert-success" id="closure-ready">
                            <span>✅</span>
                            <span class="text-sm">Every corrective action is verified and effectiveness-reviewed. This can be closed.</span>
                        </div>
                        <form method="POST" action="{{ route('non_conformities.close', $nc) }}" id="close-form">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" id="btn-close">🗄️ Close non-conformity</button>
                        </form>
                    @else
                        {{-- Disabled, with the actual reasons, not just a lock icon. --}}
                        <div class="alert alert-warning" id="closure-blocked">
                            <span>🔒</span>
                            <div class="text-sm">
                                <div class="font-semibold">This cannot be closed yet.</div>
                                <ul class="list-disc list-inside mt-1 space-y-0.5">
                                    @foreach ($blockers as $blocker)
                                        <li>{{ $blocker }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('non_conformities.close', $nc) }}" id="close-form">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-disabled" id="btn-close" disabled title="Every action must be verified and effectiveness-reviewed first.">
                                🗄️ Close non-conformity
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-2 text-sm">
                    <h2 class="font-bold mb-1">Details</h2>
                    <div class="flex items-center justify-between"><span class="opacity-70">Found by</span><span>{{ $nc->source_label }}</span></div>
                    {{-- QMS-18: the other half of the finding link. A non-conformity
                         raised from an audit says so here, and says which one. --}}
                    @if ($auditFinding = $nc->auditFinding)
                        <div class="flex items-center justify-between"><span class="opacity-70">Raised from</span>
                            <a href="{{ route('internal_audits.show', $auditFinding->internal_audit_id) }}"
                               class="link link-hover font-mono text-xs" id="nc-from-audit">
                                {{ $auditFinding->internalAudit?->ref_no }} · {{ $auditFinding->finding_no }}
                            </a>
                        </div>
                    @endif
                    <div class="flex items-center justify-between"><span class="opacity-70">Detected</span><span class="font-mono text-xs">{{ $nc->detected_date?->format('d M Y') }}</span></div>
                    <div class="flex items-center justify-between"><span class="opacity-70">Due</span><span class="font-mono text-xs">{{ $nc->due_date?->format('d M Y') ?? '—' }}</span></div>
                    <div class="flex items-center justify-between"><span class="opacity-70">Process</span><span>{{ $nc->process_area ?? '—' }}</span></div>
                    <div class="flex items-center justify-between"><span class="opacity-70">Owner</span><span>{{ $nc->owner ? trim($nc->owner->surname . ', ' . $nc->owner->name) : 'Unassigned' }}</span></div>
                    <div class="flex items-center justify-between"><span class="opacity-70">Recorded by</span><span>{{ $nc->creator ? trim($nc->creator->surname . ', ' . $nc->creator->name) : '—' }}</span></div>
                </div>
            </div>

            @if ($canManage && ! $nc->is_closed)
                <div class="card bg-base-100 shadow">
                    <div class="card-body space-y-3">
                        <h2 class="font-bold">Update the finding</h2>
                        <form method="POST" action="{{ route('non_conformities.update', $nc) }}" class="space-y-2" id="nc-update-form">
                            @csrf @method('PUT')

                            <div class="form-control">
                                <label class="label py-1" for="containment"><span class="label-text text-xs">Containment</span></label>
                                <textarea name="containment" id="containment" rows="2" class="textarea textarea-bordered textarea-xs">{{ $nc->containment }}</textarea>
                            </div>

                            <div class="form-control">
                                <label class="label py-1" for="root_cause"><span class="label-text text-xs">Root cause</span></label>
                                <textarea name="root_cause" id="root_cause" rows="2" class="textarea textarea-bordered textarea-xs">{{ $nc->root_cause }}</textarea>
                            </div>

                            <div class="form-control">
                                <label class="label py-1" for="status"><span class="label-text text-xs">Status</span></label>
                                <select name="status" id="status" class="select select-bordered select-xs">
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}" @selected($nc->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-control">
                                <label class="label py-1" for="due_date"><span class="label-text text-xs">Due date</span></label>
                                <input type="date" name="due_date" id="due_date" value="{{ $nc->due_date?->toDateString() }}" class="input input-bordered input-xs">
                            </div>

                            <div class="form-control">
                                <label class="label py-1" for="severity"><span class="label-text text-xs">Severity</span></label>
                                <select name="severity" id="severity" class="select select-bordered select-xs">
                                    @foreach (\App\Models\NonConformity::SEVERITIES as $value => $label)
                                        <option value="{{ $value }}" @selected($nc->severity === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-xs btn-primary w-full" id="nc-update">Save changes</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
