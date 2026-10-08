@extends('layouts.app')

@section('title', 'Agency Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Welcome Banner --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-8 card-lift">
        <div class="card-body p-6 lg:p-8">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold mb-1 flex items-center gap-3">
                        @if ($agency->logo)
                            <img src="{{ Storage::url($agency->logo) }}" alt="{{ $agency->name }} icon"
                                 class="w-10 h-10 lg:w-12 lg:h-12 object-contain bg-white/90 rounded-lg p-1 shadow-sm">
                        @else
                            <span>🏢</span>
                        @endif
                        {{ $agency->name }}
                    </h1>
                    <p class="opacity-80 text-lg">Welcome back, {{ $user->name }}! 👋</p>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2 text-sm opacity-75">
                <span>📅 {{ now()->format('l, F j, Y') }}</span>
                <span class="opacity-30">|</span>
                <span>🕐 {{ now()->format('h:i A') }}</span>
            </div>


        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card bg-base-100 shadow-sm card-lift border-l-4 border-primary">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <p class="text-sm opacity-60 uppercase tracking-wider font-semibold">👥 Applicants</p>
                    <div class="stat-icon bg-primary/10 text-primary">👤</div>
                </div>
                <p class="text-4xl font-bold text-primary mt-1">{{ $stats['total_applicants'] }}</p>
                <div class="mt-3">
                    <a href="{{ route('applicants.index') }}" class="link link-primary text-sm">View all &rarr;</a>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm card-lift border-l-4 border-secondary">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <p class="text-sm opacity-60 uppercase tracking-wider font-semibold">🏢 FRAs</p>
                    <div class="stat-icon bg-secondary/10 text-secondary">🏢</div>
                </div>
                <p class="text-4xl font-bold text-secondary mt-1">{{ $stats['total_employers'] }}</p>
                <div class="mt-3">
                    <a href="{{ route('employers.index') }}" class="link link-secondary text-sm">View all &rarr;</a>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm card-lift border-l-4 border-accent">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <p class="text-sm opacity-60 uppercase tracking-wider font-semibold">💼 Job Positions</p>
                    <div class="stat-icon bg-accent/10 text-accent">💼</div>
                </div>
                <p class="text-4xl font-bold text-accent mt-1">{{ $stats['total_job_positions'] }}</p>
                <div class="mt-3">
                    <a href="{{ route('employers.index') }}" class="link link-accent text-sm">View FRAs &rarr;</a>
                </div>
            </div>
        </div>
    </div>


    {{-- "Quick Actions" card removed per client request (Mjolnir card
         "Quick Actions - REMOVE", 2026-10-08): the same links already live
         in the sidebar, so the card was redundant. Deployment Pipeline now
         spans the full width. --}}
    <div class="grid grid-cols-1 gap-6 mb-8">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-lg mb-2">📋 Deployment Pipeline</h3>

                <p class="text-xs opacity-50 uppercase tracking-wider font-semibold mb-2">By Status</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('applicants.index') }}"
                       class="badge badge-lg {{ request()->query('status') === null && request()->query('employer') === null ? 'badge-primary' : 'badge-ghost' }}">
                        📋 All
                        <span class="ml-1">{{ $statusCounts->sum() }}</span>
                    </a>
                    @foreach($statusCodes as $sc)
                        @php $count = $statusCounts->get($sc->code, 0); @endphp
                        @if($count > 0)
                        <a href="{{ route('applicants.index', ['status' => $sc->code]) }}"
                           class="badge badge-lg {{ request('status') === (string)$sc->code ? 'badge-primary' : '' }}" style="{{ request('status') === (string)$sc->code ? '' : 'background-color: ' . ($sc->color ?? '#e5e7eb') . '; color: #fff;' }}">
                            {{ $sc->label }}
                            <span class="ml-1">{{ $count }}</span>
                        </a>
                        @endif
                    @endforeach
                </div>

                {{-- Deployment Pipeline (table form) — Mjolnir card
                     "DEPLOYMENT PIPELINE - (Table form)", 2026-10-08. --}}
                <form method="GET" action="{{ route('agency.dashboard') }}"
                      class="flex flex-wrap items-end gap-3 mt-4 mb-3">
                    @if(request('status') !== null)
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div>
                        <label class="text-xs opacity-50 uppercase tracking-wider font-semibold block mb-1">Year</label>
                        <select name="pipeline_year" class="select select-bordered select-sm">
                            <option value="">All</option>
                            @foreach ($pipelineYears as $y)
                                <option value="{{ $y }}" {{ (string) request('pipeline_year') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs opacity-50 uppercase tracking-wider font-semibold block mb-1">Month</label>
                        <select name="pipeline_month" class="select select-bordered select-sm">
                            <option value="">All</option>
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ (string) request('pipeline_month') === (string) $m ? 'selected' : '' }}>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs opacity-50 uppercase tracking-wider font-semibold block mb-1">Country</label>
                        <select name="pipeline_country" class="select select-bordered select-sm">
                            <option value="">All</option>
                            @foreach ($pipelineCountries as $c)
                                <option value="{{ $c->id }}" {{ (string) request('pipeline_country') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('agency.dashboard') }}" class="btn btn-sm btn-ghost">Reset</a>
                </form>

                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">FRA</th>
                                @foreach (array_keys($pipelineStages) as $stage)
                                    <th class="text-center whitespace-nowrap">{{ $stage }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pipelineEmployers as $emp)
                                <tr>
                                    <td class="whitespace-nowrap">{{ $emp->name }}</td>
                                    @foreach (array_keys($pipelineStages) as $stage)
                                        <td class="text-center">{{ $stageTotalsByEmployer[$emp->id][$stage] ?? 0 }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($pipelineStages) + 1 }}" class="text-center opacity-60 py-4">
                                        No applicants in the pipeline stages yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="font-semibold">
                                <th class="whitespace-nowrap">TOTAL</th>
                                @foreach (array_keys($pipelineStages) as $stage)
                                    <th class="text-center">{{ $pipelineTotals[$stage] ?? 0 }}</th>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <p class="text-sm opacity-50 mt-3">📊 Click a status above to filter applicants below.</p>
            </div>
        </div>
    </div>

    {{-- D3 Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        @include('partials.dashboard-charts')
    </div>

    {{-- Recent Applicants --}}
    @if ($stats['recent_applicants']->isNotEmpty())
    <div class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">🕐 Recent Applicants</h2>
            <div class="divide-y divide-base-200">
                @foreach ($stats['recent_applicants'] as $applicant)
                <div class="py-3 flex justify-between items-center hover:bg-base-200/50 px-3 -mx-3 rounded-lg transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr($applicant->first_name, 0, 1)) }}{{ strtoupper(substr($applicant->last_name, 0, 1)) }}
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('applicants.show', $applicant) }}" class="font-medium link link-primary">
                                {{ $applicant->first_name }} {{ $applicant->last_name }}
                            </a>
                            <p class="text-xs opacity-50">{{ $applicant->email }}</p>
                        </div>
                    </div>
                    <span class="badge badge-ghost badge-sm">
                        {{ $applicant->status }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body items-center text-center py-10">
            <span class="text-5xl mb-4">👤</span>
            <h3 class="text-lg font-medium mb-2">No Applicants Yet</h3>
            <p class="opacity-60 mb-4">Start building your pipeline by adding your first applicant</p>
            <a href="{{ route('applicants.create') }}" class="btn btn-primary">
                ➕ Add Your First Applicant
            </a>
        </div>
    </div>
    @endif
</div>
@endsection