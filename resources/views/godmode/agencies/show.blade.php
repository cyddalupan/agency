@extends('godmode.layouts.app')

@section('title', $agency->name)

@section('content')
<div class="flex items-start justify-between gap-4">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-2xl font-extrabold">{{ $agency->name }}</h1>
            @include('godmode.agencies._status', ['status' => $agency->status])
        </div>
        <p class="text-sm opacity-70 mt-1">
            @if ($fullHost)
                <code>{{ $fullHost }}</code>
            @else
                <span class="badge badge-sm badge-error badge-outline">no subdomain</span>
                This agency cannot be reached at any address.
            @endif
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('godmode.agencies.edit', $agency) }}" class="btn btn-sm btn-neutral">Edit</a>

        {{-- No delete control anywhere on this screen (§1, criterion 15). --}}
        @if ($agency->status === 'active')
            <form method="POST" action="{{ route('godmode.agencies.archive', $agency) }}"
                  onsubmit="return confirm(@js('Archive '.$agency->name.'? It has '.number_format($counts['applicants']).' applicants and '.$counts['users'].' users. Their logins stop working.'));">
                @csrf
                <button type="submit" class="btn btn-sm btn-warning">Archive</button>
            </form>
        @else
            <form method="POST" action="{{ route('godmode.agencies.activate', $agency) }}"
                  onsubmit="return confirm(@js('Activate '.$agency->name.'? Its subdomain starts serving again.'));">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">Activate</button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
    @foreach ([
        ['Applicants', number_format($counts['applicants']), 'applicants'],
        ['Users', number_format($counts['users']), 'users'],
        ['Branches', number_format($counts['branches']), 'branches'],
        ['Declared branches', number_format((int) $agency->num_branches), 'declared-branches'],
    ] as [$label, $value, $key])
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4">
                <span class="text-xs uppercase tracking-wide opacity-60">{{ $label }}</span>
                <span class="text-2xl font-bold" data-count="{{ $key }}">{{ $value }}</span>
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-base">Identity</h2>
            <dl class="grid grid-cols-3 gap-y-2 text-sm">
                <dt class="opacity-60">Email</dt><dd class="col-span-2">{{ $agency->email ?: '—' }}</dd>
                <dt class="opacity-60">Contact person</dt><dd class="col-span-2">{{ $agency->contact_person ?: '—' }}</dd>
                <dt class="opacity-60">City</dt><dd class="col-span-2">{{ $agency->city ?: '—' }}</dd>
                <dt class="opacity-60">Address</dt><dd class="col-span-2">{{ $agency->address ?: '—' }}</dd>
                <dt class="opacity-60">Created</dt><dd class="col-span-2">{{ $agency->created_at?->format('d/m/Y H:i') }}</dd>
            </dl>

            <h3 class="font-semibold text-sm mt-4">Payload (read-only)</h3>
            <p class="text-xs opacity-60">
                Settings, logo and company profile are summarised here. Editing them belongs to the
                per-agency settings screen, not this card.
            </p>
            <ul class="text-sm mt-2 space-y-1">
                <li>Logo: {{ $agency->logo ? 'set' : 'none' }}</li>
                <li>Settings: {{ is_array($agency->settings) && $agency->settings !== [] ? count($agency->settings).' keys' : 'empty' }}</li>
                <li>Company profile: {{ filled($agency->company_profile) ? 'written' : 'empty' }}</li>
                <li>Page tabs: {{ is_array($agency->page_tabs) ? count(array_filter($agency->page_tabs)) : 0 }} with content</li>
            </ul>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-base">History</h2>
            @forelse ($history as $event)
                <div class="border-b border-base-300 pb-2 mb-2 text-sm" data-audit-id="{{ $event->id }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-semibold">{{ $event->action_label }}</span>
                        <span class="text-xs opacity-60">{{ $event->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="text-xs opacity-70">
                        by {{ $event->actor?->username ?: ($event->actor?->email ?: 'system') }}
                    </div>
                    @if (! empty($event->before) || ! empty($event->after))
                        @foreach (array_unique(array_merge(array_keys($event->before ?? []), array_keys($event->after ?? []))) as $field)
                            @php
                                $from = data_get($event->before, $field);
                                $to = data_get($event->after, $field);
                            @endphp
                            @if ($from !== $to)
                                <div class="text-xs">
                                    <span class="opacity-60">{{ $field }}:</span>
                                    {{ is_scalar($from) || $from === null ? ($from ?? '—') : json_encode($from) }}
                                    →
                                    {{ is_scalar($to) || $to === null ? ($to ?? '—') : json_encode($to) }}
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            @empty
                <p class="text-sm opacity-60">No recorded changes yet.</p>
            @endforelse

            <h3 class="font-semibold text-sm mt-4">Recent activity</h3>
            @forelse ($activity as $row)
                <div class="text-sm border-b border-base-300 pb-1 mb-1">
                    {{ $row->description ?: $row->action }}
                    <span class="text-xs opacity-60">· {{ $row->created_at?->format('d/m/Y H:i') }}</span>
                </div>
            @empty
                <p class="text-sm opacity-60">No activity in this agency.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
