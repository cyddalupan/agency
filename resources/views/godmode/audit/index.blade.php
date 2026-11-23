@extends('godmode.layouts.app')

@section('title', 'Audit trail')

@php
    $exportUrl = route('godmode.audit.export', array_filter([
        'from'         => $filters['from'],
        'to'           => $filters['to'],
        'actor'        => $filters['actor'],
        'agency'       => $filters['agency'],
        'action'       => $filters['action'],
        'subject_type' => $filters['subject_type'],
        'q'            => $filters['q'] ?: null,
    ], fn ($v) => $v !== null && $v !== ''));
@endphp

@section('content')
<div class="flex items-start justify-between gap-3 flex-wrap">
    <div>
        <h1 class="text-2xl font-extrabold">Audit trail</h1>
        <p class="text-sm opacity-70">
            Every recorded change across every agency, read-only. Rows with no agency are the
            control plane's own: god logins, and the trail of an agency that was hard-deleted.
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('godmode.audit.sensitive', request()->only(['from', 'to', 'actor', 'agency', 'action', 'subject_type', 'q', 'per'])) }}"
           class="btn btn-sm btn-ghost" id="audit-tab-sensitive">Sensitive actions</a>
        <a href="{{ $exportUrl }}" class="btn btn-sm btn-neutral" id="audit-export">Export CSV</a>
    </div>
</div>

<div class="flex items-center gap-3 flex-wrap text-sm">
    <span class="badge badge-lg" data-audit-matching="{{ $matching }}" id="audit-matching">{{ number_format($matching) }} matching</span>
    <span class="opacity-60">of {{ number_format($total) }} events</span>
    <a href="{{ route('godmode.audit.index', ['agency' => $agencyNoneValue] + array_filter(['from' => $filters['from'], 'to' => $filters['to'], 'per' => $filters['per'] !== 25 ? $filters['per'] : null])) }}"
       class="badge badge-outline badge-lg" data-audit-no-agency="{{ $noAgency }}" id="audit-no-agency">
        {{ number_format($noAgency) }} with no agency
    </a>
</div>

@include('godmode.audit._filters', ['sensitive' => false])

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-sm" id="audit-table">
        <thead>
            <tr>
                <th>When</th>
                <th>Actor</th>
                <th>Action</th>
                <th>Record</th>
                <th>Reference</th>
                <th>Agency</th>
                <th>Changed</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $event)
                @php
                    $before = $event->before ?? [];
                    $after = $event->after ?? [];
                    $changed = [];
                    foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $key) {
                        if (($before[$key] ?? null) !== ($after[$key] ?? null)) {
                            $changed[] = $key;
                        }
                    }
                    sort($changed);
                    $isDelete = $audit->isDeletedAgencyEvent($event);
                @endphp
                <tr data-audit-row="{{ $event->id }}" data-agency="{{ $event->agency_id === null ? $agencyNoneValue : $event->agency_id }}">
                    <td class="whitespace-nowrap">{{ $event->created_at?->format('Y-m-d H:i:s') ?? '—' }}</td>
                    <td data-actor="{{ $event->actor_user_id === null ? $actorNoneValue : $event->actor_user_id }}">
                        {{ $audit->actorName($event->actor) }}
                    </td>
                    <td data-action="{{ $event->action }}">{{ $event->action_label }}</td>
                    <td data-subject-type="{{ $event->subject_type }}">{{ $audit->subjectLabel($event->subject_type) }}</td>
                    <td class="font-mono text-xs">{{ $event->subject_label ?? '—' }}</td>
                    <td data-agency-label>{{ $audit->agencyName($event->agency_id) }}</td>
                    <td class="text-xs">{{ $changed === [] ? '—' : implode(', ', $changed) }}</td>
                    <td class="text-right">
                        <details>
                            <summary class="btn btn-xs btn-ghost">+</summary>
                            <div class="p-3 text-xs space-y-2 min-w-[22rem]">
                                @if ($isDelete)
                                    <p class="opacity-80" data-deleted-agency-note>{{ \App\Services\GodMode\GodAuditService::DELETED_AGENCY_NOTE }}</p>
                                @endif
                                <p><strong>User agent:</strong> {{ $event->user_agent ?? '—' }}</p>
                                <p><strong>IP:</strong> {{ $event->ip_address ?? '—' }}</p>
                                <p><strong>Record ID:</strong> {{ $event->subject_id }}</p>
                                <p><strong>Before:</strong> <code>{{ $before === [] ? '—' : json_encode($before, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></p>
                                <p><strong>After:</strong> <code>{{ $after === [] ? '—' : json_encode($after, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></p>
                            </div>
                        </details>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-8 opacity-60" id="audit-empty">No events match these filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $rows->links() }}</div>
</div>
@endsection
