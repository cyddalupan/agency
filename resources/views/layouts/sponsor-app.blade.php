@php $universe = config('app.universe', 1); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $universe == 2 ? 'universe-2' : 'corporate' }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sponsor Portal') — Sponsor Portal</title>
    @if($universe == 2)
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⛩️</text></svg>" type="image/svg+xml">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚡</text></svg>" type="image/svg+xml">
    @endif
    @vite('resources/css/app.css')
    @yield('head')
</head>
<body class="bg-gray-50 min-h-screen">

    @php
        $currentLang = app()->getLocale();
        $langs = config('app.supported_languages', ['en' => 'English', 'ar' => 'العربية', 'zh' => '中文', 'ja' => '日本語']);
    @endphp

    {{-- Mobile drawer overlay --}}
    <div class="drawer lg:hidden">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex flex-col">
            {{-- Mobile top bar --}}
            <div class="navbar bg-teal-600 text-white px-4 min-h-14 sticky top-0 z-30">
                <div class="flex-1 flex items-center gap-2">
                    <label for="sidebar-drawer" class="btn btn-ghost btn-square text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </label>
                    <div class="flex items-center gap-1">
                        <span class="text-lg">🏢</span>
                        <span class="font-semibold text-sm">Sponsor Portal</span>
                    </div>
                </div>
            </div>

            <main class="flex-1 p-4">
                @if (session('success'))
                <div class="mb-4">
                    <div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg text-sm shadow-sm flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
                @endif
                @if (session('error'))
                <div class="mb-4">
                    <div class="bg-red-100 text-red-800 px-4 py-3 rounded-lg text-sm shadow-sm flex items-center gap-2">
                        <span>❌</span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
                @endif
                @yield('content')
            </main>
        </div>

        {{-- Mobile drawer --}}
        <div class="drawer-side z-40">
            <label for="sidebar-drawer" class="drawer-overlay"></label>
            <div class="flex flex-col min-h-full w-72 bg-white">
                <div class="px-6 py-5 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🏢</span>
                        <span class="font-bold text-xl text-teal-600">Sponsor Portal</span>
                    </div>
                </div>
                <nav class="flex-1 px-3 py-4 space-y-1">
                    <a href="{{ route('sponsor.dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors hover:bg-teal-50 {{ request()->routeIs('sponsor.dashboard') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700' }}">
                        📋 {{ __('Line Up') }}
                    </a>
                    <a href="{{ route('sponsor.my-applicants') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors hover:bg-teal-50 {{ request()->routeIs('sponsor.my-applicants') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700' }}">
                        👥 {{ __('My Applicants') }}
                    </a>
                </nav>

                {{-- Mobile drawer language dropdown --}}
                <div class="border-t border-gray-100 px-4 py-3">
                    <p class="text-xs font-medium text-gray-500 mb-2">{{ __('Language') }}</p>
                    <form method="POST" action="{{ route('sponsor.account.language.update') }}" id="mobile-lang-form">
                        @csrf
                        <select name="language" onchange="this.form.submit()"
                            class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg bg-white text-gray-700 cursor-pointer focus:outline-none focus:border-teal-500">
                            @foreach ($langs as $code => $label)
                                <option value="{{ $code }}" {{ $currentLang === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="border-t border-gray-200 px-4 py-4">
                    <div class="flex items-center gap-3 text-sm">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-teal-500 to-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-sm">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="truncate font-medium text-gray-800">{{ $user->name }}</p>
                        </div>
                        <form method="POST" action="{{ route('sponsor.logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Desktop layout --}}
    <div class="hidden lg:flex lg:flex-col min-h-screen">
        {{-- Top bar --}}
        <div class="bg-white border-b border-gray-200 px-6 py-2 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-2">
                <span class="text-xl">🏢</span>
                <span class="font-bold text-lg text-teal-600">Sponsor Portal</span>
            </div>

            <div class="flex items-center gap-4">
                        {{-- Desktop language dropdown --}}
                <form method="POST" action="{{ route('sponsor.account.language.update') }}" id="desktop-lang-form" class="flex items-center">
                    @csrf
                    <select name="language" onchange="this.form.submit()"
                        class="text-xs px-2 py-1.5 border border-gray-300 rounded bg-white text-gray-700 cursor-pointer
                               focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500/30">
                        @foreach ($langs as $code => $label)
                            <option value="{{ $code }}" {{ $currentLang === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>

                <form method="POST" action="{{ route('sponsor.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Horizontal tabs --}}
        <nav class="bg-gradient-to-r from-teal-600 to-teal-500 text-white shadow-md">
            <div class="max-w-7xl mx-auto flex">
                <a href="{{ route('sponsor.dashboard') }}"
                   class="flex items-center gap-2 px-6 py-3.5 text-sm font-medium transition-all duration-150 hover:bg-white/15 {{ request()->routeIs('sponsor.dashboard') ? 'bg-white/20 font-semibold shadow-inner' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span>{{ __('Line Up') }}</span>
                </a>
                <a href="{{ route('sponsor.my-applicants') }}"
                   class="flex items-center gap-2 px-6 py-3.5 text-sm font-medium transition-all duration-150 hover:bg-white/15 {{ request()->routeIs('sponsor.my-applicants') ? 'bg-white/20 font-semibold shadow-inner' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                    <span>{{ __('My Applicants') }}</span>
                </a>
            </div>
        </nav>

        {{-- Content --}}
        <main class="flex-1 p-6 overflow-x-auto">
            @if (session('success'))
            <div class="mb-4">
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg text-sm shadow-sm flex items-center gap-2">
                    <span>✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            @endif
            @if (session('error'))
            <div class="mb-4">
                <div class="bg-red-100 text-red-800 px-4 py-3 rounded-lg text-sm shadow-sm flex items-center gap-2">
                    <span>❌</span>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
            @endif
            @yield('content')
        </main>

        <footer class="px-6 py-3 text-center text-xs opacity-40 border-t border-gray-200 bg-white">
            🏢 {{ __('Sponsor Portal') }} &bull; {{ __('Powered by') }} {{ app_brand_name() }}
        </footer>
    </div>

    @yield('scripts')
</body>
</html>
