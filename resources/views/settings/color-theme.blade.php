@extends('layouts.app')

@section('title', 'Color Theme')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('settings.index') }}" class="link link-secondary text-sm">← Back to Settings</a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold">🎨 Color Theme</h2>
        <p class="opacity-60 text-sm mt-1">
            Pick a color theme for the whole system. Everyone who logs in under
            <strong>{{ $agency->name }}</strong> sees the same palette.
        </p>
    </div>

    @if(session('success'))
        <div role="alert" class="alert alert-success mb-6 shadow-sm"><span>✅</span> {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="alert alert-error mb-6 shadow-sm">
            <span>❌</span>
            <ul class="list-disc pl-4 text-sm">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.color-theme.update') }}" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-6">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Choose a color theme</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($themes as $key => $theme)
                        @php($preview = $theme['preview'] ?? ['#c9a84c', '#5f9ea0', '#b5651d'])
                        <label class="cursor-pointer">
                            <input type="radio" name="color_theme" value="{{ $key }}"
                                   class="peer sr-only" @checked($selected === $key)>
                            <div class="rounded-box border-2 border-base-300 p-3 transition
                                        peer-checked:border-primary peer-checked:ring-2 peer-checked:ring-primary/30
                                        hover:border-primary/50">
                                <div class="flex items-center gap-2 mb-2">
                                    @foreach($preview as $hex)
                                        <span class="inline-block w-6 h-6 rounded-full border border-base-300"
                                              style="background: {{ $hex }};"></span>
                                    @endforeach
                                </div>
                                <div class="font-semibold text-sm">{{ $theme['label'] }}</div>
                                @if($selected === $key)
                                    <div class="text-xs text-primary mt-1">✓ Currently active</div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
                <p class="text-sm opacity-60 mt-2">
                    Choosing <em>Landas Gold (default)</em> keeps the system's built-in palette.
                </p>
            </fieldset>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">💾 Save Color Theme</button>
            </div>
        </div>
    </form>
</div>
@endsection
