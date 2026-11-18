@extends('layouts.app')

@section('title', 'Audit Trail')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🧾</span>
                <div>
                    <h1 class="text-2xl font-extrabold">Audit Trail</h1>
                    <p class="opacity-70 text-sm mt-0.5">
                        Who changed what, and when. Read-only — entries cannot be edited or deleted.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0">
                    <span class="font-bold" id="audit-count">{{ number_format($filteredCount) }}</span>
                    {{ $activeFilterCnt > 0 ? 'matching' : 'events' }}
                </span>
                @if ($activeFilterCnt > 0)
                    <span class="badge badge-ghost py-4">of {{ number_format($totalCount) }}</span>
                @endif
                {{-- QMS-04: export carries the active filters through as query string --}}
                <a href="{{ route('audit_trail.export', array_filter($filters)) }}"
                   class="btn btn-sm btn-secondary shadow-md" id="audit-export">
                    ⬇️ Export CSV
                </a>
            </div>
        </div>
    </div>

    {{-- Cross-link between the two audit spines. audit_events holds record
         changes; activity_logs holds sensitive actions (card 6afdc2b9). --}}
    <div role="tablist" class="tabs tabs-boxed w-fit" id="audit-tabs">
        <a role="tab" href="{{ route('audit_trail.index') }}" class="tab tab-active">Record changes</a>
        <a role="tab" href="{{ route('audit_trail.sensitive') }}" class="tab">Sensitive actions</a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('audit_trail.index') }}" id="audit-filters" class="card bg-base-100 shadow-md">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
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
                        <option value="none" disabled>— system (no actor) —</option>
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
                    <span class="label-text text-xs font-semibold mb-1">Record type</span>
                    <select name="subject_type" class="select select-bordered select-sm">
                        <option value="">All records</option>
                        @foreach ($subjectTypes as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['subject_type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="form-control">
                    <span class="label-text text-xs font-semibold mb-1">Search label</span>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="e.g. 000668"
                           class="input input-bordered input-sm">
                </label>
            </div>
            <div class="flex items-center gap-2 mt-3">
                <button type="submit" class="btn btn-sm btn-primary">Apply filters</button>
                <a href="{{ route('audit_trail.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            </div>
        </div>
    </form>

    {{-- Events --}}
    <div class="card bg-base-100 shadow-md">
        <div class="card-body p-0">
            @if ($events->isEmpty())
                <div class="p-10 text-center opacity-60">
                    <p class="text-4xl mb-2">🗂️</p>
                    <p class="font-semibold">No audit events match these filters.</p>
                    @if ($totalCount === 0)
                        <p class="text-sm mt-1">The trail is empty — events appear once records are created or changed.</p>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-sm" id="audit-table">
                        <thead>
                            <tr>
                                <th class="w-10"></th>
                                <th>When</th>
                                <th>Actor</th>
                                <th>Action</th>
                                <th>Record</th>
                                <th>Reference</th>
                                <th>Fields changed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                @php
                                    // A field changed when it is present on either side
                                    // with a different value. array_diff_key alone misses a
                                    // key present in both, which is the common case (status).
                                    $keys = array_unique(array_merge(
                                        array_keys($event->before ?? []),
                                        array_keys($event->after ?? [])
                                    ));
                                    $changed = array_values(array_filter($keys, fn ($k)
                                        => ($event->before[$k] ?? null) !== ($event->after[$k] ?? null)));
                                    sort($changed);
                                @endphp
                                <tr>
                                    <td>
                                        {{-- Native disclosure: no JS needed, expands a readable diff. --}}
                                        <details class="inline-block">
                                            <summary class="cursor-pointer list-none text-primary font-bold" title="Show details">+</summary>
                                            <div class="mt-2 w-full"></div>
                                        </details>
                                    </td>
                                    <td class="whitespace-nowrap text-xs">
                                        {{ $event->created_at?->format('M d, Y H:i:s') ?? '—' }}
                                    </td>
                                    <td class="text-xs">
                                        {{ $event->actor?->surname && $event->actor?->name
                                            ? trim($event->actor->surname . ', ' . $event->actor->name)
                                            : '— system —' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-sm {{ match($event->action) {
                                            'created' => 'badge-success',
                                            'updated' => 'badge-info',
                                            'approved' => 'badge-success',
                                            'rejected' => 'badge-error',
                                            'deleted' => 'badge-error',
                                            'archived' => 'badge-ghost',
                                            default => 'badge-ghost',
                                        } }}">{{ $event->action_label }}</span>
                                    </td>
                                    <td class="text-xs">{{ class_basename($event->subject_type) }}</td>
                                    <td class="text-xs font-mono">{{ $event->subject_label ?? ('#' . $event->subject_id) }}</td>
                                    <td class="text-xs">
                                        @if (count($changed) === 0)
                                            <span class="opacity-50">—</span>
                                        @else
                                            <span class="font-mono">{{ implode(', ', $changed) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-t border-base-300">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Readable diffs, one card per event, rendered only for expanded rows.
         Kept as a separate section so the table stays scannable. --}}
    @if ($events->isNotEmpty())
        <div class="space-y-3">
            <h2 class="font-bold text-sm opacity-70 uppercase tracking-wide">Change detail</h2>
            @foreach ($events as $event)
                @php
                    $keys = array_unique(array_merge(array_keys($event->before ?? []), array_keys($event->after ?? [])));
                    sort($keys);
                @endphp
                <details class="card bg-base-100 shadow-sm" id="event-{{ $event->id }}">
                    <summary class="card-body p-3 cursor-pointer flex flex-row items-center gap-3 text-sm">
                        <span class="badge badge-sm badge-outline">{{ $event->action_label }}</span>
                        <span class="font-mono text-xs">{{ class_basename($event->subject_type) }} {{ $event->subject_label ?? ('#' . $event->subject_id) }}</span>
                        <span class="opacity-60 text-xs ml-auto">{{ $event->created_at?->format('M d, Y H:i:s') }}</span>
                    </summary>
                    <div class="card-body p-3 pt-0">
                        @if (count($keys) === 0)
                            <p class="text-xs opacity-60">No field values recorded.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="table table-xs">
                                    <thead>
                                        <tr><th>Field</th><th>Before</th><th>After</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($keys as $key)
                                            @php
                                                $b = ($event->before ?? [])[$key] ?? null;
                                                $a = ($event->after  ?? [])[$key] ?? null;
                                                $fmt = fn ($v) => is_bool($v) ? ($v ? 'true' : 'false')
                                                    : (is_array($v) ? json_encode($v) : (is_null($v) ? '—' : (string) $v));
                                            @endphp
                                            <tr>
                                                <td class="font-mono text-xs">{{ $key }}</td>
                                                <td class="text-xs {{ $b === null ? 'opacity-40' : '' }}">{{ $fmt($b) }}</td>
                                                <td class="text-xs {{ $a === null ? 'opacity-40' : '' }}">{{ $fmt($a) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <p class="text-[11px] opacity-50 mt-2">
                            Event #{{ $event->id }}
                            @if ($event->ip_address) · IP {{ $event->ip_address }} @endif
                            @if ($event->user_agent) · {{ \Illuminate\Support\Str::limit($event->user_agent, 60) }} @endif
                        </p>
                    </div>
                </details>
            @endforeach
        </div>
    @endif

</div>
@endsection
