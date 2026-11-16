@extends('layouts.app')

@section('title', 'Risk Register')

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
                <span class="text-3xl">⚠️</span>
                <div>
                    <h1 class="text-2xl font-extrabold">Risk Register</h1>
                    <p class="opacity-70 text-sm mt-0.5">Likelihood × impact, owner, treatment and review dates.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0">
                    <span class="font-bold" id="risk-count">{{ number_format($risks->total()) }}</span>
                    {{ $risks->total() === 1 ? 'risk' : 'risks' }}
                </span>
                <a href="{{ route('risks.matrix') }}" class="btn btn-sm btn-ghost" id="risk-matrix-link">▦ Matrix</a>
                <a href="{{ route('risks.create') }}" class="btn btn-sm btn-secondary shadow-md" id="risk-new">➕ New Risk</a>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('risks.index') }}" class="card bg-base-100 shadow">
        <div class="card-body py-4 px-5 flex flex-col sm:flex-row gap-3 sm:items-end">
            <div class="form-control flex-1">
                <label class="label py-1" for="filter-q"><span class="label-text text-xs font-semibold">Ref, title or process</span></label>
                <input type="text" name="q" id="filter-q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="e.g. RISK-001 or payroll" class="input input-bordered input-sm w-full">
            </div>
            <div class="form-control">
                <label class="label py-1" for="filter-level"><span class="label-text text-xs font-semibold">Level</span></label>
                <select name="level" id="filter-level" class="select select-bordered select-sm">
                    <option value="">All levels</option>
                    @foreach ($levels as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['level'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-control">
                <label class="label py-1" for="filter-status"><span class="label-text text-xs font-semibold">Status</span></label>
                <select name="status" id="filter-status" class="select select-bordered select-sm">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-control">
                <label class="label py-1" for="filter-sort"><span class="label-text text-xs font-semibold">Sort</span></label>
                <select name="sort" id="filter-sort" class="select select-bordered select-sm">
                    <option value="score_desc" @selected($sort === 'score_desc')>Score (high → low)</option>
                    <option value="score_asc" @selected($sort === 'score_asc')>Score (low → high)</option>
                    <option value="ref" @selected($sort === 'ref')>Reference</option>
                    <option value="review_date" @selected($sort === 'review_date')>Review date</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary" id="filter-apply">Filter</button>
                <a href="{{ route('risks.index') }}" class="btn btn-sm btn-ghost" id="filter-reset">Reset</a>
            </div>
        </div>
    </form>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-sm" id="risk-table">
                    <thead>
                        <tr class="text-xs uppercase tracking-wide">
                            <th>Ref</th>
                            <th>Risk</th>
                            <th>Process</th>
                            <th class="text-center">L</th>
                            <th class="text-center">I</th>
                            <th class="text-right">Score</th>
                            <th>Level</th>
                            <th>Owner</th>
                            <th>Review</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($risks as $risk)
                            <tr>
                                <td class="font-mono font-semibold">{{ $risk->ref_no }}</td>
                                <td>
                                    <div class="font-medium">{{ $risk->title }}</div>
                                    @if ($risk->cause)
                                        <div class="text-xs opacity-60">Cause: {{ \Illuminate\Support\Str::limit($risk->cause, 70) }}</div>
                                    @endif
                                </td>
                                <td class="text-xs">{{ $risk->process_area ?? '—' }}</td>
                                <td class="text-center font-mono">{{ $risk->likelihood }}</td>
                                <td class="text-center font-mono">{{ $risk->impact }}</td>
                                <td class="text-right font-mono font-bold">{{ $risk->risk_score }}</td>
                                <td><span class="badge badge-sm {{ $risk->level_badge }}">{{ $risk->level_label }}</span></td>
                                <td class="text-xs">
                                    {{ $risk->riskOwner ? trim($risk->riskOwner->surname . ', ' . $risk->riskOwner->name) : '—' }}
                                </td>
                                <td class="text-xs">
                                    @if ($risk->review_date)
                                        <span class="{{ $risk->is_review_overdue ? 'text-error font-semibold' : '' }}">{{ $risk->review_date->format('d M Y') }}</span>
                                        @if ($risk->is_review_overdue)<span class="badge badge-xs badge-error ml-1">overdue</span>@endif
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                                <td><span class="badge badge-sm {{ $risk->status_badge }}">{{ $risk->status_label }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('risks.edit', $risk) }}" class="btn btn-xs btn-ghost" id="risk-edit-{{ $risk->id }}">Edit</a>
                                    <form method="POST" action="{{ route('risks.destroy', $risk) }}" class="inline"
                                          onsubmit="return confirm('Delete {{ $risk->ref_no }}?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-ghost text-error" id="risk-delete-{{ $risk->id }}">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-10" id="risk-empty">
                                    <div class="text-3xl mb-2">⚠️</div>
                                    <p class="font-medium">No risks recorded yet.</p>
                                    <p class="text-sm opacity-60">Add the first risk to start the register.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($risks->hasPages())
        <div class="flex justify-center">{{ $risks->links() }}</div>
    @endif
</div>
@endsection
