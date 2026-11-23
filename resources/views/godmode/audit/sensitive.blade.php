@extends('godmode.layouts.app')

@section('title', 'Sensitive actions')

@php
    $exportUrl = route('godmode.audit.export', array_filter([
        'tab'          => 'sensitive',
        'from'         => $filters['from'],
        'to'           => $filters['to'],
        'actor'        => $filters['actor'],
        'agency'       => $filters['agency'],
        'action'       => $filters['action'],
        'q'            => $filters['q'] ?: null,
    ], fn ($v) => $v !== null && $v !== ''));
@endphp

@section('content')
<div class="flex items-start justify-between gap-3 flex-wrap">
    <div>
        <h1 class="text-2xl font-extrabold">Sensitive actions</h1>
        <p class="text-sm opacity-70">
            The activity log across every agency: logins, role changes, exports and status
            history. Its own table and its own columns — not the audit-event diff.
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('godmode.audit.index', request()->only(['from', 'to', 'actor', 'agency', 'action', 'per'])) }}"
           class="btn btn-sm btn-ghost" id="audit-tab-trail">Audit trail</a>
        <a href="{{ $exportUrl }}" class="btn btn-sm btn-neutral" id="audit-export">Export CSV</a>
    </div>
</div>

<div class="flex items-center gap-3 flex-wrap text-sm">
    <span class="badge badge-lg" data-audit-matching="{{ $matching }}" id="audit-matching">{{ number_format($matching) }} matching</span>
    <span class="opacity-60">of {{ number_format($total) }} activity rows</span>
</div>

@include('godmode.audit._filters', ['sensitive' => true])

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-sm" id="audit-sensitive-table">
        <thead>
            <tr>
                <th>When</th>
                <th>Actor</th>
                <th>Action</th>
                <th>Description</th>
                <th>Record</th>
                <th>Agency</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $log)
                <tr data-audit-row="{{ $log->id }}" data-agency="{{ $log->agency_id === null ? $agencyNoneValue : $log->agency_id }}">
                    <td class="whitespace-nowrap">{{ $log->created_at?->format('Y-m-d H:i:s') ?? '—' }}</td>
                    <td data-actor="{{ $log->user_id === null ? $actorNoneValue : $log->user_id }}">
                        {{ $audit->actorName($log->user) }}
                    </td>
                    <td class="font-mono text-xs" data-action="{{ $log->action }}">{{ $log->action }}</td>
                    <td class="text-xs">{{ \Illuminate\Support\Str::limit((string) $log->description, 160) }}</td>
                    <td>{{ $audit->subjectLabel($log->subject_type) }}</td>
                    <td data-agency-label>{{ $audit->agencyName($log->agency_id) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-8 opacity-60" id="audit-empty">No activity rows match these filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $rows->links() }}</div>
</div>
@endsection
