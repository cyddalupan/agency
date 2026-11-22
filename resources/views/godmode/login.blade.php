<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>God Mode — Sign in</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; }
    </style>
</head>
{{--
    Card 6b0341cf42a2071fc8b38ea5 — god mode login.

    No brand header, no register link, no forgot-password link, no link back to
    the public site: the only way here is to know the URL. The password field
    never re-renders a submitted value, and the form has no "remember me".
--}}
<body class="min-h-screen flex items-center justify-center bg-slate-950 p-4">
    <div class="w-full max-w-sm bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-8">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-100 tracking-tight">God Mode</h1>
            <p class="text-sm text-slate-500 mt-1">Operator access only.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-950 border border-red-900 text-red-300 rounded-xl text-sm" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('godmode.login.post') }}">
            @csrf
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-slate-400 mb-1.5">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                    required autofocus autocomplete="username"
                    class="w-full px-4 py-2.5 bg-slate-950 border border-slate-700 text-slate-100 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-slate-400 mb-1.5">Password</label>
                <input type="password" id="password" name="password"
                    required autocomplete="current-password"
                    class="w-full px-4 py-2.5 bg-slate-950 border border-slate-700 text-slate-100 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
            </div>
            <button type="submit"
                class="w-full px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold rounded-xl transition-colors">
                Sign in
            </button>
        </form>
    </div>
</body>
</html>
