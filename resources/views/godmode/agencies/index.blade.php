@extends('godmode.layouts.app')

@section('title', 'Agencies')

@php
    // Filters into a query string, minus paging: used for the sort links and
    // the hidden `_return` field so archive/activate come back to this view.
    $base = array_filter([
        'q'      => $filters['q'] ?: null,
        'status' => $filters['status'] ?: null,
        'empty'  => $filters['empty'] ? 1 : null,
        'sort'   => $filters['sort'] ?: null,
        'dir'    => $filters['dir'] ?: null,
        'per'    => $filters['per'] ?: null,
        'page'   => request('page'),
    ], fn ($v) => $v !== null && $v !== '');
    $returnQuery = http_build_query($base);

    $sortLink = function (string $column) use ($base, $filters) {
        $dir = ($filters['sort'] === $column && $filters['dir'] !== 'desc') ? 'desc' : 'asc';
        return route('godmode.agencies.index', array_merge($base, ['sort' => $column, 'dir' => $dir]));
    };
    $arrow = function (string $column) use ($filters) {
        if ($filters['sort'] !== $column) {
            return '';
        }
        return $filters['dir'] === 'desc' ? ' ↓' : ' ↑';
    };
@endphp

@section('content')
<div class="flex items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold">Agencies</h1>
        <p class="text-sm opacity-70">Every client on the platform, across all tenants.</p>
    </div>
    <a href="{{ route('godmode.agencies.create') }}" class="btn btn-primary btn-sm" id="agencies-create">New agency</a>
</div>

{{-- Search + filters. All state lives in the URL so a copied link reproduces
     the screen (criterion 23). --}}
<form method="GET" action="{{ route('godmode.agencies.index') }}" class="card bg-base-100 shadow" id="agencies-filters">
    <div class="card-body p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-3">
        <label class="flex flex-col gap-1 flex-1 min-w-[12rem]">
            <span class="text-sm font-semibold">Search</span>
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Name, subdomain, email or contact person"
                   class="input input-bordered input-sm" minlength="2" autocomplete="off" id="agencies-q">
        </label>

        @if ($filters['status'])
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
        @endif
        @if ($filters['sort'])
            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
            <input type="hidden" name="dir" value="{{ $filters['dir'] }}">
        @endif

        <label class="flex flex-col gap-1 min-w-36 max-w-40">
            <span class="text-sm font-semibold">Per page</span>
            <select name="per" class="select select-bordered select-sm" onchange="this.form.submit()">
                @foreach ($perOptions as $option)
                    <option value="{{ $option }}" @selected((int) ($filters['per'] ?: 25) === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </label>

        <label class="label cursor-pointer gap-2 normal-case">
            <input type="checkbox" name="empty" value="1" class="checkbox checkbox-sm" @checked($filters['empty']) onchange="this.form.submit()">
            <span class="text-sm">Only empty agencies</span>
        </label>

        <button type="submit" class="btn btn-sm btn-neutral">Search</button>
        <a href="{{ route('godmode.agencies.index') }}" class="btn btn-sm btn-ghost">Clear</a>
    </div>
</form>

{{-- Status tabs with whole-set counts: they do not move while searching. --}}
<div role="tablist" class="tabs tabs-boxed w-fit" id="agencies-status-tabs">
    @foreach (['' => 'all', 'active' => 'active', 'pending' => 'pending', 'inactive' => 'inactive'] as $value => $key)
        <a role="tab"
           href="{{ route('godmode.agencies.index', array_merge($base, ['status' => $value ?: null, 'page' => null])) }}"
           class="tab @if ((string) ($filters['status'] ?? '') === (string) $value) tab-active @endif"
           data-tab="{{ $key }}">
            {{ ucfirst($key) }} <span class="ml-1 opacity-60">{{ $statusCounts[$key] }}</span>
        </a>
    @endforeach
</div>

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-sm" id="agencies-table">
        <thead>
            <tr>
                <th><a href="{{ $sortLink('name') }}" class="link link-hover">Agency{{ $arrow('name') }}</a></th>
                <th><a href="{{ $sortLink('subdomain') }}" class="link link-hover">Subdomain{{ $arrow('subdomain') }}</a></th>
                <th><a href="{{ $sortLink('status') }}" class="link link-hover">Status{{ $arrow('status') }}</a></th>
                <th class="text-right"><a href="{{ $sortLink('applicants') }}" class="link link-hover">Applicants{{ $arrow('applicants') }}</a></th>
                <th class="text-right"><a href="{{ $sortLink('users') }}" class="link link-hover">Users{{ $arrow('users') }}</a></th>
                <th>Branches</th>
                <th>City</th>
                <th>Contact person</th>
                <th><a href="{{ $sortLink('created') }}" class="link link-hover">Created{{ $arrow('created') }}</a></th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($agencies as $agency)
                <tr data-agency-id="{{ $agency->id }}">
                    <td class="font-semibold">
                        <a href="{{ route('godmode.agencies.show', $agency) }}" class="link link-hover">{{ $agency->name }}</a>
                    </td>
                    <td>
                        @if (filled($agency->subdomain))
                            <code class="text-xs">{{ $agency->subdomain }}.landas.fixitautoservices.com</code>
                        @else
                            <span class="badge badge-sm badge-error badge-outline" data-badge="no-subdomain">no subdomain</span>
                        @endif
                    </td>
                    <td>@include('godmode.agencies._status', ['status' => $agency->status])</td>
                    <td class="text-right">
                        <span data-count="applicants">{{ (int) $agency->applicants_count }}</span>
                    </td>
                    <td class="text-right">
                        <span data-count="users">{{ (int) $agency->users_count }}</span>
                    </td>
                    <td>
                        <span data-count="branches">{{ (int) $agency->branches_count }}</span>
                        @if ((int) $agency->branches_count !== (int) $agency->num_branches)
                            <div class="text-xs opacity-60" title="Declared at registration">declared {{ (int) $agency->num_branches }}</div>
                        @endif
                    </td>
                    <td>{{ $agency->city ?: '—' }}</td>
                    <td>{{ $agency->contact_person ?: '—' }}</td>
                    <td>{{ $agency->created_at?->format('d/m/Y') }}</td>
                    <td class="text-right whitespace-nowrap">
                        <a href="{{ route('godmode.agencies.show', $agency) }}" class="btn btn-xs btn-ghost">View</a>
                        <a href="{{ route('godmode.agencies.edit', $agency) }}" class="btn btn-xs btn-ghost">Edit</a>

                        {{-- Archive / Activate. NOTHING here deletes: see §1. --}}
                        @if ($agency->status === 'active')
                            <form method="POST" action="{{ route('godmode.agencies.archive', $agency) }}" class="inline"
                                  onsubmit="return confirm(@js('Archive '.$agency->name.'? It has '.number_format((int) $agency->applicants_count).' applicants and '.(int) $agency->users_count.' users. Their logins stop working.'));">
                                @csrf
                                <input type="hidden" name="_return" value="{{ $returnQuery }}">
                                <button type="submit" class="btn btn-xs btn-warning">Archive</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('godmode.agencies.activate', $agency) }}" class="inline"
                                  onsubmit="return confirm(@js('Activate '.$agency->name.'? Its subdomain starts serving again.'));">
                                @csrf
                                <input type="hidden" name="_return" value="{{ $returnQuery }}">
                                <button type="submit" class="btn btn-xs btn-success">Activate</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-8 opacity-60">
                        No agencies match these filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex items-center justify-between gap-4">
    <span class="text-xs opacity-60">
        @if ($agencies->total() > 0)
            Showing {{ $agencies->firstItem() }}–{{ $agencies->lastItem() }} of {{ $agencies->total() }}
        @else
            No rows
        @endif
    </span>
    {{ $agencies->links() }}
</div>
@endsection
