@extends('layouts.app')

@section('title', 'Sensitive Actions')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-warning via-warning/80 to-error text-warning-content shadow-lg card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🔐</span>
                <div>
                    <h1 class="text-2xl font-extrabold">Sensitive Actions</h1>
                    <p class="opacity-80 text-sm mt-0.5">
                        Logins, role changes, password resets and exports. Read-only — entries cannot be edited or deleted.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0">
                    <span class="font-bold" id="sensitive-count">{{ number_format($filteredCount) }}</span>
                    {{ $activeFilterCnt > 0 ? 'matching' : 'actions' }}
                </span>
                @if ($activeFilterCnt > 0)
                    <span class="badge badge-ghost py-4">of {{ number_format($totalCount) }}</span>
                @endif
                <a href="{{ route('audit_trail.sensitive.export', array_filter($filters)) }}"
                   class="btn btn-sm btn-secondary shadow-md" id="sensitive-export">
                    ⬇️ Export CSV
                </a>
            </div>
        </div>
    </div>

    {{-- Cross-link between the two audit spines. --}}
    <div role="tablist" class="tabs tabs-boxed w-fit">
        <a role="tab" href="{{ route('audit_trail.index') }}" class="tab">Record changes</a>
        <a role="tab" href="{{ route('audit_trail.sensitive') }}" class="tab tab-active">Sensitive actions</a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('audit_trail.sensitive') }}" id="sensitive-filters" class="card bg-base-100 shadow-md">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <label class="form-control">
                    <span class="label-text text-xs font-semibold mb-1">From</span>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="input input-bordered input-sm">
                </label>
                <label class="form-control">
                    <span class="label-text text-xs font-semibold mb-1">To</span>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="input input-bordered input-sm">
                </label>
                <label class="form-control">
                    <span class="label-text text-xs font-semibold mb-1">Actor</span>
                    <select name="actor" class="select select-bordered select-sm">
                        <option value="">All users</option>
                        @foreach ($actors as $id => $label)
                            <option value="{{ $id }}" @selected((string) ($filters['actor'] ?? '') === (string) $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="form-control">
                    <span class="label-text text-xs font-semibold mb-1">Action</span>
                    <select name="action" class="select select-bordered select-sm">
                        <option value="">All actions</option>
                        @foreach ($actions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['action'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="form-control">
                    <span class="label-text text-xs font-semibold mb-1">Search description</span>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="e.g. password"
                           class="input input-bordered input-sm">
                </label>
            </div>
            <div class="flex items-center gap-2 mt-3">
                <button type="submit" class="btn btn-sm btn-primary">Apply filters</button>
                <a href="{{ route('audit_trail.sensitive') }}" class="btn btn-sm btn-ghost">Reset</a>
            </div>
        </div>
    </form>

    {{-- Rows --}}
    <div class="card bg-base-100 shadow-md">
        <div class="card-body p-0">
            @if ($logs->isEmpty())
                <div class="p-10 text-center opacity-60">
                    <p class="text-4xl mb-2">🔐</p>
                    <p class="font-semibold">No sensitive actions match these filters.</p>
                    @if ($totalCount === 0)
                        <p class="text-sm mt-1">Nothing recorded yet — entries appear as people log in, change roles or reset passwords.</p>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-sm" id="sensitive-table">
                        <thead>
                            <tr>
                                <th class="w-10"></th>
                                <th>When</th>
                                <th>Actor</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Subject</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>
                                        {{-- Metadata is machine data, not a diff. Labelled as such. --}}
                                        <details class="inline-block">
                                            <summary class="cursor-pointer list-none text-primary font-bold" title="Show metadata">+</summary>
                                            <div class="mt-2 w-full"></div>
                                        </details>
                                    </td>
                                    <td class="whitespace-nowrap text-xs">
                                        {{ $log->created_at?->format('M d, Y H:i:s') ?? '—' }}
                                    </td>
                                    <td class="text-xs">
                                        {{ $log->user
                                            ? trim(implode(', ', array_filter([$log->user->surname, $log->user->name])))
                                            : '— system —' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-sm {{ match(true) {
                                            str_contains($log->action, 'failed')     => 'badge-error',
                                            str_contains($log->action, 'suspicious') => 'badge-error',
                                            str_contains($log->action, 'password')   => 'badge-warning',
                                            str_contains($log->action, 'role')       => 'badge-warning',
                                            str_contains($log->action, 'login')      => 'badge-info',
                                            default                                  => 'badge-ghost',
                                        } }}">{{ $log->action_label }}</span>
                                        <div class="text-[10px] opacity-40 font-mono mt-0.5">{{ $log->action }}</div>
                                    </td>
                                    <td class="text-xs">{{ $log->description ?? '—' }}</td>
                                    <td class="text-xs">
                                        @if ($log->subject_type)
                                            <span class="font-mono">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</span>
                                        @else
                                            <span class="opacity-50">—</span>
                                        @endif
                                    </td>
                                    <td class="text-xs font-mono">{{ $log->metadata['ip'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-t border-base-300">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Metadata detail, one card per row, only where there is some. --}}
    @if ($logs->isNotEmpty())
        <div class="space-y-3">
            <h2 class="font-bold text-sm opacity-70 uppercase tracking-wide">Metadata</h2>
            @foreach ($logs as $log)
                <details class="card bg-base-100 shadow-sm" id="log-{{ $log->id }}">
                    <summary class="card-body p-3 cursor-pointer flex flex-row items-center gap-3 text-sm">
                        <span class="badge badge-sm badge-outline">{{ $log->action_label }}</span>
                        <span class="font-mono text-xs">{{ $log->action }}</span>
                        <span class="opacity-60 text-xs ml-auto">{{ $log->created_at?->format('M d, Y H:i:s') }}</span>
                    </summary>
                    <div class="card-body p-3 pt-0">
                        @if (empty($log->metadata))
                            <p class="text-xs opacity-60">No metadata recorded.</p>
                        @else
                            <pre class="text-xs bg-base-200 rounded p-3 overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                        @endif
                    </div>
                </details>
            @endforeach
        </div>
    @endif
</div>
@endsection
