@extends('layouts.app')

@section('title', 'Site Display')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('settings.index') }}" class="link link-secondary text-sm">← Back to Settings</a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold">🔠 Site Display</h2>
        <p class="opacity-60 text-sm mt-1">
            Choose how this agency's site is displayed. This applies to the whole agency —
            everyone who logs in under <strong>{{ $agency->name }}</strong> sees the same style.
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

    <form method="POST" action="{{ route('settings.site-display.update') }}" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-6">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">ALL CAPS mode</legend>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="all_caps" value="1"
                           class="toggle toggle-primary mt-1"
                           @checked($allCaps)>
                    <span class="text-sm">
                        <span class="font-semibold">Display the entire site in ALL CAPS.</span>
                        <span class="block opacity-60">When on, all pages under this agency render
                        in uppercase. When off, the site uses normal case.</span>
                    </span>
                </label>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Banner color (replaces the gold banner)</legend>
                <div class="grid md:grid-cols-[1fr_auto] gap-4 items-center">
                    <div class="space-y-2">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="custom_banner_color" id="custom_banner_color" value="1"
                                   class="checkbox checkbox-primary mt-0.5"
                                   @checked(!empty($bannerColor))>
                            <span class="text-sm">
                                <span class="font-semibold">Use a custom banner color.</span>
                                <span class="block opacity-60">Untick to go back to the default gold.</span>
                            </span>
                        </label>
                        <div class="flex items-center gap-3 {{ empty($bannerColor) ? 'opacity-40 pointer-events-none' : '' }}" id="banner_color_wrap">
                            <input type="color" name="banner_color" id="banner_color"
                                   value="{{ $bannerColor ?? '#c9a84c' }}"
                                   class="h-10 w-16 cursor-pointer rounded border border-base-300 bg-white">
                            <span class="text-sm opacity-70" id="banner_hex_label">{{ $bannerColor ?? 'Default gold' }}</span>
                        </div>
                        <p class="text-sm opacity-60">
                            Pick <em>one</em> color — the gradient end is derived automatically.
                            Every gold banner, tinted header, and gold text across the whole site
                            follows this color. Leave it untouched for the default gold.
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="text-xs opacity-50 mb-1">Live preview</div>
                        <div id="banner_preview"
                             class="rounded-box h-20 w-56 shadow-sm border border-base-300 flex items-center justify-center"
                             style="background-image: linear-gradient(135deg, {{ $bannerColor ?? '#c9a84c' }}, {{ $bannerColor ? app_hex_shade($bannerColor, -0.28) : '#8c7030' }});">
                            <span class="font-semibold text-sm px-2 text-center"
                                  style="color: {{ $bannerColor && app_hex_is_light($bannerColor) ? '#1a2744' : '#ffffff' }};">
                                {{ $agency->name }}
                            </span>
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">💾 Save Display Setting</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const checkbox = document.getElementById('custom_banner_color');
    const input = document.getElementById('banner_color');
    const preview = document.getElementById('banner_preview');
    const label = document.getElementById('banner_hex_label');
    const wrap = document.getElementById('banner_color_wrap');
    if (!checkbox || !input || !preview) return;

    function shade(hex, pct) {
        const h = hex.replace('#', '');
        const full = h.length === 3 ? h.split('').map(c => c + c).join('') : h;
        const num = parseInt(full, 16);
        const amt = Math.round(2.55 * pct);
        const r = Math.min(255, Math.max(0, (num >> 16) + amt));
        const g = Math.min(255, Math.max(0, ((num >> 8) & 0xff) + amt));
        const b = Math.min(255, Math.max(0, (num & 0xff) + amt));
        return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }

    function isLight(hex) {
        const h = hex.replace('#', '');
        const full = h.length === 3 ? h.split('').map(c => c + c).join('') : h;
        const num = parseInt(full, 16);
        const r = (num >> 16) / 255, g = ((num >> 8) & 0xff) / 255, b = (num & 0xff) / 255;
        const lin = c => c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
        return 0.2126 * lin(r) + 0.7152 * lin(g) + 0.0722 * lin(b) > 0.5;
    }

    input.addEventListener('input', function () {
        const c1 = input.value;
        const c2 = shade(c1, -28); // darker by 28%, matching app_hex_shade
        preview.style.backgroundImage = 'linear-gradient(135deg, ' + c1 + ', ' + c2 + ')';
        const dark = isLight(c1) ? '#1a2744' : '#ffffff';
        preview.querySelector('span').style.color = dark;
        label.textContent = c1;
    });

    function renderCheckbox() {
        const on = checkbox.checked;
        wrap.classList.toggle('opacity-40', !on);
        wrap.classList.toggle('pointer-events-none', !on);
        if (!on) {
            // show default gold preview
            preview.style.backgroundImage = 'linear-gradient(135deg, #c9a84c, #8c7030)';
            preview.querySelector('span').style.color = '#ffffff';
            label.textContent = 'Default gold';
        } else {
            input.dispatchEvent(new Event('input'));
        }
    }

    checkbox.addEventListener('change', renderCheckbox);
    renderCheckbox();
})();
</script>
@endpush
