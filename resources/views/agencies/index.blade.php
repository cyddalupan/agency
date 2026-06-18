@extends('layouts.app')

@section('title', 'Agencies')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>🏢</span> Agencies
            </h2>
            <p class="opacity-60 text-sm mt-1">Manage all agencies in the system</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('agencies.create') }}" class="btn btn-primary">
                <span>➕</span> Add Agency
            </a>
        </div>
    </div>

    {{-- Success / Error Messages --}}
    @if (session('success'))
        <div class="alert alert-success mb-4">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error mb-4">
            <span>❌</span> {{ session('error') }}
        </div>
    @endif

    {{-- Status Filter --}}
    <div class="mb-4 flex gap-2">
        <a href="{{ route('agencies.index') }}"
           class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-ghost' }}">
            All
        </a>
        <a href="{{ route('agencies.index', ['status' => 'active']) }}"
           class="btn btn-sm {{ request('status') === 'active' ? 'btn-primary' : 'btn-ghost' }}">
            Active
        </a>
        <a href="{{ route('agencies.index', ['status' => 'inactive']) }}"
           class="btn btn-sm {{ request('status') === 'inactive' ? 'btn-primary' : 'btn-ghost' }}">
            Inactive
        </a>
    </div>

    {{-- Agencies Table --}}
    <div class="overflow-x-auto bg-base-100 rounded-box shadow-sm border">
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Subdomain</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($agencies as $agency)
                    <tr>
                        <td class="font-medium">{{ $agency->name }}</td>
                        <td><code>{{ $agency->subdomain }}</code></td>
                        <td>
                            @if ($agency->status === 'active')
                                <span class="badge badge-success">Active</span>
                            @elseif ($agency->status === 'inactive')
                                <span class="badge badge-warning">Inactive</span>
                            @else
                                <span class="badge badge-error">{{ ucfirst($agency->status) }}</span>
                            @endif
                        </td>
                        <td>{{ $agency->created_at?->format('M d, Y') ?? '—' }}</td>
                        <td>
                            <div class="flex gap-1">
                                <a href="{{ route('agencies.edit', $agency) }}" class="btn btn-xs btn-ghost">Edit</a>
                                @if ($agency->status === 'active')
                                    <form action="{{ route('agencies.deactivate', $agency) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-xs btn-ghost text-warning">Deactivate</button>
                                    </form>
                                @else
                                    <form action="{{ route('agencies.activate', $agency) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-xs btn-ghost text-success">Activate</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 opacity-60">
                            No agencies found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $agencies->links() }}
    </div>
</div>
@endsection
