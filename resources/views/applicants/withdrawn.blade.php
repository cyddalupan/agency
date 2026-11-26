@extends('layouts.app')

@section('title', 'Backout, Cancelled & Repat')

@section('content')
<div class="w-full">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>🗂️</span> Backout, Cancelled & Repat
            </h2>
            <p class="opacity-60 text-sm mt-1">Applicants with Cancel, Backout, or Repatriated status</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('applicants.index') }}" class="btn btn-outline">
                <span>👥</span> All Applicants
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Status Pipeline Chips (only the 3 withdrawn statuses) --}}
    <div class="flex flex-wrap gap-2 mb-3">
        <a href="{{ route('applicants.withdrawn', request()->only(['type'])) }}"
           class="btn btn-xs gap-1 {{ request()->query('status') === null ? 'btn-primary' : 'btn-ghost' }}">
            All
            <span class="badge badge-xs {{ request()->query('status') === null ? 'badge-outline' : '' }}">{{ $statusCounts->sum() }}</span>
        </a>
        @foreach($statusCodes as $sc)
            @php $count = $statusCounts->get($sc->code, 0); @endphp
            @if($count > 0)
            <a href="{{ route('applicants.withdrawn', request()->only(['type']) + ['status' => $sc->code]) }}"
               class="btn btn-xs gap-1 {{ request('status') === (string)$sc->code ? 'btn-primary' : 'btn-ghost' }}"
               @if(request('status') !== (string)$sc->code) style="background-color: {{ $sc->color ?? '#e5e7eb' }}15;" @endif>
                {{ $sc->label }}
                <span class="badge badge-xs {{ request('status') === (string)$sc->code ? 'badge-outline' : '' }}">{{ $count }}</span>
            </a>
            @endif
        @endforeach
    </div>

    {{-- (Mjolnir "Applicant module Tabs" 2026-11-05) Client-specified order:
         ALL TYPES -> HOUSEHOLD -> SKILLED. Gulf hides the HOUSEHOLD/SKILLED
         counters, FINAS keeps them; ALL TYPES always shows its count. --}}
    @php
        $activeType = $type ?? 'skilled';
        $showProfessional = \App\Models\Applicant::hasProfessionalTab(auth()->user()->agency_id ?? null);
    @endphp
    <div class="grid {{ $showProfessional ? 'grid-cols-4' : 'grid-cols-3' }} gap-3 mb-6 rounded-2xl bg-base-200/70 p-2 border border-base-300">
        <a href="{{ route('applicants.withdrawn', array_merge(request()->except(['type', 'page']), ['type' => 'all'])) }}"
           class="flex items-center justify-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-5 py-4 text-base sm:text-lg font-extrabold tracking-wide whitespace-nowrap transition-all {{ $activeType === 'all' ? 'bg-neutral text-neutral-content shadow-lg' : 'bg-base-100 text-base-content/70 hover:bg-base-300' }}">
            <span class="text-2xl shrink-0">👥</span><span>ALL TYPES</span>
            <span class="badge badge-lg shrink-0 {{ $activeType === 'all' ? 'badge-neutral' : 'badge-ghost' }}">{{ $typeCounts['all'] ?? 0 }}</span>
        </a>
        <a href="{{ route('applicants.withdrawn', array_merge(request()->except(['type', 'page']), ['type' => 'household'])) }}"
           class="flex items-center justify-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-5 py-4 text-base sm:text-lg font-extrabold tracking-wide whitespace-nowrap transition-all {{ $activeType === 'household' ? 'bg-primary text-primary-content shadow-lg' : 'bg-base-100 text-base-content/70 hover:bg-base-300' }}">
            <span class="text-2xl shrink-0">🏠</span><span>HOUSEHOLD</span>
            @unless (hide_type_tab_count('household'))
            <span class="badge badge-lg shrink-0 {{ $activeType === 'household' ? 'badge-neutral' : 'badge-ghost' }}">{{ $typeCounts['household'] ?? 0 }}</span>
            @endunless
        </a>
        <a href="{{ route('applicants.withdrawn', array_merge(request()->except(['type', 'page']), ['type' => 'skilled'])) }}"
           class="flex items-center justify-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-5 py-4 text-base sm:text-lg font-extrabold tracking-wide whitespace-nowrap transition-all {{ $activeType === 'skilled' ? 'bg-secondary text-secondary-content shadow-lg' : 'bg-base-100 text-base-content/70 hover:bg-base-300' }}">
            <span class="text-2xl shrink-0">💼</span><span>SKILLED</span>
            @unless (hide_type_tab_count('skilled'))
            <span class="badge badge-lg shrink-0 {{ $activeType === 'skilled' ? 'badge-neutral' : 'badge-ghost' }}">{{ $typeCounts['skilled'] ?? 0 }}</span>
            @endunless
        </a>
        @if($showProfessional)
        <a href="{{ route('applicants.withdrawn', array_merge(request()->except(['type', 'page']), ['type' => 'professional'])) }}"
           class="flex items-center justify-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-5 py-4 text-base sm:text-lg font-extrabold tracking-wide whitespace-nowrap transition-all {{ $activeType === 'professional' ? 'bg-accent text-accent-content shadow-lg' : 'bg-base-100 text-base-content/70 hover:bg-base-300' }}">
            <span class="text-2xl shrink-0">🎓</span><span>PROFESSIONAL</span>
            <span class="badge badge-lg shrink-0 {{ $activeType === 'professional' ? 'badge-neutral' : 'badge-ghost' }}">{{ $typeCounts['professional'] ?? 0 }}</span>
        </a>
        @endif
    </div>

    {{-- Filters remain visible on zero results (see index()). --}}
    {{-- Search & Filters --}}
        <form method="GET" action="{{ route('applicants.withdrawn') }}" class="mb-4" id="filter-form">
            <input type="hidden" name="type" value="{{ $type ?? 'skilled' }}">
            {{-- Row 1: Search bar full width --}}
            <div class="flex gap-3 items-end mb-3">
                <div class="form-control flex-1">
                    <label class="input input-bordered flex items-center gap-2">
                        <span>🔍</span>
                        <input type="text" name="search" class="grow" placeholder="Search by name, email, or contact..."
                               value="{{ request('search') }}" />
                        @if(($filtersActive ?? false))
                            <a href="{{ route('applicants.withdrawn', ['type' => $type]) }}" class="btn btn-ghost btn-xs btn-square" title="Clear filters">✕</a>
                        @endif
                    </label>
                </div>
            </div>

            {{-- Row 2: Filters in a responsive grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 items-end">
                {{-- Status filter (from pipeline chips) --}}
                <div class="form-control w-full">
                    <select name="status" class="select select-bordered select-sm" onchange="this.form.submit()">
                        <option value="">📊 All Status</option>
                        @foreach($statusCodes as $sc)
                            <option value="{{ $sc->code }}" {{ request('status') === (string)$sc->code ? 'selected' : '' }}>
                                {{ $sc->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Gender filter --}}
                <div class="form-control w-full">
                    <select name="gender" class="select select-bordered select-sm" onchange="this.form.submit()">
                        <option value="">⚤ Gender</option>
                        <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>👨 Male</option>
                        <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>👩 Female</option>
                    </select>
                </div>

                {{-- Employer filter --}}
                <div class="form-control w-full">
                    <select name="employer" class="select select-bordered select-sm" onchange="this.form.submit()">
                        <option value="">🏢 Employer</option>
                        @foreach($employers as $employer)
                            <option value="{{ $employer->id }}" {{ request('employer') == $employer->id ? 'selected' : '' }}>
                                {{ $employer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Country filter --}}
                <div class="form-control w-full">
                    <select name="country" class="select select-bordered select-sm" onchange="this.form.submit()">
                        <option value="">🌍 Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ request('country') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Branch filter (card "Applicant Branch filter", 2026-10-22) --}}
                <div class="form-control w-full">
                    <select name="branch" class="select select-bordered select-sm" onchange="this.form.submit()">
                        <option value="">🏬 Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Submit / Clear --}}
                <div class="flex gap-2 items-end">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Search</button>
                    @if(($filtersActive ?? false))
                        <a href="{{ route('applicants.withdrawn', ['type' => $type]) }}" class="btn btn-ghost btn-sm">✕ Clear</a>
                    @endif
                </div>
            </div>

            @include('applicants.partials.advanced-search', ['clearUrl' => route('applicants.withdrawn', ['type' => $type])])
        </form>

        {{-- Stats summary + Export --}}
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4 text-sm">
            <div class="flex flex-wrap gap-2">
                <span class="badge badge-ghost badge-lg">📋 {{ $applicants->total() }} total</span>
                @if(($filtersActive ?? false))
                    <span class="badge badge-warning badge-lg">🔍 {{ $applicants->total() }} matching</span>
                @endif
            </div>
            <div class="flex gap-2">
                {{-- except(['page']) rather than a hand-listed subset: a
                     subset silently dropped the Part A filters. --}}
                <a href="{{ route('applicants.withdrawn.export', request()->except(['page'])) }}"
                   class="btn btn-sm btn-outline btn-success gap-1">
                    <span>📥</span> Export to CSV
                </a>
                <a href="{{ route('applicants.withdrawn.export.pdf', request()->except(['page', 'print'])) }}"
                   class="btn btn-sm btn-outline btn-error gap-1">
                    <span>📄</span> Export to PDF
                </a>
            </div>
        </div>

        @php ob_start(); @endphp
        @include('applicants.partials.withdrawn-headers')
        @php $thHeaders = ob_get_clean(); @endphp
        @if($applicants->count())
        {{-- Infinite scroll (card 6b07e35d), same behaviour as the main list. --}}
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                @include('applicants.partials.infinite-scroll', [
                    'applicants' => $applicants,
                    'rowPartial' => 'applicants.partials.withdrawn-rows',
                    'context' => 'withdrawn',
                    'tableColumns' => [],
                    'customHeaders' => $thHeaders,
                ])
            </div>
        </div>
    @else
        @php $hasFilters = ($filtersActive ?? false); @endphp
        {{-- Empty state --}}
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">{{ $hasFilters ? '🔍' : '👤' }}</span>
                <h3 class="text-xl font-bold mb-2">{{ $hasFilters ? 'No applicants match your filters' : 'No applicants found' }}</h3>
                <p class="opacity-60 mb-6 max-w-md">
                    {{ $hasFilters
                        ? 'Try a different keyword or clear the filters to see the full list.'
                        : 'No applicants currently have Cancel, Backout, or Repatriated status.' }}
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    @if($hasFilters)
                        <a href="{{ route('applicants.withdrawn', ['type' => $type]) }}" class="btn btn-primary btn-lg">
                            <span>✕</span> Clear Filters
                        </a>
                    @else
                        <a href="{{ route('applicants.index') }}" class="btn btn-primary btn-lg">
                            <span>👥</span> Go to All Applicants
                        </a>
                        <a href="{{ route('employers.create') }}" class="btn btn-outline btn-lg">
                            <span>🏢</span> Add an Employer First
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick tips --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">📝</span>
                    <h4 class="font-semibold text-sm">Fill Details</h4>
                    <p class="text-xs opacity-60 mt-1">Add personal info, education, work history</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">📄</span>
                    <h4 class="font-semibold text-sm">Upload Docs</h4>
                    <p class="text-xs opacity-60 mt-1">Passport, certificates, requirements</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">✈️</span>
                    <h4 class="font-semibold text-sm">Track Status</h4>
                    <p class="text-xs opacity-60 mt-1">Follow the pipeline from pending to deployed</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection