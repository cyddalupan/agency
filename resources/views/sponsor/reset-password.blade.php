@extends('layouts.sponsor-guest')

@section('content')
<div class="absolute inset-0 overflow-hidden pointer-events-none">
    <div class="absolute -top-24 -left-24 w-80 h-80 bg-teal-400/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl"></div>
    <div class="absolute top-1/3 left-1/2 w-64 h-64 bg-cyan-300/10 rounded-full blur-3xl"></div>
</div>

<div class="w-full max-w-md bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8 relative z-10">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-teal-500 to-blue-600 rounded-2xl shadow-lg mb-4">
            <span class="text-3xl">🔑</span>
        </div>
        <h1 class="text-2xl font-extrabold text-gray-900">{{ __('Reset Password') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('Choose a new password for your account') }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
            <span>❌</span>
            <span>{{ $errors->first('email') ?: $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('sponsor.password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">📧 Email</label>
                <input type="email" name="email" value="{{ old('email', request('email')) }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                    placeholder="Your registered email" required readonly>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">🔑 New Password</label>
                <input type="password" name="password"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                    placeholder="Minimum 8 characters" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">🔑 Confirm Password</label>
                <input type="password" name="password_confirmation"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                    placeholder="Re-enter new password" required>
            </div>
        </div>

        <button type="submit"
            class="w-full mt-6 py-2.5 bg-gradient-to-r from-teal-500 to-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:from-teal-600 hover:to-blue-700 transition-all text-sm">
            🔑 Reset Password
        </button>
    </form>

    <div class="mt-6 text-center">
        <p class="text-sm text-gray-500">
            <a href="{{ route('sponsor.login') }}" class="text-teal-600 hover:text-teal-700 font-semibold">Back to Sign In</a>
        </p>
    </div>
</div>
@endsection
