@extends('layouts.app')

@section('title', 'New Status Code')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('status-codes.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Status Codes
        </a>
    </div>

    <div class="card bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-indigo-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>🏷️</span> New Status Code
        </h2>
        <p class="opacity-60 text-sm mt-1">Add a new status code</p>
    </div>

    @if($errors->any())
        <div role="alert" class="alert alert-error mb-6 shadow-sm">
            <span>❌</span>
            <ul class="list-disc pl-4 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('status-codes.store') }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🔢 Code <span class="text-error">*</span></legend>
                <input type="number" name="code" value="{{ old('code', $nextCode) }}" required
                    class="input w-full" placeholder="e.g. 10">
                @error('code') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏷️ Label <span class="text-error">*</span></legend>
                <input type="text" name="label" value="{{ old('label') }}" required
                    class="input w-full" placeholder="e.g. Pending">
                @error('label') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🇸🇦 Label (Saudi)</legend>
                <input type="text" name="label_saudi" value="{{ old('label_saudi') }}"
                    class="input w-full" placeholder="e.g. قيد الانتظار">
                @error('label_saudi') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📝 Description</legend>
                <textarea name="description" class="textarea w-full" rows="4"
                    placeholder="Describe this status code...">{{ old('description') }}</textarea>
                @error('description') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🎨 Color</legend>
                    <input type="color" name="color" value="{{ old('color', '#64748b') }}"
                        class="input w-full h-12 p-1">
                    @error('color') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🔢 Sort Order</legend>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                        min="0" class="input w-full" placeholder="0">
                    @error('sort_order') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Create Status Code
                </button>
                <a href="{{ route('status-codes.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
