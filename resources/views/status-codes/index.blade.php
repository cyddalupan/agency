@extends('layouts.app')

@section('title', 'Status Codes')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>🏷️</span> Status Codes
            </h2>
            <p class="opacity-60 text-sm mt-1">Manage status codes and their labels</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('status-codes.create') }}" class="btn btn-primary">
                <span>➕</span> New Status Code
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="alert alert-error mb-4 text-sm shadow-sm">
            <span>❌</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($statuses->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>Code</th>
                            <th>Label</th>
                            <th>Label (Saudi)</th>
                            <th>Description</th>
                            <th>Color</th>
                            <th>Sort</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statuses as $statusCode)
                        <tr class="hover transition-colors">
                            <td class="font-mono text-sm">{{ $statusCode->code }}</td>
                            <td class="font-medium">{{ $statusCode->label }}</td>
                            <td>{{ $statusCode->label_saudi }}</td>
                            <td>{{ $statusCode->description }}</td>
                            <td>
                                <span class="inline-flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full border border-base-300" style="background-color: {{ $statusCode->color }}"></span>
                                    <span class="font-mono text-xs opacity-70">{{ $statusCode->color }}</span>
                                </span>
                            </td>
                            <td class="text-sm">{{ $statusCode->sort_order }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('status-codes.edit', $statusCode) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                    <form action="{{ route('status-codes.destroy', $statusCode) }}" method="POST"
                                          onsubmit="return confirm('Delete status code {{ $statusCode->code }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">🏷️</span>
                <h3 class="text-xl font-bold mb-2">No Status Codes Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">
                    Create status codes to categorize your records.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('status-codes.create') }}" class="btn btn-primary btn-lg">
                        <span>➕</span> Create Your First Status Code
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
