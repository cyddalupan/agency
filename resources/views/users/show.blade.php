@extends('layouts.app')

@section('title', 'User: ' . $user->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="link link-hover text-sm">&larr; Back to Users</a>
    </div>

    <div class="bg-base-100 rounded-box shadow-sm border p-6">
        <h2 class="text-2xl font-bold mb-6">{{ $user->name }}</h2>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm opacity-60">Name</dt>
                <dd class="font-medium">{{ $user->name }}</dd>
            </div>
            <div>
                <dt class="text-sm opacity-60">Email</dt>
                <dd class="font-medium">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="text-sm opacity-60">Role</dt>
                <dd><span class="badge badge-ghost">{{ $user->user_type }}</span></dd>
            </div>
            <div>
                <dt class="text-sm opacity-60">Status</dt>
                <dd>
                    @if ($user->status === 'active')
                        <span class="badge badge-success">Active</span>
                    @elseif ($user->status === 'inactive')
                        <span class="badge badge-warning">Inactive</span>
                    @else
                        <span class="badge badge-error">Suspended</span>
                    @endif
                </dd>
            </div>
        </dl>

        <div class="mt-6 flex gap-2">
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">Edit User</a>
            <a href="{{ route('users.index') }}" class="btn btn-ghost">Back</a>
        </div>
    </div>
</div>
@endsection
