@extends('godmode.layouts.app')

@section('title', 'Settings · '.$agency->name)

@section('content')
<div class="flex items-start justify-between gap-4">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-2xl font-extrabold">Settings · {{ $agency->name }}</h1>
            @include('godmode.agencies._status', ['status' => $agency->status])
        </div>
        <p class="text-sm opacity-70 mt-1">
            @if ($fullHost)
                <code>{{ $fullHost }}</code>
            @else
                <span class="badge badge-sm badge-error badge-outline">no subdomain</span>
            @endif
        </p>
    </div>
    <a href="{{ route('godmode.agencies.show', $agency) }}" class="btn btn-sm btn-ghost">Back to agency</a>
</div>

<form method="POST" action="{{ route('godmode.agencies.settings.update', $agency) }}" class="space-y-5" data-testid="agency-settings-form">
    @csrf
    {{-- Concurrency token: md5 of the document as rendered. A changed hash
         refuses the save with 422 rather than overwriting someone else. --}}
    <input type="hidden" name="settings_version" value="{{ $state['settings_version'] }}">

    {{-- A. Features --}}
    <div class="card bg-base-100 shadow">
        <div class="card-body space-y-4">
            <h2 class="card-title text-base">Features</h2>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Quality Management (QMS)</legend>
                <div class="flex flex-wrap gap-4 text-sm">
                    @foreach ([\App\Services\GodMode\GodAgencySettingsService::QMS_ON => 'On',
                               \App\Services\GodMode\GodAgencySettingsService::QMS_OFF => 'Off',
                               \App\Services\GodMode\GodAgencySettingsService::QMS_DEFAULT => 'Default'] as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="qms_enabled" value="{{ $value }}"
                                   class="radio radio-primary radio-sm" data-qms="{{ $value }}"
                                   @checked($state['qms_enabled'] === $value)>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs opacity-60 mt-1">
                    Default removes the stored key and restores the code default
                    (@if ($state['qms_default_on']) on for this agency @else off for this agency @endif).
                    @if (! $state['qms_stored'])
                        No QMS key is stored for this agency today.
                    @endif
                </p>
            </fieldset>

            <fieldset class="fieldset">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="hide_type_tab_counts" value="1"
                           class="checkbox checkbox-primary checkbox-sm mt-1"
                           data-toggle="hide-type-tab-counts"
                           @checked($state['hide_type_tab_counts'])>
                    <span class="text-sm">
                        <span class="font-semibold">Hide applicant-type tab counters</span>
                        <span class="block opacity-60">
                            Follows the stored key. An agency whose name contains GULF hides the
                            counters even with nothing stored; ticking this makes it explicit.
                        </span>
                    </span>
                </label>
            </fieldset>

            <div class="text-xs opacity-60 space-y-1">
                <p class="font-semibold opacity-100">Gate lists still hard-coded (not editable here):</p>
                <p>Welfare, Employees and Agents Report are code lists in GatingTable::FEATURE_GATES.</p>
            </div>
        </div>
    </div>

    {{-- B. Defaults --}}
    <div class="card bg-base-100 shadow">
        <div class="card-body space-y-4">
            <h2 class="card-title text-base">Defaults</h2>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Complaint SLA</legend>
                <div class="flex flex-wrap gap-4 text-sm">
                    <label class="flex items-center gap-2">
                        <span>Acknowledgement window</span>
                        <input type="number" name="complaint_sla[ack_days]" min="1" max="30"
                               value="{{ $state['ack_days'] }}" class="input input-sm input-bordered w-24"
                               data-sla="ack" required>
                        <span class="opacity-60">days</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <span>Resolution window</span>
                        <input type="number" name="complaint_sla[resolve_days]" min="1" max="90"
                               value="{{ $state['resolve_days'] }}" class="input input-sm input-bordered w-24"
                               data-sla="resolve" required>
                        <span class="opacity-60">days</span>
                    </label>
                </div>
                <p class="text-xs opacity-60 mt-1">
                    Defaults 2 / 15.
                    @if (! $state['ack_days_stored'] || ! $state['resolve_days_stored'])
                        Currently showing defaults, not stored values.
                    @endif
                </p>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Applicant form</legend>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="enable_firstimer" value="1"
                           class="checkbox checkbox-primary checkbox-sm mt-1"
                           data-toggle="enable-firstimer"
                           @checked($state['enable_firstimer'])>
                    <span class="text-sm">Enable the first-timer option</span>
                </label>
                <p class="text-xs opacity-60">
                    The form's default lists (positions, statuses, sources) are edited by the
                    agency in its own Settings screen.
                </p>
            </fieldset>
        </div>
    </div>

    {{-- C. Identity-adjacent overrides --}}
    <div class="card bg-base-100 shadow">
        <div class="card-body space-y-4">
            <h2 class="card-title text-base">Display</h2>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Colour theme</legend>
                <select name="color_theme" class="select select-sm select-bordered w-full max-w-xs" data-field="color-theme">
                    @foreach ($themes as $key => $theme)
                        <option value="{{ $key }}" @selected($state['color_theme'] === $key)>
                            {{ $theme['label'] ?? $key }}
                        </option>
                    @endforeach
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Banner colour</legend>
                <input type="text" name="banner_color" value="{{ $state['banner_color'] }}"
                       placeholder="#220dbf" class="input input-sm input-bordered w-40"
                       data-field="banner-color">
                <p class="text-xs opacity-60">Leave empty for the default gold.</p>
            </fieldset>

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="all_caps" value="1"
                       class="checkbox checkbox-primary checkbox-sm mt-1"
                       data-toggle="all-caps"
                       @checked($state['all_caps'])>
                <span class="text-sm">Render site text in all caps</span>
            </label>

            {{-- Label is the meaning; the stored key is inverted. --}}
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="show_company_profile" value="1"
                       class="checkbox checkbox-primary checkbox-sm mt-1"
                       data-toggle="show-company-profile"
                       @checked($state['show_company_profile'])>
                <span class="text-sm">
                    <span class="font-semibold">Show the Company Profile page</span>
                    <span class="block opacity-60">Stored as <code>hide_company_profile</code> (inverted).</span>
                </span>
            </label>
        </div>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('godmode.agencies.show', $agency) }}" class="btn btn-sm btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-sm btn-primary" data-action="save-settings">Save settings</button>
    </div>
</form>

{{-- D. Read-only --}}
<div class="card bg-base-100 shadow">
    <div class="card-body space-y-3">
        <h2 class="card-title text-base">Read-only</h2>

        <div class="text-sm" data-readonly="applicant-qr-token">
            <span class="opacity-60">Applicant QR token:</span>
            @if ($qrToken)
                <code>{{ $qrToken }}</code> — rotate from Settings &gt; QR Code in the tenant.
            @else
                <span class="opacity-60">not set</span>
            @endif
        </div>

        @if ($readOnly === [])
            <p class="text-sm opacity-60">No other keys stored.</p>
        @else
            <table class="table table-xs">
                <thead>
                    <tr><th>Key</th><th>Value</th></tr>
                </thead>
                <tbody data-readonly="keys">
                    @foreach ($readOnly as $path => $value)
                        <tr data-key="{{ $path }}">
                            <td><code>{{ $path }}</code></td>
                            <td class="break-all">
                                @if (is_bool($value))
                                    {{ $value ? 'true' : 'false' }}
                                @elseif ($path === 'applicant_qr_token')
                                    {{-- Never render a live QR token: rotation invalidates printed codes, and
                                         this list would otherwise print it in full next to the masked line. --}}
                                    <code>{{ $qrToken ?? 'set' }}</code>
                                @elseif (is_array($value))
                                    <code>{{ json_encode($value, JSON_UNESCAPED_SLASHES) }}</code>
                                @elseif ($value === null)
                                    <span class="opacity-60">null</span>
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
