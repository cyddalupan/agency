<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — {{ app_brand_name() }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); }
        .glow { box-shadow: 0 0 30px rgba(59, 130, 246, 0.1); }
        .input-field { transition: border-color 0.2s, box-shadow 0.2s; }
        .input-field:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">

    <!-- Simple nav -->
    <nav class="border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center font-bold text-sm">AS</div>
                    <span class="font-bold text-lg">{{ app_brand_name() }}</span>
                </a>
                <a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition text-sm font-medium">Sign In</a>
            </div>
        </div>
    </nav>

    <div class="max-w-lg mx-auto px-4 py-16">
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-blue-600/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <h1 class="text-3xl font-bold mb-2">Register Your Agency</h1>
            <p class="text-gray-400">Get started with {{ app_brand_name() }}. Approval usually takes 24 hours.</p>
        </div>

        <!-- Success message -->
        @if (session('success'))
            <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 mb-6 text-green-400 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-8 glow">
            <form method="POST" action="{{ route('agency.register.post') }}" class="space-y-5">
                @csrf

                <!-- Agency Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Agency Name</label>
                    <input type="text" name="agency_name" value="{{ old('agency_name') }}" required
                        class="input-field w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-500 outline-none"
                        placeholder="Your Agency Name">
                    @error('agency_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Address</label>
                    <input type="text" name="address" value="{{ old('address') }}" required
                        class="input-field w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-500 outline-none"
                        placeholder="123 Main Street, Brgy. San Antonio">
                    @error('address') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- City -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">City</label>
                    <input type="text" name="city" value="{{ old('city') }}" required
                        class="input-field w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-500 outline-none"
                        placeholder="Makati City">
                    @error('city') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Agency Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="input-field w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-500 outline-none"
                        placeholder="agency@example.com">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Contact Person -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Contact Person</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" required
                        class="input-field w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-500 outline-none"
                        placeholder="Juan dela Cruz">
                    @error('contact_person') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Number of Branches -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Number of Branches</label>
                    <input type="number" name="num_branches" value="{{ old('num_branches') }}" required min="1"
                        class="input-field w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-500 outline-none"
                        placeholder="1">
                    @error('num_branches') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <hr class="border-white/10">

                <!-- Admin Username -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Admin Username</label>
                    <input type="text" name="admin_username" value="{{ old('admin_username') }}" required
                        class="input-field w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-500 outline-none"
                        placeholder="admin_username">
                    @error('admin_username') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Admin Password</label>
                    <input type="password" name="admin_password" required
                        class="input-field w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-500 outline-none"
                        placeholder="Enter password">
                    @error('admin_password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-xl transition glow">
                    Register Agency
                </button>

                <p class="text-center text-gray-500 text-sm mt-6">
                    Already registered?
                    <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 transition">Sign In</a>
                </p>
            </form>
        </div>
    </div>

    <footer class="border-t border-white/10 py-8 text-center text-gray-500 text-sm">
        <p>&copy; {{ date('Y') }} {{ app_brand_name() }}. All rights reserved.</p>
    </footer>

</body>
</html>
