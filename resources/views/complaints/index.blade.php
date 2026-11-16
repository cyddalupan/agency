@extends('layouts.app')

@section('title', 'Complaints')

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
                <span class="text-3xl">📣</span>
                <div>
                    <h1 class="text-2xl font-extrabold">Complaints &amp; Grievances</h1>
                    <p class="opacity-70 text-sm mt-0.5">Logged to a service-level deadline and tracked until the complainant is answered.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0">
                    <span class="font-bold" id="complaint-count">{{ number_format($counts['all']) }}</span>
                    {{ $counts['all'] === 1 ? 'complaint' : 'complaints' }}
                </span>
                @if (in_array((string) auth()->user()->user_type, ['super_admin', 'admin', 'qms_manager'], true))
                    <a href="{{ route('complaints.create') }}" class="btn btn-sm btn-secondary shadow-md" id="complaint-new">➕ Log Complaint</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Status tabs. Overdue is a live view, not a stored status. --}}
    <div class="tabs tabs-boxed bg-base-100 shadow w-fit flex-wrap" id="complaint-tabs">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('complaints.index', array_filter(['status' => $key, 'q' => $filters['q'] ?? null])) }}"
               class="tab {{ $key === $tab ? 'tab-active font-semibold' : '' }} {{ $key === 'overdue' && $counts['overdue'] > 0 ? 'text-error' : '' }}"
               id="tab-{{ $key }}"
               data-tab="{{ $key }}"
               data-count="{{ $counts[$key] ?? 0 }}">
                {{ $label }}
                <span class="badge badge-sm ml-1 {{ $key === 'overdue' && $counts['overdue'] > 0 ? 'badge-error' : 'badge-ghost' }}">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('complaints.index') }}" class="card bg-base-100 shadow">
        <input type="hidden" name="status" value="{{ $tab }}">
        <div class="card-body py-4 px-5 flex flex-col sm:flex-row gap-3 sm:items-end">
            <div class="form-control flex-1">
                <label class="label py-1" for="filter-q"><span class="label-text text-xs font-semibold">Ref, complainant or description</span></label>
                <input type="text" name="q" id="filter-q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="e.g. CMP-001 or late payroll" class="input input-bordered input-sm w-full">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary" id="filter-apply">Search</button>
                <a href="{{ route('complaints.index', ['status' => $tab]) }}" class="btn btn-sm btn-ghost" id="filter-reset">Reset</a>
            </div>
        </div>
    </form>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-sm" id="complaint-table">
                    <thead>
                        <tr class="text-xs uppercase tracking-wide">
                            <th>Ref</th>
                            <th>Complainant</th>
                            <th>Received</th>
                            <th>Ack by</th>
                            <th>Resolve by</th>
                            <th>Assigned</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($complaints as $complaint)
                            <tr class="{{ $complaint->is_ack_overdue || $complaint->is_resolve_overdue ? 'bg-error/5' : '' }}">
                                <td class="font-mono font-semibold">
                                    <a href="{{ route('complaints.show', $complaint) }}" class="link link-hover">{{ $complaint->ref_no }}</a>
                                </td>
                                <td>
                                    <div class="font-medium">{{ $complaint->complainant_name }}</div>
                                    <div class="text-xs opacity-60">{{ \App\Models\Complaint::COMPLAINANT_TYPES[$complaint->complainant_type] ?? $complaint->complainant_type }}</div>
                                </td>
                                <td class="text-xs">{{ $complaint->date_received?->format('d M Y') }}</td>
                                <td class="text-xs">
                                    @if ($complaint->target_ack_date)
                                        <span class="{{ $complaint->is_ack_overdue ? 'text-error font-semibold' : '' }}">
                                            {{ $complaint->target_ack_date->format('d M Y') }}
                                        </span>
                                        @if ($complaint->is_ack_overdue)
                                            <span class="badge badge-xs badge-error ml-1" id="ack-overdue-{{ $complaint->id }}">overdue</span>
                                        @endif
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                                <td class="text-xs">
                                    @if ($complaint->target_resolve_date)
                                        <span class="{{ $complaint->is_resolve_overdue ? 'text-error font-semibold' : '' }}">
                                            {{ $complaint->target_resolve_date->format('d M Y') }}
                                        </span>
                                        @if ($complaint->is_resolve_overdue)
                                            <span class="badge badge-xs badge-error ml-1" id="resolve-overdue-{{ $complaint->id }}">overdue</span>
                                        @endif
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                                <td class="text-xs">
                                    {{ $complaint->assignee ? trim($complaint->assignee->surname . ', ' . $complaint->assignee->name) : '—' }}
                                </td>
                                <td><span class="badge badge-sm {{ $complaint->status_badge }}">{{ $complaint->status_label }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-xs btn-ghost" id="complaint-open-{{ $complaint->id }}">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10" id="complaint-empty">
                                    <div class="text-3xl mb-2">📣</div>
                                    <p class="font-medium">No complaints in this view.</p>
                                    <p class="text-sm opacity-60">A quiet register is a good sign, but log anything that arrives.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($complaints->hasPages())
        <div class="flex justify-center">{{ $complaints->links() }}</div>
    @endif
</div>
@endsection
