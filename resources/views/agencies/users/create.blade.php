@extends('layouts.app')

@section('title', 'Add User — ' . $agency->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('agencies.show', $agency) }}" class="link link-hover text-sm opacity-60 mb-2 inline-block">
            ← Back to {{ $agency->name }}
        </a>
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>👤</span> Add User to {{ $agency->name }}
        </h2>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-base-100 rounded-box shadow-sm border p-6">
        <form action="{{ route('agencies.users.store', $agency) }}" method="POST">
            @csrf

            <div class="form-control mb-4">
                <label class="label" for="name"><span class="label-text">Full Name</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="input input-bordered @error('name') input-error @enderror">
            </div>

            <div class="form-control mb-4">
                <label class="label" for="email"><span class="label-text">Email</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="input input-bordered @error('email') input-error @enderror">
            </div>

            <div class="form-control mb-4">
                <label class="label" for="password"><span class="label-text">Password</span></label>
                <input type="password" name="password" id="password" required minlength="8"
                       class="input input-bordered @error('password') input-error @enderror">
            </div>

            <div class="form-control mb-4">
                <label class="label" for="user_type"><span class="label-text">Access Level</span></label>
                <select name="user_type" id="user_type" required class="select select-bordered w-full">
                    <option value="">Select access level...</option>
                    @foreach(\App\Models\User::ACCESS_PRESETS as $val => $label)
                        <option value="{{ $val }}" @selected(old('user_type') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control mb-6">
                <label class="label" for="status"><span class="label-text">Status</span></label>
                <select name="status" id="status" required class="select select-bordered w-full">
                    <option value="active" @selected(old('status') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="flex gap-2 justify-end">
                <a href="{{ route('agencies.show', $agency) }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>
@endsection
