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
    Card 6b0341cf42a2071fc8b38ea5 — PLACEHOLDER dashboard.

    This card delivers the door, not the rooms. The four sibling cards (agencies
    CRUD, payments + dashboard, custom agency settings) fill this in. Do not add
    screens here.
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

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <p class="text-slate-300">
                Signed in as <span class="font-semibold text-slate-100">{{ $user->username ?: $user->email }}</span>.
            </p>
            <p class="text-sm text-slate-500 mt-2">
                This is a placeholder. The console pages ship separately.
            </p>
        </div>
    </div>
</body>
</html>
