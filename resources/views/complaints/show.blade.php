@extends('layouts.app')

@section('title', $complaint->ref_no)

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
        <a href="{{ route('complaints.index') }}" class="btn btn-sm btn-ghost">← Register</a>
        <h1 class="text-2xl font-extrabold font-mono">{{ $complaint->ref_no }}</h1>
        <span class="badge badge-lg {{ $complaint->status_badge }}" id="complaint-status">{{ $complaint->status_label }}</span>
        @if ($complaint->is_ack_overdue)
            <span class="badge badge-lg badge-error gap-1" id="badge-ack-overdue">⏰ acknowledgement overdue</span>
        @endif
        @if ($complaint->is_resolve_overdue)
            <span class="badge badge-lg badge-error gap-1" id="badge-resolve-overdue">⏰ resolution overdue</span>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: the complaint itself --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-4">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <div>
                            <div class="text-xs uppercase tracking-wide opacity-60">Complainant</div>
                            <div class="text-lg font-bold" id="complaint-complainant">{{ $complaint->complainant_name }}</div>
                            <div class="text-sm opacity-70">
                                {{ \App\Models\Complaint::COMPLAINANT_TYPES[$complaint->complainant_type] ?? $complaint->complainant_type }}
                                @if ($complaint->category) · {{ $complaint->category }} @endif
                            </div>
                        </div>
                        <div class="text-right text-sm">
                            <div class="text-xs uppercase tracking-wide opacity-60">Received</div>
                            <div class="font-semibold">{{ $complaint->date_received?->format('d M Y') }}</div>
                        </div>
                    </div>

                    <div class="divider my-0"></div>

                    <div>
                        <div class="text-xs uppercase tracking-wide opacity-60 mb-1">What happened</div>
                        <p class="whitespace-pre-line text-sm" id="complaint-description">{{ $complaint->description }}</p>
                    </div>

                    @if ($complaint->resolution)
                        <div class="divider my-0"></div>
                        <div>
                            <div class="text-xs uppercase tracking-wide opacity-60 mb-1">Outcome</div>
                            <p class="whitespace-pre-line text-sm" id="complaint-resolution">{{ $complaint->resolution }}</p>
                        </div>
                    @endif

                    @if ($complaint->closure_notes)
                        <div class="divider my-0"></div>
                        <div>
                            <div class="text-xs uppercase tracking-wide opacity-60 mb-1">Closure notes</div>
                            <p class="whitespace-pre-line text-sm" id="complaint-closure">{{ $complaint->closure_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions. Only rendered for manage roles; the auditor reads. --}}
            @if ($canManage)
                <div class="card bg-base-100 shadow">
                    <div class="card-body space-y-4">
                        <h2 class="font-bold text-lg">Handle this complaint</h2>

                        @if ($complaint->status === \App\Models\Complaint::STATUS_OPEN)
                            <form method="POST" action="{{ route('complaints.acknowledge', $complaint) }}" class="flex items-center gap-3 flex-wrap" id="ack-form">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning" id="btn-acknowledge">✋ Acknowledge</button>
                                <span class="text-xs opacity-60">Record that the complainant has been told we have it.</span>
                            </form>
                        @endif

                        @if (in_array($complaint->status, [\App\Models\Complaint::STATUS_OPEN, \App\Models\Complaint::STATUS_ACKNOWLEDGED], true))
                            <form method="POST" action="{{ route('complaints.investigate', $complaint) }}" class="flex items-center gap-3 flex-wrap" id="investigate-form">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-info" id="btn-investigate">🔍 Investigating</button>
                                <span class="text-xs opacity-60">Mark that someone is looking into it.</span>
                            </form>
                        @endif

                        @if (in_array($complaint->status, [
                            \App\Models\Complaint::STATUS_OPEN,
                            \App\Models\Complaint::STATUS_ACKNOWLEDGED,
                            \App\Models\Complaint::STATUS_INVESTIGATING,
                        ], true))
                            <form method="POST" action="{{ route('complaints.resolve', $complaint) }}" class="space-y-2" id="resolve-form">
                                @csrf
                                <label class="label py-1" for="resolution">
                                    <span class="label-text font-semibold">Resolution</span>
                                </label>
                                <textarea name="resolution" id="resolution" rows="3" class="textarea textarea-bordered w-full"
                                          placeholder="What was done about it, and what the complainant was told." required></textarea>
                                <button type="submit" class="btn btn-sm btn-success" id="btn-resolve">✅ Resolve</button>
                            </form>
                        @endif

                        @if ($complaint->status === \App\Models\Complaint::STATUS_RESOLVED)
                            <form method="POST" action="{{ route('complaints.close', $complaint) }}" class="space-y-2" id="close-form">
                                @csrf
                                <label class="label py-1" for="closure_notes">
                                    <span class="label-text font-semibold">Closure notes</span>
                                </label>
                                <textarea name="closure_notes" id="closure_notes" rows="2" class="textarea textarea-bordered w-full"
                                          placeholder="Why this is closed — e.g. complainant confirmed the fix." required></textarea>
                                <button type="submit" class="btn btn-sm btn-outline" id="btn-close">🗄️ Close</button>
                            </form>
                        @endif

                        @if ($complaint->is_closed && ! $complaint->closure_notes)
                            <p class="text-sm opacity-60">This complaint is closed.</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert" id="read-only-notice">
                    <span class="text-lg">🔒</span>
                    <span class="text-sm">Read-only: your role can audit complaints but not handle them.</span>
                </div>
            @endif
        </div>

        {{-- Right: the clocks and who owns it --}}
        <div class="space-y-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-3">
                    <h2 class="font-bold">Deadlines</h2>

                    <div class="flex items-center justify-between">
                        <span class="text-sm opacity-70">Acknowledge by</span>
                        <span class="font-mono text-sm {{ $complaint->is_ack_overdue ? 'text-error font-bold' : '' }}" id="target-ack">
                            {{ $complaint->target_ack_date?->format('d M Y') ?? '—' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm opacity-70">Acknowledged</span>
                        <span class="font-mono text-sm" id="acknowledged-at">
                            {{ $complaint->acknowledged_at?->format('d M Y H:i') ?? 'not yet' }}
                        </span>
                    </div>

                    <div class="divider my-0"></div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm opacity-70">Resolve by</span>
                        <span class="font-mono text-sm {{ $complaint->is_resolve_overdue ? 'text-error font-bold' : '' }}" id="target-resolve">
                            {{ $complaint->target_resolve_date?->format('d M Y') ?? '—' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm opacity-70">Resolved</span>
                        <span class="font-mono text-sm" id="resolved-at">
                            {{ $complaint->resolved_at?->format('d M Y H:i') ?? 'not yet' }}
                        </span>
                    </div>

                    @if ($complaint->days_to_resolve !== null && ! $complaint->is_closed)
                        <div class="text-xs text-center pt-2 {{ $complaint->days_to_resolve < 0 ? 'text-error font-semibold' : 'opacity-60' }}" id="days-to-resolve">
                            @if ($complaint->days_to_resolve < 0)
                                {{ abs($complaint->days_to_resolve) }} day{{ abs($complaint->days_to_resolve) === 1 ? '' : 's' }} past the resolve deadline
                            @else
                                {{ $complaint->days_to_resolve }} day{{ $complaint->days_to_resolve === 1 ? '' : 's' }} left
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-2 text-sm">
                    <h2 class="font-bold mb-1">Ownership</h2>
                    <div class="flex items-center justify-between">
                        <span class="opacity-70">Assigned to</span>
                        <span id="assigned-to">{{ $complaint->assignee ? trim($complaint->assignee->surname . ', ' . $complaint->assignee->name) : 'Unassigned' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="opacity-70">Logged by</span>
                        <span>{{ $complaint->creator ? trim($complaint->creator->surname . ', ' . $complaint->creator->name) : '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="opacity-70">Logged</span>
                        <span class="font-mono text-xs">{{ $complaint->created_at?->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
