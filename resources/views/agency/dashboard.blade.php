@extends('layouts.app')

@section('title', 'Agency Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Welcome Banner --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-8 card-lift">
        <div class="card-body p-6 lg:p-8">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold mb-1">
                        🏢 {{ $agency->name }}
                    </h1>
                    <p class="opacity-80 text-lg">Welcome back, {{ $user->name }}! 👋</p>
                </div>
                <span class="text-4xl hidden sm:block">🎯</span>
            </div>
            <div class="mt-4 flex flex-wrap gap-2 text-sm opacity-75">
                <span>📅 {{ now()->format('l, F j, Y') }}</span>
                <span class="opacity-30">|</span>
                <span>🕐 {{ now()->format('h:i A') }}</span>
            </div>

            @if (!is_tenant_request())
                <div role="alert" class="alert alert-warning mt-4 text-sm shadow-md">
                    <span>⚠️ Testing mode — you're logged in via the main domain. In production, this would use <strong class="font-bold">{{ $agency->subdomain }}.agency.classapparelph.com</strong>.</span>
                </div>
            @endif
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
                    <p class="text-sm opacity-60 uppercase tracking-wider font-semibold">🏢 Employers</p>
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
                    <a href="{{ route('employers.index') }}" class="link link-accent text-sm">View employers &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-lg mb-2">⚡ Quick Actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('applicants.create') }}" class="btn btn-outline btn-primary btn-block h-auto py-4 flex-col gap-1">
                        <span class="text-2xl">➕</span>
                        <span class="text-xs">New Applicant</span>
                    </a>
                    <a href="{{ route('employers.create') }}" class="btn btn-outline btn-secondary btn-block h-auto py-4 flex-col gap-1">
                        <span class="text-2xl">🏢</span>
                        <span class="text-xs">New Employer</span>
                    </a>
                    <a href="{{ route('applicants.index') }}" class="btn btn-outline btn-accent btn-block h-auto py-4 flex-col gap-1">
                        <span class="text-2xl">👥</span>
                        <span class="text-xs">Browse Applicants</span>
                    </a>
                    <a href="{{ route('employers.index') }}" class="btn btn-outline btn-info btn-block h-auto py-4 flex-col gap-1">
                        <span class="text-2xl">🏢</span>
                        <span class="text-xs">Browse Employers</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-lg mb-2">📋 Deployment Pipeline</h3>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('applicants.index') }}"
                       class="badge badge-lg {{ request()->query('status') === null ? 'badge-primary' : 'badge-ghost' }}">
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
                <p class="text-sm opacity-50 mt-3">📊 Click a status to filter recent applicants below.</p>
            </div>
        </div>
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