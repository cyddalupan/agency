@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Welcome Banner --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-8 card-lift">
        <div class="card-body p-6 lg:p-8">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold mb-1">
                        @if ($agency)
                            {{ $agency->name }}
                        @else
                            🌟 Super Admin Dashboard
                        @endif
                    </h1>
                    <p class="opacity-80 text-lg">Welcome back, {{ $user->name }}! 👋</p>
                </div>
                <span class="text-4xl hidden sm:block">🚀</span>
            </div>
            <div class="mt-4 flex flex-wrap gap-2 text-sm opacity-75">
                <span>📅 {{ now()->format('l, F j, Y') }}</span>
                <span class="opacity-30">|</span>
                <span>🕐 {{ now()->format('h:i A') }}</span>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @if ($agency)
            <div class="card bg-base-100 shadow-sm card-lift border-l-4 border-primary">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h3 class="card-title text-sm uppercase tracking-wider opacity-60">👥 Applicants</h3>
                        <div class="stat-icon bg-primary/10 text-primary">👤</div>
                    </div>
                    <p class="text-4xl font-bold mt-2">0</p>
                    <p class="text-xs opacity-60 mt-1">Total active applicants</p>
                    <div class="mt-3">
                        <a href="{{ route('applicants.index') }}" class="link link-primary text-sm">View all →</a>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm card-lift border-l-4 border-secondary">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h3 class="card-title text-sm uppercase tracking-wider opacity-60">🏢 Employers</h3>
                        <div class="stat-icon bg-secondary/10 text-secondary">🏢</div>
                    </div>
                    <p class="text-4xl font-bold mt-2">0</p>
                    <p class="text-xs opacity-60 mt-1">Active employers</p>
                    <div class="mt-3">
                        <a href="{{ route('employers.index') }}" class="link link-secondary text-sm">View all →</a>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm card-lift border-l-4 border-accent">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h3 class="card-title text-sm uppercase tracking-wider opacity-60">💼 Job Positions</h3>
                        <div class="stat-icon bg-accent/10 text-accent">💼</div>
                    </div>
                    <p class="text-4xl font-bold mt-2">0</p>
                    <p class="text-xs opacity-60 mt-1">Open positions</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm card-lift border-l-4 border-warning">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h3 class="card-title text-sm uppercase tracking-wider opacity-60">✈️ Deployed</h3>
                        <div class="stat-icon bg-warning/10 text-warning">✈️</div>
                    </div>
                    <p class="text-4xl font-bold mt-2">0</p>
                    <p class="text-xs opacity-60 mt-1">This month</p>
                </div>
            </div>
        @else
            <div class="card bg-base-100 shadow-sm card-lift border-l-4 border-primary">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h3 class="card-title text-sm uppercase tracking-wider opacity-60">🏛️ Agencies</h3>
                        <div class="stat-icon bg-primary/10 text-primary">🏛️</div>
                    </div>
                    <p class="text-4xl font-bold mt-2">{{ \App\Models\Agency::count() }}</p>
                    <p class="text-xs opacity-60 mt-1">Registered agencies</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm card-lift border-l-4 border-secondary">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h3 class="card-title text-sm uppercase tracking-wider opacity-60">👥 Users</h3>
                        <div class="stat-icon bg-secondary/10 text-secondary">👥</div>
                    </div>
                    <p class="text-4xl font-bold mt-2">{{ \App\Models\User::count() }}</p>
                    <p class="text-xs opacity-60 mt-1">Total users</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm card-lift border-l-4 border-accent">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h3 class="card-title text-sm uppercase tracking-wider opacity-60">🏢 Employers</h3>
                        <div class="stat-icon bg-accent/10 text-accent">🏢</div>
                    </div>
                    <p class="text-4xl font-bold mt-2">{{ \App\Models\Employer::count() }}</p>
                    <p class="text-xs opacity-60 mt-1">Total employers</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm card-lift border-l-4 border-warning">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h3 class="card-title text-sm uppercase tracking-wider opacity-60">👤 Applicants</h3>
                        <div class="stat-icon bg-warning/10 text-warning">👤</div>
                    </div>
                    <p class="text-4xl font-bold mt-2">{{ \App\Models\Applicant::count() }}</p>
                    <p class="text-xs opacity-60 mt-1">Total applicants</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Quick Actions + Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Quick Actions --}}
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

        {{-- Recent Activity / Placeholder --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-lg mb-2">📋 Recent Activity</h3>
                <div class="space-y-3">
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-base-200/50">
                        <span class="text-xl">👋</span>
                        <div>
                            <p class="text-sm font-medium">Welcome to {{ app_brand_name() }}!</p>
                            <p class="text-xs opacity-50 mt-0.5">Start by adding applicants and employers</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-base-200/50">
                        <span class="text-xl">💡</span>
                        <div>
                            <p class="text-sm font-medium">Pro Tip</p>
                            <p class="text-xs opacity-50 mt-0.5">Add an employer first, then create job positions for them</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-base-200/50">
                        <span class="text-xl">🚀</span>
                        <div>
                            <p class="text-sm font-medium">System Ready</p>
                            <p class="text-xs opacity-50 mt-0.5">All modules are online and ready to use</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sparkle-divider"></div>

    {{-- Status Pipeline Overview --}}
    @if ($agency)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h3 class="card-title text-lg mb-4">📈 Deployment Pipeline</h3>
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
            @if($recentApplicants->isNotEmpty())
            <div class="mt-4 space-y-2">
                @foreach($recentApplicants as $app)
                <div class="flex justify-between items-center py-1 text-sm">
                    <a href="{{ route('applicants.show', $app) }}" class="link link-primary">
                        {{ $app->first_name }} {{ $app->last_name }}
                    </a>
                    @if($app->statusCode)
                    <span class="badge badge-sm"
                        style="background-color: {{ $app->statusCode->color ?? '#e5e7eb' }}20; color: {{ $app->statusCode->color ?? '#374151' }}">
                        {{ $app->statusCode->label }}
                    </span>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm opacity-50 mt-4">📊 No applicants yet.</p>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection