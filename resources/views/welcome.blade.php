<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Agency Super') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    {{-- Navigation --}}
    <nav class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                        <span class="text-xl">🏢</span>
                    </div>
                    <span class="text-xl font-extrabold text-gray-900">Agency <span class="text-blue-600">Super</span></span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('agency.login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 hover:underline transition-colors">🏛️ Agency Portal</a>
                    <a href="{{ route('login') }}" class="btn-nav bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-5 py-2 rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200 inline-flex items-center gap-1.5">
                        <span>🔐</span> Admin Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <main class="pt-32 pb-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16 animate-float-up">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl shadow-2xl mb-6">
                    <span class="text-4xl">🏢</span>
                </div>
                <h1 class="text-5xl lg:text-7xl font-black text-gray-900 mb-4 leading-tight">
                    Agency <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Super</span>
                </h1>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-8 leading-relaxed">
                    🚀 Your all-in-one recruitment management platform — track applicants, manage employers, 
                    fill job positions, and grow your agency.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('login') }}" class="px-8 py-3.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-2xl font-bold text-lg shadow-xl hover:shadow-2xl transition-all duration-200 inline-flex items-center gap-2">
                        <span>🔐</span> Admin Login →
                    </a>
                    <a href="{{ route('agency.login') }}" class="px-8 py-3.5 bg-white border-2 border-gray-200 hover:border-blue-300 text-gray-700 hover:text-blue-700 rounded-2xl font-semibold text-lg shadow-sm hover:shadow-lg transition-all duration-200 inline-flex items-center gap-2">
                        <span>🏛️</span> Agency Portal →
                    </a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-20 animate-float-up stagger-1">
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-gray-100 text-center hover:shadow-xl transition-all hover:-translate-y-0.5">
                    <span class="text-4xl block mb-3">👥</span>
                    <div class="text-3xl font-black text-gray-900">100+</div>
                    <div class="text-sm text-gray-500 mt-1">Applicants Managed</div>
                </div>
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-gray-100 text-center hover:shadow-xl transition-all hover:-translate-y-0.5">
                    <span class="text-4xl block mb-3">🏢</span>
                    <div class="text-3xl font-black text-gray-900">50+</div>
                    <div class="text-sm text-gray-500 mt-1">Employer Partners</div>
                </div>
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-gray-100 text-center hover:shadow-xl transition-all hover:-translate-y-0.5">
                    <span class="text-4xl block mb-3">💼</span>
                    <div class="text-3xl font-black text-gray-900">200+</div>
                    <div class="text-sm text-gray-500 mt-1">Positions Filled</div>
                </div>
            </div>

            {{-- Features --}}
            <div class="text-center mb-12">
                <span class="inline-block px-4 py-1.5 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold mb-3">✨ Powerful Features</span>
                <h2 class="text-3xl lg:text-4xl font-black text-gray-900 mb-4">Everything You Need to <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Succeed</span></h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-all group">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <span class="text-2xl">👥</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Applicant Tracking</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">📋 Manage applicant profiles, documents, and status updates all in one place. Track passport, education, certificates, and more.</p>
                </div>
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-all group">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <span class="text-2xl">🏢</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Employer Management</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">📊 Manage client companies, their contact information, and job requirements. Keep everything organized by country and position.</p>
                </div>
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-all group">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <span class="text-2xl">💼</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Job Position Matching</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">🎯 Match applicants to open positions. Track salary, slots, status, and hiring progress for each job position.</p>
                </div>
            </div>

            {{-- CTA --}}
            <div class="mt-20 text-center bg-gradient-to-r from-blue-600 via-purple-600 to-pink-500 rounded-3xl p-12 shadow-2xl animate-float-up stagger-2">
                <span class="text-5xl block mb-4">🚀</span>
                <h2 class="text-3xl lg:text-4xl font-black text-white mb-4">Ready to Get Started?</h2>
                <p class="text-lg text-white/80 mb-8 max-w-lg mx-auto">Sign in to your dashboard and start managing your recruitment workflow today.</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('login') }}" class="px-8 py-3.5 bg-white text-blue-700 rounded-2xl font-bold text-lg shadow-lg hover:shadow-xl transition-all inline-flex items-center gap-2">
                        <span>🔐</span> Admin Login →
                    </a>
                    <a href="{{ route('agency.login') }}" class="px-8 py-3.5 bg-white/10 border-2 border-white/30 text-white rounded-2xl font-bold text-lg hover:bg-white/20 transition-all inline-flex items-center gap-2">
                        <span>🏛️</span> Agency Portal →
                    </a>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200/60 py-8 px-4">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500">
            <div class="flex items-center gap-2">
                <span>🏢</span>
                <span>&copy; {{ date('Y') }} Agency Super. All rights reserved.</span>
            </div>
            <div class="flex items-center gap-6">
                <span>🔒 Secure Platform</span>
                <span>⚡ Built with Laravel</span>
                <span>❤️ Made with care</span>
            </div>
        </div>
    </footer>

    <style>
        @keyframes float-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-float-up {
            animation: float-up 0.6s ease-out;
        }
        .stagger-1 { animation-delay: 0.15s; }
        .stagger-2 { animation-delay: 0.3s; }
        .btn-nav {
            display: inline-flex;
            align-items: center;
        }
    </style>
</body>
</html>
