@extends('layouts.app')

@section('title', 'Create Agency')

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('agencies.index') }}" class="link link-hover text-sm opacity-60 mb-2 inline-block">
            ← Back to Agencies
        </a>
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>🏢</span> Create Agency
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
        <form action="{{ route('agencies.store') }}" method="POST">
            @csrf

            <div class="form-control mb-4">
                <label class="label" for="name">
                    <span class="label-text">Agency Name</span>
                    <span class="label-text-alt text-error">*</span>
                </label>
                <input type="text" name="name" id="name"
                       class="input input-bordered @error('name') input-error @enderror"
                       value="{{ old('name') }}" required>
                @error('name')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            <div class="form-control mb-4">
                <label class="label" for="subdomain">
                    <span class="label-text">Subdomain or URL</span>
                    <span class="label-text-alt text-error">*</span>
                </label>
                <div class="join w-full">
                    <input type="text" name="subdomain" id="subdomain"
                           class="input input-bordered w-full @error('subdomain') input-error @enderror"
                           value="{{ old('subdomain') }}" required placeholder="my-agency or https://my-agency.landas.fixitautoservices.com">
                </div>
                <p class="text-xs opacity-50 mt-1">Type just the subdomain slug (e.g. <code>my-agency</code>) or paste the full URL — we'll extract just the subdomain part automatically.</p>
                @error('subdomain')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            <div class="flex gap-2 justify-end mt-6">
                <a href="{{ route('agencies.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Agency</button>
            </div>
        </form>
    </div>
</div>
@endsection
