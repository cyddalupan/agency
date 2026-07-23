@extends('layouts.employer-guest')

@section('content')
<div class="absolute inset-0 overflow-hidden pointer-events-none">
    <div class="absolute -top-24 -left-24 w-80 h-80 bg-teal-400/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl"></div>
    <div class="absolute top-1/3 left-1/2 w-64 h-64 bg-cyan-300/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-1/4 left-1/4 w-48 h-48 bg-teal-500/10 rounded-full blur-3xl"></div>
</div>

<div class="w-full max-w-md relative z-10 animate-fade-in">
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8">
        {{-- Logo / Header --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-teal-500 to-blue-600 rounded-2xl shadow-lg mb-4">
                <span class="text-3xl">🌍</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900">{{ __('messages.fra_portal') }}</h1>
            <p class="text-sm text-gray-500 mt-1">🌐 {{ __('messages.fra_portal') }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
                <span>❌</span>
                <span>{{ $errors->first('login') ?: __('messages.invalid_credentials') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('fra.login.post') }}">
            @csrf

            <div class="mb-4">
                <label for="login" class="block text-sm font-medium text-gray-700 mb-1.5">📧 {{ __('messages.email_or_username') }}</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-lg opacity-50">👤</span>
                    <input
                        id="login"
                        type="text"
                        name="login"
                        value="{{ old('login') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="{{ __('messages.email_placeholder') }}"
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all @error('login') border-red-400 @enderror"
                    >
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">🔑 {{ __('messages.password') }}</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-lg opacity-50">🔒</span>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all @error('password') border-red-400 @enderror"
                    >
                </div>
            </div>

            <div class="mb-6 flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-gray-800 transition-colors">
                    <input
                        type="checkbox"
                        name="remember"
                        class="rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                    >
                    ✅ {{ __('messages.remember_me') }}
                </label>
                @if (Route::has('fra.password.request'))
                <a href="{{ route('fra.password.request') }}" class="text-sm text-teal-600 hover:text-teal-800 hover:underline">
                    {{ __('messages.forgot_password_link') }}
                </a>
                @endif
            </div>

            <button
                type="submit"
                class="w-full bg-gradient-to-r from-teal-500 to-blue-600 hover:from-teal-600 hover:to-blue-700 text-white no-underline py-2.5 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2"
            >
                <span>🚀</span> {{ __('messages.sign_in') }}
            </button>
        </form>

        <div class="flex items-center justify-center gap-4 mt-5 pt-4 border-t border-gray-100">
            <span class="text-xs text-gray-400">🌐 {{ __('messages.language') }}:</span>
            <div class="flex gap-1.5">
                @foreach (config('app.supported_languages', ['en' => 'English', 'ar' => 'العربية', 'zh' => '中文', 'ja' => '日本語']) as $code => $label)
                    <a href="{{ route('fra.language.switch', $code) }}"
                       class="text-xs px-2.5 py-1 rounded-full transition-colors {{ app()->getLocale() === $code ? 'bg-[#29A1C4] text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">
            🔒 {{ __('messages.secure_login') }}
        </p>
    </div>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.5s ease-out;
    }
</style>
@endsection
