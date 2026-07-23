@php
    $currentLang = app()->getLocale();
    $langs = config('app.supported_languages', ['en' => 'English', 'ar' => 'العربية']);
    $universe = config('app.universe', 1);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLang) }}" data-theme="{{ $universe == 2 ? 'universe-2' : 'corporate' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('messages.fra_portal')) — {{ __('messages.fra_portal') }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌍</text></svg>" type="image/svg+xml">
    @vite('resources/css/app.css')
    @yield('head')
</head>
<body class="bg-base-200 bg-noise min-h-screen">

    {{-- Mobile drawer overlay — hidden on lg+ --}}
    <div class="drawer lg:hidden">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex flex-col">
            {{-- Mobile top bar with hamburger --}}
            <div class="navbar bg-[#29A1C4] text-white px-4 min-h-14 sticky top-0 z-30">
                <div class="flex-1 flex items-center gap-2">
                    <label for="sidebar-drawer" class="btn btn-ghost btn-square text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </label>
                    <div class="logo flex items-center gap-1">
                        <span class="text-lg">🌍</span>
                        <span class="font-semibold text-sm">{{ __('messages.fra_portal') }}</span>
                    </div>
                </div>
            </div>

            {{-- Page content --}}
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
                    <div class="logo flex items-center gap-2">
                        <span class="text-2xl">🌍</span>
                        <span class="font-bold text-xl text-[#29A1C4]">{{ __('messages.fra_portal') }}</span>
                    </div>
                </div>
                <nav class="flex-1 px-3 py-4 space-y-1">
                    <a href="{{ route('fra.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors hover:bg-teal-50 {{ request()->routeIs('fra.dashboard') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700' }}">
                        📊 {{ __('messages.dashboard') }}
                    </a>
                    <a href="{{ route('fra.lineup') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors hover:bg-teal-50 {{ request()->routeIs('fra.lineup') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700' }}">
                        📋 {{ __('messages.line_up') }}
                    </a>
                    <a href="{{ route('fra.selected') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors hover:bg-teal-50 {{ request()->routeIs('fra.selected') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700' }}">
                        ✅ {{ __('messages.selected') }}
                    </a>
                    <a href="{{ route('fra.onprocess') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors hover:bg-teal-50 {{ request()->routeIs('fra.onprocess') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700' }}">
                        ⏳ {{ __('messages.on_process') }}
                    </a>
                    <a href="{{ route('fra.cancelled') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors hover:bg-teal-50 {{ request()->routeIs('fra.cancelled') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700' }}">
                        ❌ {{ __('messages.cancelled') }}
                    </a>
                    <a href="{{ route('fra.account') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors hover:bg-teal-50 {{ request()->routeIs('fra.account') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700' }}">
                        ⚙️ {{ __('messages.account') }}
                    </a>
                </nav>
                <div class="border-t border-gray-200 px-4 py-3">
                    <div class="flex items-center gap-3 text-sm">
                        <div class="avatar placeholder">
                            <div class="w-8 h-8 rounded-full bg-[#29A1C4] text-white flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="truncate font-medium text-gray-800">{{ $user->name }}</p>
                        </div>
                        <form method="POST" action="{{ route('fra.logout') }}">
                            @csrf
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">{{ __('messages.sign_out') }}</button>
                        </form>
                    </div>
                </div>

                {{-- Mobile drawer language dropdown --}}
                <div class="border-t border-gray-100 px-4 py-3">
                    <p class="text-xs font-medium text-gray-500 mb-2">{{ __('messages.language') }}</p>
                    <form method="POST" action="{{ route('fra.account.language.update') }}" id="mobile-lang-form">
                        @csrf
                        <select name="language" onchange="this.form.submit()"
                            class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg bg-white text-gray-700 cursor-pointer focus:outline-none focus:border-[#29A1C4]">
                            @foreach ($langs as $code => $label)
                                <option value="{{ $code }}" {{ $currentLang === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Desktop layout --}}
    <div class="hidden lg:flex lg:flex-col min-h-screen">
        {{-- Top bar --}}
        <div class="bg-white border-b border-gray-200 px-6 py-2 flex items-center justify-between sticky top-0 z-30">
            <div class="logo flex items-center gap-2">
                <span class="text-xl">🌍</span>
                <span class="font-bold text-lg text-[#29A1C4]">{{ __('messages.fra_portal') }}</span>
            </div>

            {{-- Desktop language dropdown --}}
            <form method="POST" action="{{ route('fra.account.language.update') }}" id="desktop-lang-form" class="flex items-center">
                @csrf
                <select name="language" onchange="this.form.submit()"
                    class="text-xs px-2 py-1.5 border border-gray-300 rounded bg-white text-gray-700 cursor-pointer
                           focus:outline-none focus:border-[#29A1C4] focus:ring-1 focus:ring-[#29A1C4]/30">
                    @foreach ($langs as $code => $label)
                        <option value="{{ $code }}" {{ $currentLang === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>

            <div class="flex items-center gap-4">
                <div class="avatar placeholder">
                    <div class="w-8 h-8 rounded-full bg-[#29A1C4] text-white flex items-center justify-center text-sm font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
                <span class="text-sm text-gray-600 font-medium">{{ $user->name }}</span>
                <form method="POST" action="{{ route('fra.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">🚪 {{ __('messages.sign_out') }}</button>
                </form>
            </div>
        </div>

        {{-- Horizontal nav tabs --}}
        <nav class="bg-[#29A1C4] text-white">
            <div class="flex">
                <a href="{{ route('fra.dashboard') }}"
                   class="px-6 py-3 text-sm font-medium transition-colors hover:bg-white/20 {{ request()->routeIs('fra.dashboard') ? 'bg-white/25 font-semibold' : '' }}">
                    📊 {{ __('messages.dashboard') }}
                </a>
                <a href="{{ route('fra.lineup') }}"
                   class="px-6 py-3 text-sm font-medium transition-colors hover:bg-white/20 {{ request()->routeIs('fra.lineup') ? 'bg-white/25 font-semibold' : '' }}">
                    📋 {{ __('messages.line_up') }}
                </a>
                <a href="{{ route('fra.selected') }}"
                   class="px-6 py-3 text-sm font-medium transition-colors hover:bg-white/20 {{ request()->routeIs('fra.selected') ? 'bg-white/25 font-semibold' : '' }}">
                    ✅ {{ __('messages.selected') }}
                </a>
                <a href="{{ route('fra.onprocess') }}"
                   class="px-6 py-3 text-sm font-medium transition-colors hover:bg-white/20 {{ request()->routeIs('fra.onprocess') ? 'bg-white/25 font-semibold' : '' }}">
                    ⏳ {{ __('messages.on_process') }}
                </a>
                <a href="{{ route('fra.cancelled') }}"
                   class="px-6 py-3 text-sm font-medium transition-colors hover:bg-white/20 {{ request()->routeIs('fra.cancelled') ? 'bg-white/25 font-semibold' : '' }}">
                    ❌ {{ __('messages.cancelled') }}
                </a>
                <a href="{{ route('fra.account') }}"
                   class="px-6 py-3 text-sm font-medium transition-colors hover:bg-white/20 {{ request()->routeIs('fra.account') ? 'bg-white/25 font-semibold' : '' }}">
                    ⚙️ {{ __('messages.account') }}
                </a>
            </div>
        </nav>

        {{-- Page content --}}
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
            🌍 FRA Portal &bull; Powered by Agency Super
        </footer>
    </div>

    @yield('scripts')
</body>
</html>
