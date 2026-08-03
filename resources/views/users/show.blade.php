@extends('layouts.app')

@section('title', 'User: ' . $user->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="link link-hover text-sm">&larr; Back to Users</a>
    </div>

    <div class="bg-base-100 rounded-box shadow-sm border p-6">
        <h2 class="text-2xl font-bold mb-6">{{ $user->full_name }}</h2>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm opacity-60">Name</dt>
                <dd class="font-medium">{{ $user->full_name }}</dd>
            </div>
            <div>
                <dt class="text-sm opacity-60">Email</dt>
                <dd class="font-medium">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="text-sm opacity-60">Contact #</dt>
                <dd class="font-medium">{{ $user->contact ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm opacity-60">Branch</dt>
                <dd class="font-medium">{{ $user->branch?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm opacity-60">Access</dt>
                <dd><span class="badge badge-ghost">{{ \App\Models\User::accessLabel($user->user_type) }}</span></dd>
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

    {{-- Activity Log --}}
    <div class="bg-base-100 rounded-box shadow-sm border p-6 mt-6">
        <h3 class="text-lg font-bold mb-4">📋 Activity Log</h3>

        @if (!empty($activities) && $activities->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $activity)
                            <tr>
                                <td>
                                    <span class="badge badge-ghost">{{ $activity->action }}</span>
                                </td>
                                <td>{{ $activity->user?->name ?? 'System' }}</td>
                                <td class="text-sm opacity-60">{{ $activity->created_at?->diffForHumans() ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="opacity-60 text-sm">No activity recorded for this user.</p>
        @endif
    </div>
</div>
@endsection
