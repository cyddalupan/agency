@extends('layouts.app')

@section('title', 'Non-conformities')

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
                <span class="text-3xl">🛠️</span>
                <div>
                    <h1 class="text-2xl font-extrabold">Non-conformities &amp; CAPA</h1>
                    <p class="opacity-70 text-sm mt-0.5">Every finding closed only after the fix is verified <em>and</em> shown to work.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0">
                    <span class="font-bold" id="nc-count">{{ number_format($counts['all']) }}</span>
                    {{ $counts['all'] === 1 ? 'finding' : 'findings' }}
                </span>
                @if (in_array((string) auth()->user()->user_type, ['super_admin', 'admin', 'qms_manager'], true))
                    <a href="{{ route('non_conformities.create') }}" class="btn btn-sm btn-secondary shadow-md" id="nc-new">➕ Record finding</a>
                @endif
            </div>
        </div>
    </div>

    <div class="tabs tabs-boxed bg-base-100 shadow w-fit flex-wrap" id="nc-tabs">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('non_conformities.index', array_filter(['tab' => $key, 'q' => $filters['q'] ?? null, 'severity' => $filters['severity'] ?? null])) }}"
               class="tab {{ $key === $tab ? 'tab-active font-semibold' : '' }} {{ $key === 'overdue' && $counts['overdue'] > 0 ? 'text-error' : '' }}"
               id="tab-{{ $key }}" data-tab="{{ $key }}" data-count="{{ $counts[$key] ?? 0 }}">
                {{ $label }}
                <span class="badge badge-sm ml-1 {{ $key === 'overdue' && $counts['overdue'] > 0 ? 'badge-error' : 'badge-ghost' }}">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('non_conformities.index') }}" class="card bg-base-100 shadow">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="card-body py-4 px-5 flex flex-col sm:flex-row gap-3 sm:items-end">
            <div class="form-control flex-1">
                <label class="label py-1" for="filter-q"><span class="label-text text-xs font-semibold">Ref, description or process</span></label>
                <input type="text" name="q" id="filter-q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="e.g. NC-001 or payroll" class="input input-bordered input-sm w-full">
            </div>
            <div class="form-control">
                <label class="label py-1" for="filter-severity"><span class="label-text text-xs font-semibold">Severity</span></label>
                <select name="severity" id="filter-severity" class="select select-bordered select-sm">
                    <option value="">All severities</option>
                    @foreach ($severities as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['severity'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary" id="filter-apply">Filter</button>
                <a href="{{ route('non_conformities.index', ['tab' => $tab]) }}" class="btn btn-sm btn-ghost" id="filter-reset">Reset</a>
            </div>
        </div>
    </form>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-sm" id="nc-table">
                    <thead>
                        <tr class="text-xs uppercase tracking-wide">
                            <th>Ref</th>
                            <th>Finding</th>
                            <th>Source</th>
                            <th>Detected</th>
                            <th>Due</th>
                            <th class="text-center">Actions</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($nonConformities as $nc)
                            <tr class="{{ $nc->is_overdue ? 'bg-error/5' : '' }}">
                                <td class="font-mono font-semibold">
                                    <a href="{{ route('non_conformities.show', $nc) }}" class="link link-hover">{{ $nc->ref_no }}</a>
                                </td>
                                <td>
                                    <div class="font-medium">{{ \Illuminate\Support\Str::limit($nc->description, 80) }}</div>
                                    @if ($nc->process_area)
                                        <div class="text-xs opacity-60">{{ $nc->process_area }}</div>
                                    @endif
                                </td>
                                <td class="text-xs">{{ $nc->source_label }}</td>
                                <td class="text-xs">{{ $nc->detected_date?->format('d M Y') }}</td>
                                <td class="text-xs">
                                    @if ($nc->due_date)
                                        <span class="{{ $nc->is_overdue ? 'text-error font-semibold' : '' }}">{{ $nc->due_date->format('d M Y') }}</span>
                                        @if ($nc->is_overdue)
                                            <span class="badge badge-xs badge-error ml-1" id="nc-overdue-{{ $nc->id }}">overdue</span>
                                        @endif
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                                <td class="text-center font-mono">{{ $nc->corrective_actions_count }}</td>
                                <td><span class="badge badge-sm {{ $nc->severity_badge }}">{{ $nc->severity_label }}</span></td>
                                <td><span class="badge badge-sm {{ $nc->status_badge }}">{{ $nc->status_label }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('non_conformities.show', $nc) }}" class="btn btn-xs btn-ghost" id="nc-open-{{ $nc->id }}">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10" id="nc-empty">
                                    <div class="text-3xl mb-2">🛠️</div>
                                    <p class="font-medium">No non-conformities in this view.</p>
                                    <p class="text-sm opacity-60">Record anything found by an audit, a complaint or by someone noticing.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($nonConformities->hasPages())
        <div class="flex justify-center">{{ $nonConformities->links() }}</div>
    @endif
</div>
@endsection
