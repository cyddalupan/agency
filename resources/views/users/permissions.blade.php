@extends('layouts.app')

@section('title', 'Permissions: ' . $user->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('users.show', $user) }}" class="link link-hover text-sm">&larr; Back to {{ $user->name }}</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-4">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif

    <div class="bg-base-100 rounded-box shadow-sm border p-6">
        <h2 class="text-2xl font-bold mb-2">{{ $user->name }}</h2>
        <p class="opacity-60 text-sm mb-6">Manage role and granular permissions for this user.</p>

        <form action="{{ route('users.permissions.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Role Selector --}}
            <div class="form-control mb-6">
                <label class="label">
                    <span class="label-text font-semibold">Role (user_type)</span>
                </label>
                <select name="user_type" id="user_type" class="select select-bordered w-full">
                    <option value="">Select a role...</option>
                    <option value="admin" @selected($user->user_type === 'admin')>Admin</option>
                    <option value="staff" @selected($user->user_type === 'staff')>Staff</option>
                    <option value="coordinator" @selected($user->user_type === 'coordinator')>Coordinator</option>
                    <option value="recruiter" @selected($user->user_type === 'recruiter')>Recruiter</option>
                    <option value="processor" @selected($user->user_type === 'processor')>Processor</option>
                    <option value="interviewer" @selected($user->user_type === 'interviewer')>Interviewer</option>
                    <option value="manager" @selected($user->user_type === 'manager')>Manager</option>
                    <option value="billing" @selected($user->user_type === 'billing')>Billing</option>
                    <option value="report_viewer" @selected($user->user_type === 'report_viewer')>Report Viewer</option>
                    <option value="marketer" @selected($user->user_type === 'marketer')>Marketer</option>
                    <option value="director" @selected($user->user_type === 'director')>Director</option>
                    <option value="super_admin" @selected($user->user_type === 'super_admin')>Super Admin</option>
                </select>
                @error('user_type')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Granular Permissions --}}
            <div class="mb-6">
                <label class="label">
                    <span class="label-text font-semibold">Granular Permissions</span>
                    <span class="label-text-alt opacity-60">Select individual permissions</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-2 cursor-pointer p-2 rounded hover:bg-base-200">
                            <input type="checkbox"
                                   name="permissions[]"
                                   value="{{ $permission }}"
                                   class="checkbox checkbox-sm"
                                   @checked(in_array($permission, $userPermissions))>
                            <span class="text-sm capitalize">{{ str_replace('_', ' ', $permission) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('permissions.*')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Save Permissions</button>
                <a href="{{ route('users.show', $user) }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
