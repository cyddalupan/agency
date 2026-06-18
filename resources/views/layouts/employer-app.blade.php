<!DOCTYPE html>
<html lang="en" data-theme="corporate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Employer Portal') — {{ $employer->name ?? 'Employer' }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏢</text></svg>" type="image/svg+xml">
    @vite('resources/css/app.css')
</head>
<body class="bg-base-200 bg-noise min-h-screen">

    <div class="drawer lg:drawer-open">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex flex-col min-h-screen">
            {{-- Top bar --}}
            <div class="navbar bg-base-100/80 backdrop-blur-sm border-b border-base-200 px-4 lg:px-6 min-h-14 sticky top-0 z-30">
                <div class="flex-1 flex items-center gap-3">
                    <label for="sidebar-drawer" class="btn btn-ghost btn-square lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </label>
                    <span class="font-semibold text-lg truncate">
                        @yield('title', 'Employer Portal')
                    </span>
                </div>
                <div class="flex-none flex items-center gap-4">
                    <div class="avatar placeholder">
                        <div class="w-9 h-9 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-bold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                    <span class="text-sm opacity-70 hidden sm:inline">{{ $user->name }}</span>
                    <form method="POST" action="{{ route('employer.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm text-error">🚪 Logout</button>
                    </form>
                </div>
            </div>

            {{-- Flash messages --}}
            @if (session('success'))
            <div class="px-4 lg:px-6 pt-4">
                <div role="alert" class="alert alert-success text-sm shadow-sm">
                    <span>✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            @endif
            @if (session('error'))
            <div class="px-4 lg:px-6 pt-4">
                <div role="alert" class="alert alert-error text-sm shadow-sm">
                    <span>❌</span>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
            @endif

            {{-- Page content --}}
            <main class="flex-1 p-4 lg:p-6 overflow-x-auto">
                @yield('content')
            </main>

            <footer class="px-4 lg:px-6 py-3 text-center text-xs opacity-40 border-t border-base-300">
                🏢 Employer Portal &bull; Powered by Agency Super
            </footer>
        </div>

        {{-- Sidebar --}}
        <div class="drawer-side z-40">
            <label for="sidebar-drawer" class="drawer-overlay"></label>
            <div class="flex flex-col min-h-full w-64 bg-gradient-to-b from-base-200 to-base-300 text-base-content">
                <div class="px-6 py-5 bg-gradient-to-r from-primary/10 to-secondary/10 border-b border-base-300">
                    <a href="{{ route('employer.dashboard') }}" class="flex items-center gap-2">
                        <span class="text-2xl">🏢</span>
                        <span class="font-bold text-xl bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                            {{ $employer->name }}
                        </span>
                    </a>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    <p class="px-3 text-xs opacity-40 uppercase tracking-wider font-semibold mb-2">📋 Menu</p>

                    <a href="{{ route('employer.dashboard') }}"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                              {{ request()->routeIs('employer.dashboard') ? 'active bg-base-300 shadow-sm' : 'hover:bg-base-300/60' }}">
                        <span class="text-lg">🏠</span>
                        Dashboard
                    </a>

                    <a href="{{ route('employer.dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-base-300/60 transition-colors opacity-60">
                        <span class="text-lg">👥</span>
                        Applicants
                    </a>

                    <a href="{{ route('employer.dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-base-300/60 transition-colors opacity-60">
                        <span class="text-lg">📄</span>
                        Reports
                    </a>
                </nav>

                <div class="border-t border-base-300 px-3 py-3 bg-base-300/50">
                    <div class="flex items-center gap-3 px-3 py-2 text-sm">
                        <div class="avatar placeholder">
                            <div class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="truncate font-medium">{{ $user->name }}</p>
                            <p class="text-xs opacity-50 truncate">{{ $user->email ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
