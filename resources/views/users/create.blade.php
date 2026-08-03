@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Users
        </a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>➕</span> Add New User
        </h2>
        <p class="opacity-60 text-sm mt-1">Create a new user account for your agency.</p>
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

    <form method="POST" action="{{ route('users.store') }}" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">👤 First Name <span class="text-error">*</span></legend>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="input w-full" placeholder="First name">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">✳️ Middle Name</legend>
                    <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                        class="input w-full" placeholder="Middle name (optional)">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📛 Surname</legend>
                    <input type="text" name="surname" value="{{ old('surname') }}"
                        class="input w-full" placeholder="Surname (optional)">
                </fieldset>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📧 Email <span class="text-error">*</span></legend>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="input w-full" placeholder="email@example.com">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📞 Contact #</legend>
                    <input type="text" name="contact" value="{{ old('contact') }}"
                        class="input w-full" placeholder="e.g. 0917-xxx-xxxx">
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🔑 Password <span class="text-error">*</span></legend>
                <input type="password" name="password" required
                    class="input w-full" placeholder="Minimum 8 characters">
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🔑 Confirm Password <span class="text-error">*</span></legend>
                <input type="password" name="password_confirmation" required
                    class="input w-full" placeholder="Confirm password">
            </fieldset>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🎭 Access Level <span class="text-error">*</span></legend>
                    <select name="user_type" required class="select w-full">
                        <option value="">Select access level...</option>
                        @foreach(\App\Models\User::ACCESS_PRESETS as $val => $label)
                            <option value="{{ $val }}" @selected(old('user_type') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs opacity-60">Super Admin, Admin, Accounting, Receptionist, Processing</p>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🏢 Branch</legend>
                    <select name="branch_id" class="select w-full">
                        <option value="">Select branch...</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📊 Status <span class="text-error">*</span></legend>
                <select name="status" required class="select w-full">
                    <option value="active" @selected(old('status') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                </select>
            </fieldset>

            <div class="flex gap-2 pt-2">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="{{ route('users.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
