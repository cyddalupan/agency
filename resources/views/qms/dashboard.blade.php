@extends('layouts.app')

@section('title', 'QMS dashboard')

@section('content')
<div class="p-4 sm:p-6 space-y-6 max-w-6xl">

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">📊</span>
                <div>
                    <h1 class="text-2xl font-extrabold">QMS dashboard</h1>
                    <p class="opacity-70 text-sm mt-0.5">
                        Every number here is the count of the list it links to. Nothing is a sample.
                    </p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs opacity-80 uppercase tracking-wide">As of</div>
                <div class="font-mono font-bold" id="as-of">{{ $asOf->format('d M Y H:i') }}</div>
                <div class="text-xs opacity-70">Period: {{ $period }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="qms-tiles">
        @foreach ($tiles as $tile)
            @php
                $toneClass = match ($tile['tone']) {
                    'bad'     => 'border-error/40',
                    'warn'    => 'border-warning/40',
                    default   => 'border-base-300',
                };
                $countClass = match (true) {
                    $tile['tone'] === 'bad' && $tile['count'] > 0  => 'text-error',
                    $tile['tone'] === 'warn' && $tile['count'] > 0 => 'text-warning',
                    default => '',
                };
            @endphp
            <a href="{{ $tile['url'] }}"
               class="card bg-base-100 shadow hover:shadow-md transition-shadow border {{ $toneClass }}"
               id="tile-{{ $tile['key'] }}"
               data-tile="{{ $tile['key'] }}"
               data-count="{{ $tile['count'] }}">
                <div class="card-body py-4 px-4 gap-1">
                    <div class="text-xs font-semibold uppercase tracking-wide opacity-70">{{ $tile['label'] }}</div>
                    <div class="text-3xl font-extrabold {{ $countClass }}" id="tile-count-{{ $tile['key'] }}">
                        {{ number_format($tile['count']) }}
                    </div>
                    <div class="text-xs opacity-60">{{ $tile['hint'] }}</div>
                    <div class="text-[10px] opacity-50 mt-1">
                        {{ $tile['period'] }} · as of {{ $tile['as_of']->format('H:i') }}
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="alert bg-base-200 border border-base-300 text-sm" id="traceability-note">
        <span>
            Each tile opens the filtered list that produced its number. If the two ever disagree,
            the number is wrong — the list is the record.
        </span>
    </div>
</div>
@endsection
