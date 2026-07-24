@php $universe = config('app.universe', 1); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $universe == 2 ? 'universe-2' : 'corporate' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Employer Portal')</title>
    @if($universe == 2)
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⛩️</text></svg>" type="image/svg+xml">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚡</text></svg>" type="image/svg+xml">
    @endif
    @vite('resources/css/app.css')
</head>
<body class="bg-base-200 bg-noise min-h-screen">

    {{-- Guest layout (employer login) --}}
    <main class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-base-200 via-base-100 to-base-300">
        @yield('content')
    </main>

</body>
</html>
