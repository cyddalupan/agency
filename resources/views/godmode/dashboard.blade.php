<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>God Mode</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
{{--
    Card 6b0341cf42a2071fc8b38ea5 — the dashboard.

    Card 6b0344184136c21243a7af8b adds the Agencies entry point: /godmode is
    the control plane's index, so the first screen an operator lands on has to
    name the rooms that exist rather than describe itself as a placeholder.
--}}
<body class="min-h-screen bg-slate-950 text-slate-100 p-8">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold tracking-tight">God Mode</h1>
            <form method="POST" action="{{ route('godmode.logout') }}">
                @csrf
                <button type="submit"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-sm font-medium">
                    Sign out
                </button>
            </form>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 mb-6">
            <p class="text-slate-300">
                Signed in as <span class="font-semibold text-slate-100">{{ $user->username ?: $user->email }}</span>.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('godmode.agencies.index') }}"
               class="block bg-slate-900 border border-slate-800 hover:border-slate-600 rounded-2xl p-6 transition">
                <h2 class="text-lg font-semibold">Agencies</h2>
                <p class="text-sm text-slate-400 mt-1">
                    Every client on the platform: status, size and subdomain. Create, edit, archive.
                </p>
            </a>
        </div>
    </div>
</body>
</html>
