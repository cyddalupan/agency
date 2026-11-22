<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'God Mode') · God Mode</title>
    {{-- Same asset pipeline as the tenant app (daisyUI). No CDN, so the console
         looks the same offline as it does in the operator's browser. --}}
    @vite('resources/css/app.css')
</head>
{{--
    Card 6b0344184136c21243a7af8b — the god-plane chrome.

    Deliberately does NOT extend layouts/app: that layout calls
    auth()->user(), the tenant navigation, module access and the org's own
    branding. A god operator has no agency and no modules, and every one of
    those calls would either render an agency's name on a cross-tenant screen
    or throw.
--}}
<body class="min-h-screen bg-base-200 text-base-content">
    <header class="bg-neutral text-neutral-content">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('godmode.dashboard') }}" class="font-extrabold tracking-tight">God Mode</a>
                <nav class="flex items-center gap-1 text-sm">
                    <a href="{{ route('godmode.dashboard') }}" class="px-3 py-1.5 rounded-lg hover:bg-neutral-content/10">Dashboard</a>
                    <a href="{{ route('godmode.agencies.index') }}" class="px-3 py-1.5 rounded-lg bg-neutral-content/15 font-semibold">Agencies</a>
                </nav>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs opacity-70">{{ auth('godmode')->user()?->username ?: auth('godmode')->user()?->email }}</span>
                <form method="POST" action="{{ route('godmode.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-xs btn-ghost">Sign out</button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-5">
        @if (session('status'))
            <div class="alert alert-success py-2" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error py-2" role="alert">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
