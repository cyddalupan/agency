@extends('portal.layouts.app')

@section('title', 'Applicant Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-base-200 px-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl text-primary-content font-bold">AP</span>
                </div>
                <h2 class="text-2xl font-bold">Applicant Portal</h2>
                <p class="text-sm opacity-70 mt-1">Sign in to view your application status</p>
            </div>

            @if ($errors->any())
                <div role="alert" class="alert alert-error mb-4 text-sm">
                    <span>❌</span>
                    <span>{{ $errors->first('email') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('portal.login.post') }}">
                @csrf

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="input input-bordered @error('email') input-error @enderror"
                        placeholder="your@email.com" required autofocus>
                </div>

                <div class="form-control mb-2">
                    <label class="label">
                        <span class="label-text">Password</span>
                    </label>
                    <input type="password" name="password"
                        class="input input-bordered @error('email') input-error @enderror"
                        placeholder="••••••••" required>
                </div>

                <div class="form-control mb-6">
                    <label class="label cursor-pointer justify-start gap-2">
                        <input type="checkbox" name="remember" class="checkbox checkbox-primary checkbox-sm">
                        <span class="label-text">Remember me</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    Sign In
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-sm opacity-70 hover:opacity-100">
                    ← Staff login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
