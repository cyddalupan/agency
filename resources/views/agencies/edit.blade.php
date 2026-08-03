@extends('layouts.app')

@section('title', 'Edit Agency')

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('agencies.index') }}" class="link link-hover text-sm opacity-60 mb-2 inline-block">
            ← Back to Agencies
        </a>
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>🏢</span> Edit Agency: {{ $agency->name }}
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

    {{-- Form --}}
    <div class="bg-base-100 rounded-box shadow-sm border p-6">
        <form action="{{ route('agencies.update', $agency) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Agency Icon --}}
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text">Agency Icon (Logo)</span>
                    <span class="label-text-alt opacity-60">PNG, JPG, SVG, WebP (max 2MB)</span>
                </label>
                <div class="flex items-center gap-4">
                    @if ($agency->logo)
                        <img src="{{ Storage::url($agency->logo) }}" alt="Current icon"
                             class="w-16 h-16 object-contain border rounded p-1">
                    @else
                        <div class="w-16 h-16 rounded flex items-center justify-center opacity-30 border">No icon</div>
                    @endif
                    <input type="file" name="logo" id="logo"
                           accept="image/png,image/jpeg,image/svg+xml,image/webp"
                           class="file-input file-input-bordered file-input-sm flex-1 @error('logo') input-error @enderror">
                </div>
                @error('logo')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            <div class="form-control mb-4">
                <label class="label" for="name">
                    <span class="label-text">Agency Name</span>
                    <span class="label-text-alt text-error">*</span>
                </label>
                <input type="text" name="name" id="name"
                       class="input input-bordered @error('name') input-error @enderror"
                       value="{{ old('name', $agency->name) }}" required>
                @error('name')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            <div class="form-control mb-4">
                <label class="label" for="subdomain">
                    <span class="label-text">Subdomain</span>
                    <span class="label-text-alt text-error">*</span>
                </label>
                <div class="join w-full">
                    <input type="text" name="subdomain" id="subdomain"
                           class="input input-bordered join-item flex-1 @error('subdomain') input-error @enderror"
                           value="{{ old('subdomain', $agency->subdomain) }}" required>
                </div>
                @error('subdomain')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            <div class="flex gap-2 justify-end mt-6">
                <a href="{{ route('agencies.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Agency</button>
            </div>
        </form>
    </div>
</div>
@endsection
