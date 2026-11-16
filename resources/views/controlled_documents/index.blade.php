@extends('layouts.app')

@section('title', 'Controlled Documents')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">📄</span>
                <div>
                    <h1 class="text-2xl font-extrabold">Controlled Documents</h1>
                    <p class="opacity-70 text-sm mt-0.5">
                        The register of procedures, policies and forms — with their current version and review date.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0">
                    <span class="font-bold" id="document-count">{{ number_format($documents->total()) }}</span>
                    {{ $documents->total() === 1 ? 'document' : 'documents' }}
                </span>
                @if (in_array((string) auth()->user()->user_type, ['super_admin', 'admin', 'qms_manager'], true))
                <a href="{{ route('controlled_documents.create') }}"
                   class="btn btn-sm btn-secondary shadow-md" id="document-new">
                    ➕ New Document
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('controlled_documents.index') }}" class="card bg-base-100 shadow">
        <div class="card-body py-4 px-5 flex flex-col sm:flex-row gap-3 sm:items-end">
            <div class="form-control flex-1">
                <label class="label py-1" for="filter-q"><span class="label-text text-xs font-semibold">Code or title</span></label>
                <input type="text" name="q" id="filter-q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="e.g. QMS-001 or leave"
                       class="input input-bordered input-sm w-full">
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
            <div class="flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary" id="filter-apply">Filter</button>
                <a href="{{ route('controlled_documents.index') }}" class="btn btn-sm btn-ghost" id="filter-reset">Reset</a>
            </div>
        </div>
    </form>

    {{-- Register --}}
    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-sm" id="document-table">
                    <thead>
                        <tr class="text-xs uppercase tracking-wide">
                            <th>Code</th>
                            <th>Title</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Next review</th>
                            <th>Owner</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td class="font-mono font-semibold">{{ $document->code }}</td>
                                <td>
                                    <div class="font-medium">{{ $document->title }}</div>
                                    @if ($document->document_type)
                                        <div class="text-xs opacity-60">{{ \App\Models\ControlledDocument::DOCUMENT_TYPES[$document->document_type] ?? $document->document_type }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($document->currentVersion)
                                        <span class="font-mono">v{{ $document->currentVersion->version_no }}</span>
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-sm {{ $document->status_badge }}">{{ $document->status_label }}</span>
                                </td>
                                <td>
                                    @if ($document->next_review_date)
                                        <span class="{{ $document->is_review_overdue ? 'text-error font-semibold' : '' }}">
                                            {{ $document->next_review_date->format('d M Y') }}
                                        </span>
                                        @if ($document->is_review_overdue)
                                            <span class="badge badge-xs badge-error ml-1">overdue</span>
                                        @endif
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($document->processOwner)
                                        {{ trim(implode(', ', array_filter([$document->processOwner->surname, $document->processOwner->name]))) }}
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10" id="document-empty">
                                    <div class="text-3xl mb-2">📄</div>
                                    <p class="font-medium">No controlled documents yet.</p>
                                    <p class="text-sm opacity-60">Create the first one to start the register.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($documents->hasPages())
        <div class="flex justify-center">{{ $documents->links() }}</div>
    @endif
</div>
@endsection
