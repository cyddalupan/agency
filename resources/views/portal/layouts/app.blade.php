<!DOCTYPE html>
<html lang="en" data-theme="corporate">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Applicant Portal') — Agency Super</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-base-200 min-h-screen">
    {{-- Top nav --}}
    <div class="navbar bg-base-100 shadow-sm px-4">
        <div class="flex-1">
            <a href="{{ route('portal.dashboard') }}" class="btn btn-ghost text-xl font-bold">
                <span class="text-primary">▶</span> Applicant Portal
            </a>
        </div>
        @auth('applicant')
        <div class="flex-none gap-2">
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="flex items-center gap-2 cursor-pointer">
                    <div class="avatar placeholder">
                        <div class="w-8 rounded-full bg-primary text-primary-content text-xs font-bold">
                            {{ strtoupper(substr(Auth::guard('applicant')->user()->first_name, 0, 1)) }}{{ strtoupper(substr(Auth::guard('applicant')->user()->last_name, 0, 1)) }}
                        </div>
                    </div>
                    <span class="text-sm font-medium hidden sm:inline">{{ Auth::guard('applicant')->user()->full_name }}</span>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-10 w-52 p-2 shadow-lg">
                    <li><a href="{{ route('portal.dashboard') }}">🏠 Dashboard</a></li>
                    <li><a href="{{ route('portal.jobs.index') }}">📋 Job Listings</a></li>
                    <li><a href="{{ route('portal.profile') }}">👤 My Profile</a></li>
                    <li class="divider my-1"></li>
                    <li>
                        <form method="POST" action="{{ route('portal.logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left">🚪 Sign Out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        @endauth
    </div>

    {{-- Page content --}}
    <main class="container mx-auto px-4 py-6">
        @yield('content')
    </main>
</body>
</html>
