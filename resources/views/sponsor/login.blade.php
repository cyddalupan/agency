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
            <span class="text-3xl">🏢</span>
        </div>
        <h1 class="text-2xl font-extrabold text-gray-900">Sponsor Portal</h1>
        <p class="text-sm text-gray-500 mt-1">👇 Sign in with your ID Number</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
            <span>❌</span>
            <span>{{ $errors->first('login') ?: $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('sponsor.login.post') }}">
        @csrf

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">🆔 ID Number</label>
                <input type="text" name="login" value="{{ old('login') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                    placeholder="Enter your ID Number" required autofocus>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">🔑 Password</label>
                <input type="password" name="password"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                    placeholder="Enter your password" required>
            </div>
        </div>

        <div class="flex items-center justify-between mt-4">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                Remember me
            </label>
            <a href="{{ route('sponsor.password.request') }}" class="text-sm text-teal-600 hover:text-teal-700 font-medium">Forgot password?</a>
        </div>

        <button type="submit"
            class="w-full mt-6 py-2.5 bg-gradient-to-r from-teal-500 to-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:from-teal-600 hover:to-blue-700 transition-all text-sm">
            🔐 Sign In
        </button>
    </form>

    <div class="mt-6 text-center">
        <p class="text-sm text-gray-500">
            New sponsor?
            <a href="{{ route('sponsor.register') }}" class="text-teal-600 hover:text-teal-700 font-semibold">Register here</a>
        </p>
    </div>
</div>
@endsection
