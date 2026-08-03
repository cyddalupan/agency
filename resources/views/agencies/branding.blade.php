@extends('layouts.app')

@section('title', 'Branding — ' . $agency->name)

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('agencies.index') }}" class="link link-hover text-sm opacity-60 mb-2 inline-block">
            ← Back to Agencies
        </a>
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>🎨</span> Branding: {{ $agency->name }}
        </h2>
    </div>

    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success mb-4">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Current Branding Preview --}}
    <div class="bg-base-100 rounded-box shadow-sm border p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Current Branding</h3>
        <div class="flex items-center gap-4 flex-wrap">
            @if ($agency->logo)
                <div class="text-center">
                    <p class="text-sm opacity-60 mb-1">Logo</p>
                    <img src="{{ Storage::url($agency->logo) }}" alt="Agency Logo"
                         class="max-w-[200px] max-h-[100px] object-contain border rounded p-1">
                    <p class="text-xs opacity-40 mt-1">{{ $agency->logo }}</p>
                </div>
            @else
                <div class="text-center opacity-40">
                    <p class="text-sm">No logo uploaded</p>
                </div>
            @endif

            @php $settings = $agency->settings ?? []; @endphp
            @if (!empty($settings['primary_color']) || !empty($settings['secondary_color']))
                <div class="flex gap-3 items-center">
                    @if (!empty($settings['primary_color']))
                        <div class="text-center">
                            <p class="text-sm opacity-60 mb-1">Primary</p>
                            <div class="w-10 h-10 rounded border" style="background-color: {{ $settings['primary_color'] }}"></div>
                            <p class="text-xs opacity-40 mt-1">{{ $settings['primary_color'] }}</p>
                        </div>
                    @endif
                    @if (!empty($settings['secondary_color']))
                        <div class="text-center">
                            <p class="text-sm opacity-60 mb-1">Secondary</p>
                            <div class="w-10 h-10 rounded border" style="background-color: {{ $settings['secondary_color'] }}"></div>
                            <p class="text-xs opacity-40 mt-1">{{ $settings['secondary_color'] }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Branding Form --}}
    <div class="bg-base-100 rounded-box shadow-sm border p-6">
        <form action="{{ route('agencies.branding.update', $agency) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Company Name & Address --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="form-control">
                    <label class="label" for="name">
                        <span class="label-text">Company Name</span>
                        <span class="label-text-alt opacity-60">Shown on dashboard</span>
                    </label>
                    <input type="text" name="name" id="name"
                           class="input input-bordered @error('name') input-error @enderror"
                           value="{{ old('name', $agency->name) }}"
                           placeholder="Company Name">
                    @error('name')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label" for="address">
                        <span class="label-text">Company Address</span>
                    </label>
                    <input type="text" name="address" id="address"
                           class="input input-bordered @error('address') input-error @enderror"
                           value="{{ old('address', $agency->address) }}"
                           placeholder="Address">
                    @error('address')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>
            </div>

            {{-- Logo Upload --}}
            <div class="form-control mb-6">
                <label class="label" for="logo">
                    <span class="label-text">Logo</span>
                    <span class="label-text-alt opacity-60">PNG, JPG, SVG, WebP (max 2MB)</span>
                </label>
                <input type="file" name="logo" id="logo"
                       class="file-input file-input-bordered w-full @error('logo') file-input-error @enderror"
                       accept="image/png,image/jpeg,image/svg+xml,image/webp">
                @error('logo')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            {{-- Favicon Upload --}}
            <div class="form-control mb-6">
                <label class="label" for="favicon">
                    <span class="label-text">Favicon</span>
                    <span class="label-text-alt opacity-60">ICO, PNG, JPG, SVG (max 512KB)</span>
                </label>
                <input type="file" name="favicon" id="favicon"
                       class="file-input file-input-bordered w-full @error('favicon') file-input-error @enderror"
                       accept="image/x-icon,image/png,image/jpeg,image/svg+xml">
                @error('favicon')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror

                @php $currentFavicon = $settings['favicon'] ?? null; @endphp
                @if ($currentFavicon)
                    <div class="mt-2 flex items-center gap-2 text-sm opacity-60">
                        <span>Current favicon:</span>
                        <img src="{{ Storage::url($currentFavicon) }}" alt="Favicon" class="w-4 h-4">
                        <span>{{ $currentFavicon }}</span>
                    </div>
                @endif
            </div>

            {{-- Colors --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="form-control">
                    <label class="label" for="primary_color">
                        <span class="label-text">Primary Color</span>
                        <span class="label-text-alt opacity-60">e.g. #3490dc</span>
                    </label>
                    <input type="text" name="primary_color" id="primary_color"
                           class="input input-bordered @error('primary_color') input-error @enderror"
                           value="{{ old('primary_color', $settings['primary_color'] ?? '') }}"
                           placeholder="#3490dc">
                    @error('primary_color')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label" for="secondary_color">
                        <span class="label-text">Secondary Color</span>
                        <span class="label-text-alt opacity-60">Optional</span>
                    </label>
                    <input type="text" name="secondary_color" id="secondary_color"
                           class="input input-bordered @error('secondary_color') input-error @enderror"
                           value="{{ old('secondary_color', $settings['secondary_color'] ?? '') }}"
                           placeholder="#38c172">
                    @error('secondary_color')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>
            </div>

            <div class="flex gap-2 justify-end mt-6">
                <a href="{{ route('agencies.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Branding</button>
            </div>
        </form>
    </div>
</div>
@endsection
