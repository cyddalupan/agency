@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>👥</span> Users
            </h2>
            <p class="opacity-60 text-sm mt-1">Manage agency users, roles, and permissions</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <span>➕</span> Add User
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

    {{-- Users Table --}}
    <div class="overflow-x-auto bg-base-100 rounded-box shadow-sm border">
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <a href="{{ route('users.show', $user) }}" class="link link-hover font-medium">
                                {{ $user->name }}
                            </a>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge badge-ghost">{{ $user->user_type }}</span>
                        </td>
                        <td>
                            @if ($user->status === 'active')
                                <span class="badge badge-success">Active</span>
                            @elseif ($user->status === 'inactive')
                                <span class="badge badge-warning">Inactive</span>
                            @else
                                <span class="badge badge-error">Suspended</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-1">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-xs btn-ghost">View</a>
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-xs btn-ghost">Edit</a>
                                <a href="{{ route('users.permissions', $user) }}" class="btn btn-xs btn-ghost">Permissions</a>
                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-ghost text-error">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 opacity-60">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
