<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🔐 Login — {{ app_brand_name() }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-600 via-purple-600 to-pink-500 p-4">
    {{-- Floating decorative elements --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-20 -left-20 w-72 h-72 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-pink-300/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/3 w-48 h-48 bg-blue-300/10 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-sm bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8 relative z-10 animate-fade-in">
        {{-- Logo: agency icon if uploaded, otherwise fallback emoji --}}
        <div class="text-center mb-6">
            @php $brandLogo = app_brand_logo(); @endphp
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl shadow-lg mb-4 overflow-hidden{{ $brandLogo ? ' bg-transparent' : ' bg-gradient-to-br from-blue-600 to-purple-600' }}">
                @if ($brandLogo)
                    <img src="{{ $brandLogo }}" alt="{{ app_brand_name() }} icon"
                         class="w-full h-full object-contain p-1">
                @else
                    <span class="text-3xl">🏢</span>
                @endif
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900">{{ app_brand_name() }}</h1>
            <p class="text-sm text-gray-500 mt-1">👋 Welcome back! Sign in to continue</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
                <span>❌</span>
                <span>{{ $errors->first('email') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">✉️ Email</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-lg opacity-50">📧</span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="you@example.com"
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">🔑 Password</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-lg opacity-50">🔒</span>
                    <input type="password" id="password" name="password" required
                        placeholder="••••••••"
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                </div>
            </div>
            <div class="mb-6 flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-gray-800 transition-colors">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    ✅ Remember me
                </label>
                <a href="#" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">Forgot password?</a>
            </div>
            <button type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-2.5 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                <span>🚀</span> Sign In
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-6">
            🔒 Secure login • Agency Management System
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
