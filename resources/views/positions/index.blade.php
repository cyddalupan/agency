@extends('layouts.app')

@section('title', 'Positions')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>💼</span> Positions
            </h2>
            <p class="opacity-60 text-sm mt-1">Manage available job positions</p>
        </div>
        <div class="flex gap-2">
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
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($positions as $position)
                        <tr class="hover transition-colors">
                            <td class="font-medium">{{ $position->name }}</td>
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

        <div class="mt-4">
            {{ $positions->links() }}
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
