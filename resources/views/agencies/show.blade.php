@extends('layouts.app')

@section('title', 'Manage — ' . $agency->name)

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('agencies.index') }}" class="link link-hover text-sm opacity-60 mb-2 inline-block">
                ← Back to Agencies
            </a>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>🏢</span> Manage: {{ $agency->name }}
            </h2>
            @if ($agency->status === 'active')
                <span class="badge badge-success mt-1">Active</span>
            @elseif ($agency->status === 'inactive')
                <span class="badge badge-warning mt-1">Inactive</span>
            @else
                <span class="badge badge-error mt-1">{{ ucfirst($agency->status) }}</span>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('agencies.edit', $agency) }}" class="btn btn-primary btn-sm">
                <span>✏️</span> Edit
            </a>
            <a href="{{ route('agencies.branding', $agency) }}" class="btn btn-outline btn-sm">
                <span>🎨</span> Branding
            </a>
        </div>
    </div>

    {{-- Success / Error --}}
    @if (session('success'))
        <div class="alert alert-success mb-4"><span>✅</span> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error mb-4"><span>❌</span> {{ session('error') }}</div>
    @endif

    {{-- Overview --}}
    <div class="bg-base-100 rounded-box shadow-sm border p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Overview</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="opacity-50 font-medium uppercase tracking-wider text-xs">Agency Name</dt>
                <dd class="font-semibold mt-1">{{ $agency->name }}</dd>
            </div>
            <div>
                <dt class="opacity-50 font-medium uppercase tracking-wider text-xs">Subdomain</dt>
                <dd class="mt-1">
                    <code class="bg-base-200 px-2 py-0.5 rounded">{{ $agency->subdomain }}</code>
                </dd>
            </div>
            <div>
                <dt class="opacity-50 font-medium uppercase tracking-wider text-xs">Status</dt>
                <dd class="mt-1">
                    @if ($agency->status === 'active')
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-warning">Inactive</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="opacity-50 font-medium uppercase tracking-wider text-xs">Created</dt>
                <dd class="mt-1">{{ $agency->created_at?->format('M d, Y') ?? '—' }}</dd>
            </div>
        </dl>

        @if ($agency->subdomain)
            <div class="mt-5 pt-4 border-t border-base-200">
                <p class="opacity-50 font-medium uppercase tracking-wider text-xs mb-2">Live Agency Portal</p>
                <a href="https://{{ $agency->subdomain }}.fixitautoservices.com"
                   target="_blank" rel="noopener"
                   class="btn btn-outline btn-sm">
                    <span>🌐</span> Open {{ $agency->name }} Portal
                </a>
                <p class="text-xs opacity-50 mt-2">
                    https://{{ $agency->subdomain }}.fixitautoservices.com
                </p>
            </div>
        @endif
    </div>

    {{-- Branding Snapshot --}}
    <div class="bg-base-100 rounded-box shadow-sm border p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Branding</h3>
            <a href="{{ route('agencies.branding', $agency) }}" class="link link-hover text-sm">Manage →</a>
        </div>
        <div class="flex items-center gap-4 flex-wrap">
            @if ($agency->logo)
                <img src="{{ Storage::url($agency->logo) }}" alt="{{ $agency->name }} logo"
                     class="max-w-[200px] max-h-[100px] object-contain border rounded p-1">
            @else
                <div class="opacity-40 text-sm py-4">No logo uploaded</div>
            @endif

            @php $settings = $agency->settings ?? []; @endphp
            @if (!empty($settings['primary_color']) || !empty($settings['secondary_color']))
                <div class="flex gap-3 items-center">
                    @if (!empty($settings['primary_color']))
                        <div class="text-center">
                            <p class="text-xs opacity-50 mb-1">Primary</p>
                            <div class="w-10 h-10 rounded border" style="background-color: {{ $settings['primary_color'] }}"></div>
                            <p class="text-xs opacity-40 mt-1">{{ $settings['primary_color'] }}</p>
                        </div>
                    @endif
                    @if (!empty($settings['secondary_color']))
                        <div class="text-center">
                            <p class="text-xs opacity-50 mb-1">Secondary</p>
                            <div class="w-10 h-10 rounded border" style="background-color: {{ $settings['secondary_color'] }}"></div>
                            <p class="text-xs opacity-40 mt-1">{{ $settings['secondary_color'] }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="opacity-40 text-sm">No brand colors set</div>
            @endif
        </div>
    </div>

    {{-- Agency Users --}}
    <div class="bg-base-100 rounded-box shadow-sm border p-6 mt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">👤 Users ({{ $users->count() }})</h3>
            <a href="{{ route('agencies.users.create', $agency) }}" class="btn btn-primary btn-sm">
                <span>➕</span> Add User
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-4"><span>✅</span> {{ session('success') }}</div>
        @endif

        @if ($users->isEmpty())
            <p class="opacity-40 text-sm py-4">No users in this agency yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $member)
                            <tr>
                                <td class="font-medium">{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>{{ ucfirst($member->user_type) }}</td>
                                <td>
                                    @if ($member->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-warning">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-1">
                                        <a href="{{ route('agencies.users.edit', [$agency, $member]) }}"
                                           class="btn btn-xs btn-ghost">Edit</a>
                                        <form action="{{ route('agencies.users.destroy', [$agency, $member]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-ghost text-error"
                                                    onclick="return confirm('Delete user {{ $member->name }}?')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
