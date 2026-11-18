@extends('layouts.app')

@section('title', 'Positions')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>💼</span> Positions
            </h2>
            <p class="opacity-60 text-sm mt-1">Manage available job positions</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('positions.export') }}" class="btn btn-outline gap-1" title="Export all positions to CSV">
                <span>📤</span> Export CSV
            </a>
            <a href="{{ route('positions.bulk') }}" class="btn btn-outline gap-1" title="Bulk upload positions from CSV">
                <span>📥</span> Bulk Upload
            </a>
            <a href="{{ route('positions.create') }}" class="btn btn-primary">
                <span>➕</span> New Position
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($positions->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>Name</th>
                            <th>Category</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($positions as $position)
                        <tr class="hover transition-colors">
                            <td>
                                <div class="font-medium">{{ $position->name }}</div>
                                @if($position->description)
                                    <div class="text-xs opacity-60">{{ \Illuminate\Support\Str::limit($position->description, 90) }}</div>
                                @endif
                            </td>
                            <td>
                                @if(($position->category ?? 'skilled') === 'household')
                                    <span class="badge badge-sm badge-warning">🏠 Household</span>
                                @elseif(($position->category ?? 'skilled') === 'professional')
                                    <span class="badge badge-sm badge-accent">🎓 Professional</span>
                                @else
                                    <span class="badge badge-sm badge-info">💼 Skilled</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('positions.edit', $position) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                    <form action="{{ route('positions.destroy', $position) }}" method="POST"
                                          onsubmit="return confirm('Delete {{ $position->name }}?')">
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
                <span class="text-6xl mb-4">💼</span>
                <h3 class="text-xl font-bold mb-2">No Positions Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">
                    Create positions to categorize your records.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('positions.create') }}" class="btn btn-primary btn-lg">
                        <span>➕</span> Create Your First Position
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
