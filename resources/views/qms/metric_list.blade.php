@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="p-4 sm:p-6 space-y-6 max-w-5xl">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}" class="btn btn-sm btn-ghost" id="back-to-dashboard">← Dashboard</a>
            <h1 class="text-2xl font-extrabold">{{ $title }}</h1>
        </div>
        <span class="badge badge-lg badge-primary gap-2 py-4" id="metric-count-badge">
            <span class="font-bold" id="metric-count">{{ number_format($count) }}</span>
            {{ $count === 1 ? 'record' : 'records' }}
        </span>
    </div>

    <div class="text-sm opacity-70" id="metric-period">{{ $hint }} · {{ $period }}</div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-sm" id="metric-table">
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <th>What</th>
                            <th>Due</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr data-row="{{ $loop->index }}">
                                <td class="font-mono text-xs">{{ $row['ref'] }}</td>
                                <td>
                                    <a href="{{ $row['url'] }}" class="link link-hover text-sm">{{ Str::limit($row['title'], 90) }}</a>
                                    <div class="text-xs opacity-60">{{ $row['meta'] }}</div>
                                </td>
                                <td class="font-mono text-xs whitespace-nowrap">{{ $row['due']?->format('d M Y') ?? '—' }}</td>
                                <td class="text-xs opacity-60">{{ $row['due'] ? $row['due']->diffForHumans() : '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-10 opacity-60" id="metric-empty">
                                    Nothing here. The tile should read 0.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>{{ $paginator->links() }}</div>
</div>
@endsection
