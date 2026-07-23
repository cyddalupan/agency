<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Submitted — Agency Super</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); }
        .glow { box-shadow: 0 0 30px rgba(59, 130, 246, 0.1); }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">
    <nav class="border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center font-bold text-sm">AS</div>
                    <span class="font-bold text-lg">Agency Super</span>
                </a>
            </div>
        </div>
    </nav>
    <div class="max-w-lg mx-auto px-4 py-20 text-center">
        <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold mb-4">Registration Submitted!</h1>
        <p class="text-gray-400 mb-2">Your agency registration has been received.</p>
        <p class="text-gray-500 text-sm mb-8">We'll review your application and notify you once approved.</p>
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-blue-400 hover:text-blue-300 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Home
        </a>
    </div>
    <footer class="border-t border-white/10 py-8 text-center text-gray-500 text-sm fixed bottom-0 w-full">
        <p>&copy; {{ date('Y') }} Agency Super. All rights reserved.</p>
    </footer>
</body>
</html>
