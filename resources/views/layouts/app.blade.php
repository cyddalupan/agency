<!DOCTYPE html>
<html lang="en" data-theme="corporate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Agency Super') — {{ tenant_agency()?->name ?? 'Super Admin' }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💼</text></svg>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💼</text></svg>">
    @vite('resources/css/app.css')
</head>
<body class="bg-base-200 bg-noise min-h-screen">

    @auth
    {{-- Drawer layout: sidebar on desktop, overlay on mobile --}}
    <div class="drawer lg:drawer-open">

        {{-- Hidden checkbox toggles sidebar on mobile --}}
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />

        {{-- ============================================================ --}}
        {{-- MAIN CONTENT AREA                                            --}}
        {{-- ============================================================ --}}
        <div class="drawer-content flex flex-col min-h-screen">
            {{-- Top bar --}}
            <div class="navbar bg-base-100/80 backdrop-blur-sm border-b border-base-200 px-4 lg:px-6 min-h-14 sticky top-0 z-30">
                <div class="flex-1 flex items-center gap-3">
                    {{-- Hamburger button (mobile only) --}}
                    <label for="sidebar-drawer" class="btn btn-ghost btn-square lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </label>
                    <span class="font-semibold text-lg truncate">
                        @yield('title', 'Agency Super')
                    </span>
                </div>
                <div class="flex-none flex items-center gap-4">
                    {{-- Corporate theme locked — no toggle needed --}}
                    <div class="avatar placeholder">
                        <div class="w-8 rounded-full bg-primary text-primary-content text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>
                    <span class="text-sm opacity-70 hidden sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm text-error">🚪 Logout</button>
                    </form>
                </div>
            </div>

            {{-- Session flash messages --}}
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

            {{-- Footer --}}
            <footer class="px-4 lg:px-6 py-3 text-center text-xs opacity-40 border-t border-base-300">
                ⚡ Agency Super &bull; Powered by TOYBITS
            </footer>
        </div>

        {{-- ============================================================ --}}
        {{-- SIDEBAR                                                     --}}
        {{-- ============================================================ --}}
        <div class="drawer-side z-40">
            {{-- Overlay backdrop (click to close on mobile) --}}
            <label for="sidebar-drawer" class="drawer-overlay"></label>

            <div class="flex flex-col min-h-full w-64 bg-gradient-to-b from-base-200 to-base-300 text-base-content">
                {{-- Sidebar brand with gradient accent --}}
                <div class="px-6 py-5 bg-gradient-to-r from-primary/10 to-secondary/10 border-b border-base-300">
                    <a href="{{ auth()->user()->agency_id ? route('agency.dashboard') : route('dashboard') }}" class="flex items-center gap-2">
                        <span class="text-2xl">⚡</span>
                        <span class="font-bold text-xl bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                            {{ tenant_agency()?->name ?? 'Agency Super' }}
                        </span>
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    <p class="px-3 text-xs opacity-40 uppercase tracking-wider font-semibold mb-2">📋 Main</p>

                    <a href="{{ auth()->user()->agency_id ? route('agency.dashboard') : route('dashboard') }}"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                              {{ request()->routeIs('dashboard') || request()->routeIs('agency.dashboard') ? 'active bg-base-300 shadow-sm' : 'hover:bg-base-300/60' }}">
                        <span class="text-lg">🏠</span>
                        Dashboard
                    </a>

                    <a href="{{ route('applicants.index') }}"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                              {{ request()->routeIs('applicants.*') ? 'active bg-base-300 shadow-sm' : 'hover:bg-base-300/60' }}">
                        <span class="text-lg">👥</span>
                        Applicants
                    </a>

                    <a href="{{ route('employers.index') }}"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                              {{ request()->routeIs('employers.*') ? 'active bg-base-300 shadow-sm' : 'hover:bg-base-300/60' }}">
                        <span class="text-lg">🏢</span>
                        Employers
                    </a>

                    <a href="{{ route('marketing-agencies.index') }}"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                              {{ request()->routeIs('marketing-agencies.*') ? 'active bg-base-300 shadow-sm' : 'hover:bg-base-300/60' }}">
                        <span class="text-lg">📢</span>
                        Marketing
                    </a>

                    {{-- Only show for agency users --}}
                    @if (auth()->user()->agency_id || tenant_agency())
                    <div class="pt-4 mt-4 border-t border-base-300">
                        <p class="px-3 text-xs opacity-40 uppercase tracking-wider font-semibold mb-2">🏢 Agency</p>
                        <a href="{{ route('agency.dashboard') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('agency.dashboard') ? 'active bg-base-300 shadow-sm' : 'hover:bg-base-300/60' }}">
                            <span class="text-lg">📊</span>
                            Agency Dashboard
                        </a>
                        <a href="{{ route('custom-fields.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('custom-fields.*') ? 'active bg-base-300 shadow-sm' : 'hover:bg-base-300/60' }}">
                            <span class="text-lg">⚙️</span>
                            Custom Fields
                        </a>
                    </div>
                    @endif

                    <div class="pt-4 mt-4 border-t border-base-300">
                        <p class="px-3 text-xs opacity-40 uppercase tracking-wider font-semibold mb-2">⚙️ System</p>
                        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-base-300/60 transition-colors opacity-60">
                            <span class="text-lg">⚙️</span>
                            Settings
                        </a>
                        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-base-300/60 transition-colors opacity-60">
                            <span class="text-lg">📄</span>
                            Reports
                        </a>
                    </div>
                </nav>

                {{-- Sidebar footer --}}
                <div class="border-t border-base-300 px-3 py-3 bg-base-300/50">
                    <div class="flex items-center gap-3 px-3 py-2 text-sm">
                        <div class="avatar placeholder">
                            <div class="w-7 rounded-full bg-primary text-primary-content text-xs font-bold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="truncate font-medium">{{ auth()->user()->name }}</p>
                            <p class="text-xs opacity-50 truncate">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endauth

    {{-- Guest layout (login page etc.) --}}
    @guest
    <main class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-base-200 via-base-100 to-base-300">
        @yield('content')
    </main>
    @endguest
</body>
</html>