@extends('layouts.app')

@section('title', 'Internal audits')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    @if (session('success'))
        <div class="alert alert-success" id="flash-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error" id="flash-error">{{ session('error') }}</div>
    @endif

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">📋</span>
                <div>
                    <h1 class="text-2xl font-extrabold">Internal audits</h1>
                    <p class="opacity-70 text-sm mt-0.5">What we planned to check, what we found, and what it turned into.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0">
                    <span class="font-bold" id="audit-count">{{ number_format($counts['all']) }}</span>
                    {{ $counts['all'] === 1 ? 'audit' : 'audits' }}
                </span>
                @if ($canPlan)
                    <a href="{{ route('internal_audits.create') }}" class="btn btn-sm btn-secondary shadow-md" id="audit-new">➕ Plan an audit</a>
                @endif
            </div>
        </div>
    </div>

    <div class="tabs tabs-boxed bg-base-100 shadow w-fit flex-wrap" id="audit-tabs">
        <a href="{{ route('internal_audits.index', array_filter(['q' => $filters['q'] ?? null])) }}"
           class="tab {{ empty($filters['status']) ? 'tab-active font-semibold' : '' }}" id="tab-all">
            All <span class="badge badge-sm ml-1 badge-ghost">{{ $counts['all'] }}</span>
        </a>
        @foreach ($statuses as $value => $label)
            <a href="{{ route('internal_audits.index', array_filter(['status' => $value, 'q' => $filters['q'] ?? null])) }}"
               class="tab {{ ($filters['status'] ?? '') === $value ? 'tab-active font-semibold' : '' }}"
               id="tab-{{ $value }}" data-count="{{ $counts[$value] ?? 0 }}">
                {{ $label }} <span class="badge badge-sm ml-1 badge-ghost">{{ $counts[$value] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('internal_audits.index') }}" class="card bg-base-100 shadow">
        <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
        <div class="card-body py-4 px-5 flex flex-col sm:flex-row gap-3 sm:items-end">
            <div class="form-control flex-1">
                <label class="label py-1" for="filter-q"><span class="label-text text-xs font-semibold">Ref, title or scope</span></label>
                <input type="text" name="q" id="filter-q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="e.g. AUD-001 or payroll" class="input input-bordered input-sm w-full">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary" id="filter-apply">Filter</button>
                <a href="{{ route('internal_audits.index') }}" class="btn btn-sm btn-ghost" id="filter-reset">Reset</a>
            </div>
        </div>
    </form>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-sm" id="audit-table">
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <th>Audit</th>
                            <th>Scheduled</th>
                            <th>Lead auditor</th>
                            <th>Findings</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($audits as $audit)
                            <tr id="audit-row-{{ $audit->id }}">
                                <td class="font-mono text-xs font-semibold">{{ $audit->ref_no }}</td>
                                <td>
                                    <a href="{{ route('internal_audits.show', $audit) }}" class="link link-hover font-medium">
                                        {{ $audit->title }}
                                    </a>
                                    @if ($audit->scope)
                                        <div class="text-xs opacity-60">{{ Str::limit($audit->scope, 70) }}</div>
                                    @endif
                                </td>
                                <td class="text-xs whitespace-nowrap">
                                    {{ $audit->scheduled_date?->format('d M Y') ?? '—' }}
                                </td>
                                <td class="text-xs">
                                    {{ $audit->leadAuditor ? trim($audit->leadAuditor->surname . ', ' . $audit->leadAuditor->name) : '—' }}
                                </td>
                                <td>
                                    <span class="badge badge-sm badge-ghost" id="audit-findings-{{ $audit->id }}">{{ $audit->findings_count }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-sm {{ $audit->status_badge }}" id="audit-status-{{ $audit->id }}">{{ $audit->status_label }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 opacity-60" id="audit-empty">
                                    No audits yet. Plan one to start.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>{{ $audits->links() }}</div>
</div>
@endsection
