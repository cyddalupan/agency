<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Super — Recruitment Agency Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); }
        .glow { box-shadow: 0 0 40px rgba(59, 130, 246, 0.15); }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .pulse-dot { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">

    <!-- Navigation -->
    <nav class="border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center font-bold text-sm">AS</div>
                    <span class="font-bold text-lg">Agency Super</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition text-sm font-medium">Sign In</a>
                    <a href="{{ route('agency.register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">Register Agency</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-16 text-center">
        <div class="inline-flex items-center gap-2 bg-blue-500/10 border border-blue-500/20 rounded-full px-4 py-1.5 text-sm text-blue-400 mb-8">
            <span class="w-2 h-2 bg-blue-400 rounded-full pulse-dot"></span>
            Built for Philippine recruitment agencies
        </div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight mb-6">
            Manage Your Agency<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400">From One Dashboard</span>
        </h1>
        <p class="text-gray-400 text-lg max-w-2xl mx-auto mb-10">
            Applicant tracking, employer management, billing, and reporting — everything your 
            recruitment agency needs to scale operations.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('agency.register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3.5 rounded-xl text-lg font-semibold transition glow inline-flex items-center gap-2">
                Register Your Agency
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="{{ route('login') }}" class="text-gray-400 hover:text-white border border-white/10 px-8 py-3.5 rounded-xl text-lg font-semibold transition inline-flex items-center gap-2">
                Sign In
            </a>
        </div>
    </section>

    <!-- Features -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center mb-14">
            <h2 class="text-3xl font-bold mb-4">Everything You Need</h2>
            <p class="text-gray-400 max-w-xl mx-auto">One platform to manage applicants, employers, billing, and compliance.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 feature-card transition">
                <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Applicant Management</h3>
                <p class="text-gray-400 text-sm">Track applicants through the pipeline. Upload documents, manage certifications, and communicate with candidates.</p>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 feature-card transition">
                <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Employer Management</h3>
                <p class="text-gray-400 text-sm">Manage employer accounts, job postings, and track placements. Generate billing and statements.</p>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 feature-card transition">
                <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h3v6m6 6a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Billing & Payments</h3>
                <p class="text-gray-400 text-sm">Automated billing, statement of accounts, payment tracking, and commission management all in one place.</p>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 feature-card transition">
                <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Reports & Analytics</h3>
                <p class="text-gray-400 text-sm">Comprehensive reporting on placements, revenue, and agency performance metrics.</p>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 feature-card transition">
                <div class="w-12 h-12 bg-rose-500/20 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Compliance Ready</h3>
                <p class="text-gray-400 text-sm">Built with DOLE compliance in mind. Track contracts, documentation, and regulatory requirements.</p>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 feature-card transition">
                <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Mobile-Ready</h3>
                <p class="text-gray-400 text-sm">Applicant portal and agency dashboard work seamlessly on mobile devices.</p>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="bg-white/5 border-t border-white/10 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-bold mb-4">Get Started in 3 Steps</h2>
                <p class="text-gray-400 max-w-xl mx-auto">Simple registration process to get your agency up and running.</p>
            </div>
            <div class="grid sm:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                    <h3 class="text-lg font-semibold mb-2">Register Your Agency</h3>
                    <p class="text-gray-400 text-sm">Fill in your agency details and create an admin account.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                    <h3 class="text-lg font-semibold mb-2">Get Approved</h3>
                    <p class="text-gray-400 text-sm">Our team reviews and activates your account within 24 hours.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                    <h3 class="text-lg font-semibold mb-2">Start Managing</h3>
                    <p class="text-gray-400 text-sm">Add applicants, employers, and start tracking everything.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <div class="bg-gradient-to-r from-blue-600/20 to-cyan-600/20 border border-blue-500/20 rounded-3xl p-12 glow">
            <h2 class="text-3xl font-bold mb-4">Ready to Streamline Your Agency?</h2>
            <p class="text-gray-400 mb-8 max-w-lg mx-auto">Join agencies already using Agency Super to manage recruitment operations.</p>
            <a href="{{ route('agency.register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3.5 rounded-xl text-lg font-semibold transition inline-flex items-center gap-2">
                Register Free
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-8 text-center text-gray-500 text-sm">
        <p>&copy; {{ date('Y') }} Agency Super. All rights reserved.</p>
    </footer>

</body>
</html>
