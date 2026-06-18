<!DOCTYPE html>
<html lang="en" data-theme="corporate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Agency Super') — {{ tenant_agency()?->name ?? 'Super Admin' }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💼</text></svg>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💼</text></svg>">
    @vite('resources/css/app.css')
    <style>
        /* Print-friendly styles */
        @media print {
            body { background: white !important; }
            .no-print, .navbar, .drawer-side, footer, .alert { display: none !important; }
            .drawer-content { margin-left: 0 !important; padding: 0 !important; }
            main { padding: 0 !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; break-inside: avoid; }
            .table th { background: #f5f5f5 !important; color: #000 !important; }
            a { color: #000 !important; text-decoration: underline; }
            @page { margin: 1.5cm; }
            .print-only { display: block !important; }
        }
        .print-only { display: none; }
        @media print { .print-only { display: block; } }
    </style>
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
                    {{-- Notification bell with dropdown --}}
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-square relative notification-bell">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @php
                                $unreadCount = auth()->user()->unreadNotifications()->count();
                                $recentUnread = auth()->user()->unreadNotifications()->latest()->limit(5)->get();
                            @endphp
                            @if ($unreadCount > 0)
                            <span class="notification-badge absolute -top-1 -right-1 badge badge-xs badge-error badge-outline">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                        </label>
                        <ul tabindex="0" class="dropdown-content notification-dropdown menu p-2 shadow-lg bg-base-100 rounded-box w-80 mt-2 border border-base-200">
                            @if ($recentUnread->count() > 0)
                                @foreach ($recentUnread as $note)
                                <li class="mb-1 notification-item">
                                    <div class="flex items-start gap-2 px-2 py-2 rounded-lg hover:bg-base-200 transition-colors">
                                        <span class="text-lg shrink-0">
                                            @switch($note->type)
                                                @case('status_change') 🔄 @break
                                                @case('approval') ✅ @break
                                                @case('bill_due') 💰 @break
                                                @default 🔔
                                            @endswitch
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm truncate">{{ $note->data['message'] ?? '' }}</p>
                                            <p class="text-xs opacity-50">{{ $note->created_at->diffForHumans() }}</p>
                                        </div>
                                        <form method="POST" action="{{ route('notifications.mark-as-read', $note) }}" class="shrink-0">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs text-primary" title="Mark as read">✓</button>
                                        </form>
                                    </div>
                                </li>
                                @endforeach
                                <li class="menu-title px-2 pt-2 border-t border-base-200 mt-1">
                                    <div class="flex items-center justify-between">
                                        <form method="POST" action="{{ route('notifications.mark-all-as-read') }}" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs text-primary">Mark all as read</button>
                                        </form>
                                        <a href="{{ route('notifications.index') }}" class="btn btn-ghost btn-xs">View all</a>
                                    </div>
                                </li>
                            @else
                                <li class="no-notifications">
                                    <div class="text-center py-6 opacity-50">
                                        <div class="text-3xl mb-2">🔔</div>
                                        <p class="text-sm">No new notifications</p>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="avatar placeholder">
                        <div class="w-9 h-9 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-bold">
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
                        <a href="{{ route('settings.index') }}"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                              {{ request()->routeIs('settings.*') ? 'active bg-base-300 shadow-sm' : 'hover:bg-base-300/60' }}">
                        <span class="text-lg">⚙️</span>
                        Settings
                    </a>
                        <a href="{{ route('reports.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('reports.index') ? 'active bg-base-300 shadow-sm' : 'hover:bg-base-300/60' }}">
                            <span class="text-lg">📄</span>
                            Reports
                        </a>
                    </div>
                </nav>

                {{-- Sidebar footer --}}
                <div class="border-t border-base-300 px-3 py-3 bg-base-300/50">
                    <div class="flex items-center gap-3 px-3 py-2 text-sm">
                        <div class="avatar placeholder">
                            <div class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold">
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