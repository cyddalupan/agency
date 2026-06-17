@extends('portal.layouts.app')

@section('title', 'OTP Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-base-200 px-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl text-primary-content font-bold">🔐</span>
                </div>
                <h2 class="text-2xl font-bold">OTP Login</h2>
                <p class="text-sm opacity-70 mt-1">Enter your email to receive a one-time password</p>
            </div>

            @if (session("success") || isset($success))
                <div role="alert" class="alert alert-success mb-4 text-sm">
                    <span>✅</span>
                    <span>{{ session("success") ?? $success ?? "" }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="alert alert-error mb-4 text-sm">
                    <span>❌</span>
                    <span>{{ $errors->first('otp') ?? $errors->first('email') }}</span>
                </div>
            @endif

            {{-- Step 1: Request OTP --}}
            <form method="POST" action="{{ route('portal.login.otp.send') }}">
                @csrf
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="input input-bordered @error('email') input-error @enderror"
                        placeholder="your@email.com" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    Send OTP
                </button>
            </form>

            {{-- Step 2: Verify OTP --}}
            <div class="divider mt-8">OR</div>
            <form method="POST" action="{{ route('portal.login.otp.verify') }}">
                @csrf
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="input input-bordered" placeholder="your@email.com" required>
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">OTP Code</span>
                    </label>
                    <input type="text" name="otp"
                        class="input input-bordered" placeholder="123456"
                        maxlength="6" pattern="\d{6}" required>
                </div>

                <button type="submit" class="btn btn-success w-full">
                    Verify OTP & Login
                </button>
            </form>

            <div class="text-center mt-6 flex flex-col gap-2">
                <a href="{{ route('portal.login') }}" class="text-sm opacity-70 hover:opacity-100">
                    ← Back to password login
                </a>
                <a href="{{ route('login') }}" class="text-sm opacity-70 hover:opacity-100">
                    ← Staff login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
