@extends('godmode.layouts.app')

@section('title', 'Footer logo')

{{--
    Card 6b055a7a36a4a0bd098bdd29 — the platform-wide "Powered by Landas"
    footer logo. Write side only: the footer already renders
    app_brand_landas_logo(), which reads the one settings row this screen
    writes. Classes are copied from godmode/payments/index.blade.php and
    godmode/agencies/settings.blade.php; banners come from the layout.

    The preview matters more than the form: the mark lives at ~14-27 px tall
    in a 12 px text footer, which is where a wordmark dies. So there is one
    strip at real footer size and one labelled 3x copy for inspection only.
--}}
@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold">Footer logo</h1>
            <p class="text-sm opacity-70 mt-1">
                The "Powered by Landas" wordmark in every agency's footer. Platform-wide, not per agency
                (per-agency branding lives on each agency's own settings screen).
            </p>
        </div>
        <a href="{{ route('godmode.audit.index') }}" class="btn btn-sm btn-ghost">Audit trail</a>
    </div>

    {{-- ---------- Current ---------- --}}
    <section class="card bg-base-100 shadow-sm border border-base-300">
        <div class="card-body gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="card-title text-base m-0">Current</h2>
                @if ($hasRow)
                    <span class="badge badge-warning badge-sm">Overridden</span>
                @else
                    <span class="badge badge-ghost badge-sm">Using the shipped default</span>
                @endif
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-[10rem_1fr] gap-x-4 gap-y-2 text-sm">
                <dt class="opacity-70">Serving</dt>
                <dd class="break-all font-mono text-xs">{{ $effective }}</dd>

                <dt class="opacity-70">Stored value</dt>
                <dd>
                    @if ($hasRow && $rawValue !== '')
                        <code class="break-all text-xs bg-base-200 rounded px-1.5 py-0.5 select-all">{{ $rawValue }}</code>
                    @else
                        <span class="opacity-60">— no row. The helper falls back to the shipped asset.</span>
                    @endif
                </dd>

                <dt class="opacity-70">Row</dt>
                <dd class="text-xs">
                    @if ($hasRow)
                        <code>settings</code> · key <code>brand.landas_footer_logo</code> ·
                        <code>agency_id = {{ \App\Http\Controllers\GodMode\GodBrandingController::PLATFORM_SETTINGS_AGENCY_ID }}</code>
                        ({{ $agency->name }})
                        @if ($updatedAt)
                            · updated {{ $updatedAt }}
                        @endif
                    @else
                        <span class="opacity-60">None — reset means the row is deleted.</span>
                    @endif
                </dd>
            </dl>
        </div>
    </section>

    {{-- ---------- Preview ---------- --}}
    <section class="card bg-base-100 shadow-sm border border-base-300">
        <div class="card-body gap-4">
            <h2 class="card-title text-base m-0">Preview</h2>

            <div>
                <p class="text-[11px] uppercase tracking-wide opacity-60 mb-1">At real footer size</p>
                <div class="rounded-lg border border-base-300 bg-base-200 px-4 py-3">
                    {{-- Mirrors layouts/app.blade.php's footer: text-xs (12 px)
                         context, img at height:1.15em. If it is illegible here
                         it is illegible on every page. --}}
                    <span class="text-xs opacity-40 inline-flex items-center justify-center gap-2 align-middle">
                        <span>Powered by</span>
                        <img src="{{ $effective }}"
                             alt="Powered by Landas"
                             height="18"
                             style="height:1.15em;width:auto;display:inline-block;vertical-align:-0.2em;">
                    </span>
                </div>
                <p class="text-xs opacity-60 mt-1">
                    This is the size it renders in the footer. If it reads badly here, it reads badly everywhere.
                </p>
            </div>

            <div>
                <p class="text-[11px] uppercase tracking-wide opacity-60 mb-1">Same logo, zoomed 3x — inspection only</p>
                <div class="rounded-lg border border-dashed border-base-300 bg-base-200 px-4 py-3">
                    <img src="{{ $effective }}"
                         alt="Logo enlarged 3x for inspection"
                         style="height:3.45em;width:auto;display:block;max-width:100%;">
                </div>
                <p class="text-xs opacity-60 mt-1">
                    A bigger version purely to check edge quality and detail. Do not judge legibility here.
                </p>
            </div>

            <p class="text-xs opacity-60">
                The preview shows what the footer renders right now. A newly chosen file is shown here only after Save.
            </p>
        </div>
    </section>

    {{-- ---------- Change ---------- --}}
    <section class="card bg-base-100 shadow-sm border border-base-300">
        <form method="POST" action="{{ route('godmode.branding.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body gap-4">
                <h2 class="card-title text-base m-0">Change it</h2>

                <div>
                    <label class="label pb-1" for="logo">
                        <span class="label-text font-medium">Upload a file</span>
                    </label>
                    <input type="file" id="logo" name="logo" accept="image/png,image/jpeg"
                           class="file-input file-input-bordered file-input-sm w-full max-w-md">
                    <p class="text-xs opacity-60 mt-1">
                        PNG or JPEG, up to {{ intdiv($maxKb, 1024) }} MB. SVG is not accepted.
                        Wide wordmarks work best (the shipped one is 1349x192).
                    </p>
                    @error('logo')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="divider text-xs opacity-50 my-0">or</div>

                <div>
                    <label class="label pb-1" for="url">
                        <span class="label-text font-medium">Paste an image URL</span>
                    </label>
                    <input type="url" id="url" name="url" value="{{ old('url') }}"
                           placeholder="https://..." maxlength="500"
                           class="input input-bordered input-sm w-full max-w-xl">
                    <p class="text-xs opacity-60 mt-1">
                        The footer will use this address exactly as pasted. The address must return an image.
                    </p>
                    @error('url')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="card-actions justify-end">
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </div>
        </form>
    </section>

    {{-- ---------- Reset ---------- --}}
    <section class="card bg-base-100 shadow-sm border border-base-300">
        <form method="POST" action="{{ route('godmode.branding.reset') }}"
              onsubmit="return confirm('Put back the logo shipped with the app? This deletes the saved setting, not any file.');">
            @csrf
            <div class="card-body gap-3">
                <h2 class="card-title text-base m-0">Reset to default</h2>
                <p class="text-sm opacity-70">
                    Puts back the logo shipped with the app (<code class="text-xs">images/landas-logo.png</code>).
                    Deletes the saved setting row. Does not delete any file.
                </p>
                <div class="card-actions justify-end">
                    <button type="submit" class="btn btn-outline btn-sm" @disabled(! $hasRow)>Reset to default</button>
                </div>
            </div>
        </form>
    </section>
@endsection
