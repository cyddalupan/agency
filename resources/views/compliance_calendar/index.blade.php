@extends('layouts.app')

@section('title', 'Compliance calendar')

@section('content')
<div class="p-4 sm:p-6 space-y-6 max-w-5xl">

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">⏰</span>
                <div>
                    <h1 class="text-2xl font-extrabold">Compliance calendar</h1>
                    <p class="opacity-70 text-sm mt-0.5">Every deadline in one place, read from the records that own them.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0">
                    <span class="font-bold" id="deadline-total">{{ number_format($total) }}</span>
                    {{ $total === 1 ? 'deadline' : 'deadlines' }}
                </span>
                @if ($overdue > 0)
                    <span class="badge badge-lg badge-error gap-2 py-4 border-0" id="overdue-badge">
                        <span class="font-bold" id="overdue-count">{{ number_format($overdue) }}</span> overdue
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Bucket summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="bucket-cards">
        @foreach ($buckets as $key => $label)
            <a href="#bucket-{{ $key }}" class="card bg-base-100 shadow hover:shadow-md transition-shadow">
                <div class="card-body py-4 px-4">
                    <div class="text-xs uppercase font-semibold opacity-60">{{ $label }}</div>
                    <div class="text-2xl font-extrabold {{ $key === 'overdue' && $counts[$key] > 0 ? 'text-error' : '' }}"
                         id="bucket-count-{{ $key }}">{{ $counts[$key] }}</div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Source filter --}}
    @if ($sources->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2" id="source-filter">
            <span class="text-xs font-semibold opacity-60 mr-1">Source</span>
            <a href="{{ route('compliance_calendar.index') }}"
               class="btn btn-xs {{ $source === null ? 'btn-primary' : 'btn-ghost' }}" id="source-all">All</a>
            @foreach ($sources as $s)
                <a href="{{ route('compliance_calendar.index', ['source' => $s['key']]) }}"
                   class="btn btn-xs {{ $source === $s['key'] ? 'btn-primary' : 'btn-ghost' }}"
                   id="source-{{ $s['key'] }}">{{ $s['label'] }}</a>
            @endforeach
        </div>
    @endif

    @php($anyRendered = false)

    @foreach ($buckets as $key => $label)
        @if ($grouped[$key]->isNotEmpty())
            @php($anyRendered = true)
            <div class="card bg-base-100 shadow" id="bucket-{{ $key }}">
                <div class="card-body p-0">
                    <div class="px-5 py-3 border-b border-base-300 flex items-center gap-2">
                        <span class="badge badge-sm {{ $badges[$key] }}">{{ $label }}</span>
                        <span class="text-xs opacity-60" id="bucket-size-{{ $key }}">{{ $grouped[$key]->count() }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Due</th>
                                    <th>What</th>
                                    <th>Source</th>
                                    <th>Ref</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($grouped[$key] as $item)
                                    <tr id="deadline-{{ $loop->index }}-{{ $key }}"
                                        data-source="{{ $item['source'] }}"
                                        data-bucket="{{ $key }}">
                                        <td class="whitespace-nowrap">
                                            <div class="font-mono text-xs {{ $key === 'overdue' ? 'text-error font-bold' : '' }}">
                                                {{ $item['due']->format('d M Y') }}
                                            </div>
                                            <div class="text-[10px] opacity-60">
                                                @if ($item['days'] < 0)
                                                    {{ abs($item['days']) }}d late
                                                @elseif ($item['days'] === 0)
                                                    today
                                                @else
                                                    in {{ $item['days'] }}d
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ $item['url'] }}" class="link link-hover text-sm">
                                                {{ Str::limit($item['title'], 90) }}
                                            </a>
                                        </td>
                                        <td class="text-xs opacity-70 whitespace-nowrap">{{ $item['source_label'] }}</td>
                                        <td class="font-mono text-xs">{{ $item['ref'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    @unless ($anyRendered)
        <div class="card bg-base-100 shadow">
            <div class="card-body text-center py-12">
                <div class="text-4xl">✅</div>
                <p class="opacity-60 mt-2" id="deadlines-empty">
                    Nothing is due. That is either very good news or a sign the source records are empty.
                </p>
            </div>
        </div>
    @endunless
</div>
@endsection
