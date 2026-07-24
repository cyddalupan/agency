@php $universe = config('app.universe', 1); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🔐 Agent Login — {{ app_brand_name() }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>{{ app_brand_favicon_emoji() }}</text></svg>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>{{ app_brand_favicon_emoji() }}</text></svg>">
    @vite('resources/css/app.css')
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">
    <div class="card bg-base-100 shadow-xl w-full max-w-sm">
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="text-4xl mb-2">🎯</div>
                <h1 class="text-2xl font-bold">Agent Login</h1>
                <p class="text-sm opacity-60">{{ app_brand_name() }}</p>
            </div>

            @if ($errors->any())
            <div class="alert alert-error text-sm mb-4">
                <span>❌</span>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            @if (session('success'))
            <div class="alert alert-success text-sm mb-4">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('agent.login.submit') }}">
                @csrf
                <fieldset class="fieldset mb-3">
                    <legend class="fieldset-legend">📧 Email</legend>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="input w-full" placeholder="agent@email.com" required autofocus>
                </fieldset>
                <fieldset class="fieldset mb-3">
                    <legend class="fieldset-legend">🔑 Password</legend>
                    <input type="password" name="password"
                        class="input w-full" placeholder="••••••••" required>
                </fieldset>
                <div class="flex items-center gap-2 mb-4">
                    <input type="checkbox" name="remember" id="remember" class="checkbox checkbox-sm">
                    <label for="remember" class="text-sm">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary w-full">
                    <span>🔓</span> Login
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="link link-secondary text-sm">← Admin Login</a>
            </div>
        </div>
    </div>
</body>
</html>
