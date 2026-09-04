@extends('layouts.app')

@section('title', 'Applicants Table Columns')

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('settings.index') }}" class="link link-hover text-sm opacity-60 mb-2 inline-block">
            ← Back to Settings
        </a>
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>📋</span> Applicants Table Columns
        </h2>
        <p class="opacity-60 text-sm mt-1">
            Choose which columns appear on the Applicants page for {{ $agency->name }}.
            <strong>Name</strong> and <strong>Status</strong> are always shown.
        </p>
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
            <span>✅</span> {{ session('success') }}
        </div>
    @endif

    {{-- Column Picker Form --}}
    <div class="bg-base-100 rounded-box shadow-sm border p-6">
        <form action="{{ route('settings.applicants-table-columns.update') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($labels as $key => $label)
                    @php
                        $checked = in_array($key, $selected);
                        $locked = in_array($key, ['name', 'status']);
                    @endphp
                    <label class="flex items-center gap-3 p-3 rounded-lg border {{ $checked ? 'border-primary/40 bg-primary/5' : 'border-base-300' }} cursor-pointer hover:bg-base-200/50 transition-colors">
                        @if($locked)
                            {{-- Locked columns are always included in the submitted order --}}
                            <input type="hidden" name="columns[]" value="{{ $key }}">
                            <input type="checkbox" value="{{ $key }}" class="checkbox checkbox-sm checkbox-primary" checked disabled>
                        @else
                            <input type="checkbox" name="columns[]" value="{{ $key }}"
                                   class="checkbox checkbox-sm checkbox-primary"
                                   {{ $checked ? 'checked' : '' }}>
                        @endif
                        <span class="text-sm font-medium">{{ $label }}</span>
                        @if($locked)
                            <span class="badge badge-ghost badge-xs ml-auto">always</span>
                        @endif
                    </label>
                @endforeach
            </div>

            <div class="flex gap-2 justify-end mt-6">
                <a href="{{ route('settings.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Columns</button>
            </div>
        </form>
    </div>
</div>
@endsection
