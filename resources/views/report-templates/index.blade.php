@extends('layouts.app')

@section('title', 'Report Templates')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">📋 Report Templates</h1>
        <a href="{{ route('report-templates.create') }}" class="btn btn-primary btn-sm">
            + New Template
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($templates->isEmpty())
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-8">
                <p class="text-lg opacity-70 mb-2">No report templates yet.</p>
                <p class="text-sm opacity-50 mb-4">Create your first template to generate custom reports.</p>
                <a href="{{ route('report-templates.create') }}" class="btn btn-primary">Create Template</a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4">
            @foreach ($templates as $template)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="card-title text-lg">
                                    {{ $template->name }}
                                    @if (!$template->is_active)
                                        <span class="badge badge-ghost text-xs">Inactive</span>
                                    @endif
                                </h2>
                                <p class="text-sm opacity-70 mt-1">
                                    Type: <span class="badge badge-outline badge-sm">{{ $template->type }}</span>
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('report-templates.edit', $template) }}"
                                   class="btn btn-ghost btn-xs">Edit</a>
                                <form action="{{ route('report-templates.destroy', $template) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-xs text-error">Delete</button>
                                </form>
                            </div>
                        </div>
                        @if (!empty($template->config))
                            <div class="mt-2 text-xs opacity-50">
                                Columns: {{ implode(', ', $template->config['columns'] ?? []) }}
                                @if (!empty($template->config['group_by']))
                                    · Grouped by: {{ $template->config['group_by'] }}
                                @endif
                                · Sorted: {{ $template->config['sort_by'] ?? 'created_at' }}
                                {{ $template->config['sort_order'] ?? 'desc' }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
