@extends('layouts.app')

@section('title', 'Applicant Form Defaults')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('settings.index') }}" class="link link-secondary text-sm">← Back to Settings</a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold">📋 Applicant Form Defaults</h2>
        <p class="opacity-60 text-sm mt-1">
            Choose which options appear on your agency's "Add Applicant" form. Options are picked
            from existing reference data — no typing, so no typos.
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

    <form method="POST" action="{{ route('settings.applicant-form-defaults.update') }}" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-6">
            @csrf

            {{-- Preferred Position (select from existing positions) --}}
            <fieldset class="fieldset">
                <legend class="fieldset-legend">💼 Preferred Position</legend>
                <p class="text-xs opacity-60 mb-2">Tick the positions selectable on the Add Applicant form.</p>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($positions as $pos)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="position_ids[]" value="{{ $pos->id }}"
                                   class="checkbox checkbox-sm"
                                   @checked(in_array($pos->id, $defaults['position_ids'] ?? []))>
                            {{ $pos->name }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            {{-- Status (select from existing status_codes) --}}
            <fieldset class="fieldset">
                <legend class="fieldset-legend">📊 Status</legend>
                <p class="text-xs opacity-60 mb-2">Tick the statuses selectable on the Add Applicant form.</p>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($statuses as $st)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="status_codes[]" value="{{ $st->code }}"
                                   class="checkbox checkbox-sm"
                                   @checked(in_array($st->code, $defaults['status_codes'] ?? []))>
                            {{ $st->label }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            {{-- Source (select from fixed known list) --}}
            <fieldset class="fieldset">
                <legend class="fieldset-legend">📱 Source</legend>
                <p class="text-xs opacity-60 mb-2">Tick the Source options available (incl. Branch).</p>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($sourceOpts as $src)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="sources[]" value="{{ $src }}"
                                   class="checkbox checkbox-sm"
                                   @checked(in_array($src, $defaults['sources'] ?? []))>
                            {{ $src }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            {{-- Firstimer / Ex-Abroad toggle --}}
            <fieldset class="fieldset">
                <legend class="fieldset-legend">🔄 Firstimer / Ex-Abroad</legend>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="enable_firstimer" value="1"
                           class="checkbox checkbox-sm"
                           @checked((bool)($defaults['enable_firstimer'] ?? true))>
                    Show the "Firstimer / Ex-Abroad" field on the Add Applicant form
                </label>
            </fieldset>

            {{-- FRA options (Status tab) — enabled subset from canonical list --}}
            <fieldset class="fieldset">
                <legend class="fieldset-legend">🛂 FRA (Status tab)</legend>
                <p class="text-xs opacity-60 mb-2">Choose which FRA options appear on applicants' Status tab.</p>
                <div class="grid grid-cols-1 gap-1">
                    @foreach ($fraOpts ?? [] as $value => $label)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="fra_options[]" value="{{ $value }}"
                                   class="checkbox checkbox-sm"
                                   @checked(in_array($value, $defaults['fra_options'] ?? []))>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary"><span>💾</span> Save Defaults</button>
                <a href="{{ route('settings.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
