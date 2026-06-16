<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🔐 Employer Login — Agency Super</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-500 p-4">
    {{-- Floating decorative elements --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-20 -left-20 w-72 h-72 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-cyan-300/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/3 w-48 h-48 bg-emerald-300/10 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-sm bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8 relative z-10 animate-fade-in">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-emerald-600 to-teal-600 rounded-2xl shadow-lg mb-4">
                <span class="text-3xl">🏢</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900">Employer Portal</h1>
            <p class="text-sm text-gray-500 mt-1">👋 Sign in to manage your job postings</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
                <span>❌</span>
                <span>{{ $errors->first('email') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('employer.login.post') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">✉️ Email</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-lg opacity-50">📧</span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="you@company.com"
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">🔑 Password</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-lg opacity-50">🔒</span>
                    <input type="password" id="password" name="password" required
                        placeholder="••••••••"
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                </div>
            </div>
            <div class="mb-6 flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-gray-800 transition-colors">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    ✅ Remember me
                </label>
            </div>
            <button type="submit"
                class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white py-2.5 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                <span>🚀</span> Sign In
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-6">
            <a href="{{ route('portal.login') }}" class="text-emerald-600 hover:text-emerald-800 hover:underline">Applicant Portal</a>
            &middot;
            <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-800 hover:underline">Staff Login</a>
        </p>
    </div>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.5s ease-out;
        }
    </style>
</body>
</html>
