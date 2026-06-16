@extends('layouts.employer-guest')

@section('title', 'Employer Login')

@section('content')
<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <span class="text-5xl">🏢</span>
        <h1 class="text-2xl font-bold mt-4">Employer Login</h1>
        <p class="text-base-content/60 mt-1">Sign in to your employer account</p>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-8">
            <form method="POST" action="{{ route('employer.login.post') }}">
                @csrf

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Email</span>
                    </label>
                    <input type="email" name="email"
                           class="input input-bordered w-full @error('email') input-error @enderror"
                           value="{{ old('email') }}" required autofocus autocomplete="email" />
                    @error('email')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                    @enderror
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Password</span>
                    </label>
                    <input type="password" name="password"
                           class="input input-bordered w-full @error('password') input-error @enderror"
                           required autocomplete="current-password" />
                </div>

                <div class="form-control mb-6">
                    <label class="label cursor-pointer justify-start gap-2">
                        <input type="checkbox" name="remember" value="1" class="checkbox checkbox-primary" checked />
                        <span class="label-text">Remember me</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    Sign In
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
