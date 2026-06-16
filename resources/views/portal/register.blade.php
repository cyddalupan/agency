@extends('portal.layouts.app')

@section('title', 'Applicant Registration')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-base-200 px-4 py-8">
    <div class="card w-full max-w-lg bg-base-100 shadow-xl">
        <div class="card-body p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl text-primary-content font-bold">AP</span>
                </div>
                <h2 class="text-2xl font-bold">Create Your Account</h2>
                <p class="text-sm opacity-70 mt-1">Register to track your application status</p>
            </div>

            @if ($errors->any())
                <div role="alert" class="alert alert-error mb-4 text-sm">
                    <span>❌</span>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('portal.register.post') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">First Name *</span>
                        </label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}"
                            class="input input-bordered @error('first_name') input-error @enderror"
                            placeholder="Juan" required autofocus>
                        @error('first_name')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Last Name *</span>
                        </label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}"
                            class="input input-bordered @error('last_name') input-error @enderror"
                            placeholder="Dela Cruz" required>
                        @error('last_name')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Middle Name</span>
                        </label>
                        <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                            class="input input-bordered"
                            placeholder="(optional)">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Contact Number</span>
                        </label>
                        <input type="text" name="contact" value="{{ old('contact') }}"
                            class="input input-bordered"
                            placeholder="0917 123 4567">
                    </div>
                </div>

                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Email *</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="input input-bordered @error('email') input-error @enderror"
                        placeholder="your@email.com" required>
                    @error('email')
                        <span class="text-error text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Password *</span>
                        </label>
                        <input type="password" name="password"
                            class="input input-bordered @error('password') input-error @enderror"
                            placeholder="Min. 8 characters" required>
                        @error('password')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Confirm Password *</span>
                        </label>
                        <input type="password" name="password_confirmation"
                            class="input input-bordered"
                            placeholder="Re-enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full mt-6">
                    Create Account
                </button>
            </form>

            <div class="text-center mt-6 space-y-2">
                <p class="text-sm opacity-70">
                    Already have an account?
                    <a href="{{ route('portal.login') }}" class="link link-primary">Sign in</a>
                </p>
                <a href="{{ route('login') }}" class="text-sm opacity-70 hover:opacity-100 block">
                    ← Staff login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
