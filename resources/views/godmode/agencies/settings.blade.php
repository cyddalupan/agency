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

@include('godmode.agencies._form', ['agency' => $agency, 'state' => $state, 'themes' => $themes])

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
