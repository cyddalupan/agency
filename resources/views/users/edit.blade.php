@extends('layouts.app')

@section('title', 'Edit User: ' . $user->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Users
        </a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>✏️</span> Edit User: {{ $user->name }}
        </h2>
        <p class="opacity-60 text-sm mt-1">Update user information, role, and status.</p>
    </div>

    @if($errors->any())
        <div role="alert" class="alert alert-error mb-6 shadow-sm">
            <span>❌</span>
            <ul class="list-disc pl-4 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.update', $user) }}" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf
            @method('PUT')

            <fieldset class="fieldset">
                <legend class="fieldset-legend">👤 Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="input w-full" placeholder="Full name">
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📧 Email <span class="text-error">*</span></legend>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="input w-full" placeholder="email@example.com">
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🎭 User Type <span class="text-error">*</span></legend>
                <select name="user_type" required class="select w-full">
                    <option value="admin" @selected(old('user_type', $user->user_type) === 'admin')>Admin</option>
                    <option value="super_admin" @selected(old('user_type', $user->user_type) === 'super_admin')>Super Admin</option>
                    <option value="employer" @selected(old('user_type', $user->user_type) === 'employer')>Employer</option>
                    <option value="manager" @selected(old('user_type', $user->user_type) === 'manager')>Manager</option>
                    <option value="staff" @selected(old('user_type', $user->user_type) === 'staff')>Staff</option>
                    <option value="coordinator" @selected(old('user_type', $user->user_type) === 'coordinator')>Coordinator</option>
                    <option value="recruiter" @selected(old('user_type', $user->user_type) === 'recruiter')>Recruiter</option>
                    <option value="processor" @selected(old('user_type', $user->user_type) === 'processor')>Processor</option>
                    <option value="interviewer" @selected(old('user_type', $user->user_type) === 'interviewer')>Interviewer</option>
                    <option value="billing" @selected(old('user_type', $user->user_type) === 'billing')>Billing</option>
                    <option value="report_viewer" @selected(old('user_type', $user->user_type) === 'report_viewer')>Report Viewer</option>
                    <option value="director" @selected(old('user_type', $user->user_type) === 'director')>Director</option>
                    <option value="marketer" @selected(old('user_type', $user->user_type) === 'marketer')>Marketer</option>
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📊 Status <span class="text-error">*</span></legend>
                <select name="status" required class="select w-full">
                    <option value="active" @selected(old('status', $user->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Inactive</option>
                    <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Suspended</option>
                </select>
            </fieldset>

            <div class="flex gap-2 pt-2">
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="{{ route('users.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
