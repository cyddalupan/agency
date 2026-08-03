@extends('layouts.app')

@section('title', 'Edit Branch')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('branches.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Branches
        </a>
    </div>

    <div class="card bg-gradient-to-br from-amber-500/10 to-orange-500/10 border border-amber-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>✏️</span> Edit Branch
        </h2>
        <p class="opacity-60 text-sm mt-1">{{ $branch->name }}</p>
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

    <form action="{{ route('branches.update', $branch) }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf
            @method('PUT')

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏷️ Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name', $branch->name) }}" required
                    class="input w-full" placeholder="e.g. Main Branch">
                @error('name') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📍 Address</legend>
                <input type="text" name="address" value="{{ old('address', $branch->address) }}"
                    class="input w-full" placeholder="e.g. 123 Main Street">
                @error('address') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📞 Contact</legend>
                <input type="text" name="contact" value="{{ old('contact', $branch->contact) }}"
                    class="input w-full" placeholder="e.g. +966 5x xxx xxxx">
                @error('contact') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🔘 Status</legend>
                <select name="status" class="select w-full">
                    <option value="active" @selected(old('status', $branch->status) === 'active')>🟢 Active</option>
                    <option value="inactive" @selected(old('status', $branch->status) === 'inactive')>⚪ Inactive</option>
                </select>
                @error('status') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Update Branch
                </button>
                <a href="{{ route('branches.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
