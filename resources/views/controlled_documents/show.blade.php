@extends('layouts.app')

@section('title', $document->code . ' — Version History')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    @if (session('success'))
        <div class="alert alert-success" id="flash-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error" id="flash-error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-error" id="flash-errors">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">📄</span>
                <div>
                    <h1 class="text-2xl font-extrabold">
                        <span class="font-mono" id="document-code">{{ $document->code }}</span>
                        {{ $document->title }}
                    </h1>
                    <p class="opacity-70 text-sm mt-0.5">
                        {{ \App\Models\ControlledDocument::DOCUMENT_TYPES[$document->document_type] ?? 'Document' }}
                        @if ($document->department) &middot; {{ $document->department }} @endif
                        &middot; {{ \App\Models\ControlledDocument::CONFIDENTIALITY_LEVELS[$document->confidentiality] ?? $document->confidentiality }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-lg {{ $document->status_badge }} border-0 py-4" id="document-status">
                    {{ $document->status_label }}
                </span>
                @if ($document->currentVersion)
                    <span class="badge badge-lg badge-ghost border-0 py-4 font-mono" id="document-current-version">
                        v{{ $document->currentVersion->version_no }}
                    </span>
                @endif
                <a href="{{ route('controlled_documents.index') }}" class="btn btn-sm btn-ghost" id="back-to-register">← Register</a>
            </div>
        </div>
    </div>

    {{-- Current version actions (QMS-10) --}}
    @if ($canManage && ($workflowVersion ?? $document->currentVersion))
        @php($current = $workflowVersion ?? $document->currentVersion)
        <div class="card bg-base-100 shadow">
            <div class="card-body py-4 px-5">
                <h2 class="card-title text-base" id="current-version-heading">
                    @if ($document->current_version_id === $current->id)
                        Current version — v<span id="current-version-no">{{ $current->version_no }}</span>
                    @else
                        Draft in progress — v<span id="current-version-no">{{ $current->version_no }}</span>
                        <span class="text-xs opacity-60 font-normal">(v{{ $document->currentVersion?->version_no }} stays effective until this is approved)</span>
                    @endif
                    <span class="badge badge-sm {{ $current->status_badge }}">{{ $current->status_label }}</span>
                </h2>

                <div class="flex flex-wrap items-end gap-3 mt-2">
                    @if ($current->status === \App\Models\ControlledDocumentVersion::STATUS_DRAFT)
                        <form method="POST" action="{{ route('controlled_documents.versions.submit', [$document, $current]) }}" id="form-submit">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning" id="btn-submit-for-review">📤 Submit for review</button>
                        </form>
                    @endif

                    @if ($current->status === \App\Models\ControlledDocumentVersion::STATUS_IN_REVIEW && $canReview)
                        <form method="POST" action="{{ route('controlled_documents.versions.approve', [$document, $current]) }}" id="form-approve">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" id="btn-approve">✅ Approve</button>
                        </form>

                        <form method="POST" action="{{ route('controlled_documents.versions.return', [$document, $current]) }}" class="flex items-end gap-2" id="form-return">
                            @csrf
                            <div class="form-control">
                                <label class="label py-1" for="return-note"><span class="label-text text-xs font-semibold">Return with a note</span></label>
                                <input type="text" name="note" id="return-note" placeholder="Why is this not approved?"
                                       class="input input-bordered input-sm w-72">
                            </div>
                            <button type="submit" class="btn btn-sm btn-error btn-outline" id="btn-return">↩ Return</button>
                        </form>
                    @endif
                </div>

                @if ($document->status === \App\Models\ControlledDocument::STATUS_APPROVED
                     && $current->status === \App\Models\ControlledDocumentVersion::STATUS_APPROVED)
                    <form method="POST" action="{{ route('controlled_documents.versions.store', $document) }}" class="flex flex-wrap items-end gap-2 mt-3 pt-3 border-t border-base-300" id="form-new-version">
                        @csrf
                        <div class="form-control">
                            <label class="label py-1" for="bump"><span class="label-text text-xs font-semibold">New version bump</span></label>
                            <select name="bump" id="bump" class="select select-bordered select-sm">
                                <option value="minor">Minor revision (editorial)</option>
                                <option value="major">Major revision (substantive)</option>
                            </select>
                        </div>
                        <div class="form-control flex-1">
                            <label class="label py-1" for="revision_summary"><span class="label-text text-xs font-semibold">Revision summary</span></label>
                            <input type="text" name="revision_summary" id="revision_summary" placeholder="What changed?"
                                   class="input input-bordered input-sm w-full">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary" id="btn-new-version">➕ Open next draft</button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- Version history timeline (QMS-10 acceptance) --}}
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-base">Version history
                <span class="badge badge-sm badge-ghost" id="version-count">{{ $versions->count() }}</span>
            </h2>

            @forelse ($versions as $version)
                <div class="flex gap-4 py-3 {{ ! $loop->last ? 'border-b border-base-200' : '' }}"
                     id="version-{{ $version->id }}" data-version-no="{{ $version->version_no }}"
                     data-version-status="{{ $version->status }}">
                    <div class="shrink-0 w-16">
                        <div class="font-mono font-bold">v{{ $version->version_no }}</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge badge-sm {{ $version->status_badge }}">{{ $version->status_label }}</span>
                            @if ($document->current_version_id === $version->id)
                                <span class="badge badge-sm badge-primary badge-outline">current</span>
                            @endif
                            @if ($version->supersedes_version_id)
                                <span class="badge badge-xs badge-ghost">supersedes v{{ optional($version->supersedes)->version_no }}</span>
                            @endif
                        </div>

                        {{-- Who prepared / reviewed / approved, and when --}}
                        <dl class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-1 text-xs">
                            <div>
                                <dt class="opacity-60">Prepared by</dt>
                                <dd class="font-medium" id="prepared-{{ $version->id }}">{{ $version->preparedBy?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="opacity-60">Reviewed by</dt>
                                <dd class="font-medium" id="reviewed-{{ $version->id }}">{{ $version->reviewedBy?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="opacity-60">Approved by</dt>
                                <dd class="font-medium" id="approved-{{ $version->id }}">
                                    {{ $version->approvedBy?->name ?? '—' }}
                                    @if ($version->approval_date)
                                        <span class="opacity-60">on {{ $version->approval_date->format('d M Y') }}</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        @if ($version->revision_summary)
                            <p class="mt-2 text-xs whitespace-pre-line opacity-80" id="summary-{{ $version->id }}">{{ $version->revision_summary }}</p>
                        @endif

                        @if ($version->file_name)
                            <div class="mt-2 text-xs">
                                <a href="{{ $version->file_path ? asset('storage/' . $version->file_path) : '#' }}"
                                   class="link link-primary" target="_blank" rel="noopener">{{ $version->file_name }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8" id="versions-empty">
                    <div class="text-3xl mb-2">🗂️</div>
                    <p class="font-medium">No versions yet.</p>
                    @if ($canManage && $document->status === \App\Models\ControlledDocument::STATUS_APPROVED)
                        <p class="text-sm opacity-60">Open the next draft above to start one.</p>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
