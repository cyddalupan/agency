@extends('layouts.app')

@section('title', 'New Position')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('positions.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Positions
        </a>
    </div>

    <div class="card bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-indigo-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>💼</span> New Position
        </h2>
        <p class="opacity-60 text-sm mt-1">Add a new position</p>
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

    <form action="{{ route('positions.store') }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏷️ Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="input w-full" placeholder="e.g. Technician">
                @error('name') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📝 Description</legend>
                <textarea name="description" class="textarea w-full" rows="4"
                    placeholder="Describe this position...">{{ old('description') }}</textarea>
                @error('description') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Create Position
                </button>
                <a href="{{ route('positions.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
