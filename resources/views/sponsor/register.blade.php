@extends('layouts.sponsor-guest')

@section('content')
<div class="absolute inset-0 overflow-hidden pointer-events-none">
    <div class="absolute -top-24 -left-24 w-80 h-80 bg-teal-400/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl"></div>
    <div class="absolute top-1/3 left-1/2 w-64 h-64 bg-cyan-300/10 rounded-full blur-3xl"></div>
</div>

<div class="w-full max-w-lg bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8 relative z-10">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-teal-500 to-blue-600 rounded-2xl shadow-lg mb-4">
            <span class="text-3xl">📝</span>
        </div>
        <h1 class="text-2xl font-extrabold text-gray-900">Sponsor Registration</h1>
        <p class="text-sm text-gray-500 mt-1">Register as a sponsor to hire workers</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('sponsor.register.post') }}">
        @csrf

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">🆔 ID Number <span class="text-red-500">*</span></label>
                <input type="text" name="id_number" value="{{ old('id_number') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                    placeholder="Your national ID or company ID number" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">🏢 Company Name <span class="text-red-500">*</span></label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                        placeholder="Company name" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">📧 Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                        placeholder="you@company.com" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">📱 WhatsApp/Viber No.</label>
                    <input type="text" name="contact_no" value="{{ old('contact_no') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                        placeholder="+63 9XX XXX XXXX">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">💬 Viber (optional)</label>
                    <input type="text" name="viber" value="{{ old('viber') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                        placeholder="Viber number or ID">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">📍 Address</label>
                <textarea name="address" rows="2"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                    placeholder="Full address">{{ old('address') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">🏙️ City</label>
                <input type="text" name="city" value="{{ old('city') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                    placeholder="City">
            </div>

            <div class="border-t border-gray-200 pt-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">🔑 Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                            placeholder="Min. 8 characters" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">✅ Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                            placeholder="Repeat password" required>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit"
            class="w-full mt-6 py-2.5 bg-gradient-to-r from-teal-500 to-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:from-teal-600 hover:to-blue-700 transition-all text-sm">
            ✅ Register as Sponsor
        </button>
    </form>

    <div class="mt-6 text-center">
        <p class="text-sm text-gray-500">
            Already registered?
            <a href="{{ route('sponsor.login') }}" class="text-teal-600 hover:text-teal-700 font-semibold">Sign in</a>
        </p>
    </div>
</div>
@endsection
