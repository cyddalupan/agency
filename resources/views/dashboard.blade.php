@extends('layouts.app')

@section('title', 'Dashboard')

@push('head')
<script src="{{ asset('d3.min.js') }}"></script>
@endpush

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

    {{-- D3 Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Status Pipeline Bar Chart --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">📊 Applicant Status Pipeline</h3>
                <div id="status-chart" class="w-full" style="height: 260px;"></div>
            </div>
        </div>

        {{-- Monthly Trends Line Chart --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">📈 Monthly Applications (12mo)</h3>
                <div id="trends-chart" class="w-full" style="height: 260px;"></div>
            </div>
        </div>

        {{-- Deployment Pipeline (badges) --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">📋 Deployment Pipeline</h3>
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

        {{-- Employer Growth / Pie --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">📈 Employer Growth (12mo)</h3>
                <div id="employer-chart" class="w-full" style="height: 260px;"></div>
            </div>
        </div>
    </div>

    <div class="sparkle-divider"></div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    // ===== Status Bar Chart =====
    (function statusBar() {
        const container = document.getElementById('status-chart');
        if (!container) return;
        const data = @json($chartStatusData);

        if (!data || !data.length) {
            container.innerHTML = '<div class="flex items-center justify-center h-full text-sm opacity-50">No data yet</div>';
            return;
        }

        const margin = {top: 20, right: 20, bottom: 30, left: 100};
        const width = container.clientWidth || 500;
        const height = 260;
        const innerW = width - margin.left - margin.right;
        const innerH = height - margin.top - margin.bottom;

        const svg = d3.select(container).append('svg')
            .attr('width', width).attr('height', height)
            .append('g').attr('transform', `translate(${margin.left},${margin.top})`);

        const x = d3.scaleLinear().domain([0, d3.max(data, d => d.count)]).range([0, innerW]);
        const y = d3.scaleBand().domain(data.map(d => d.label)).range([0, innerH]).padding(0.2);

        svg.selectAll('.bar').data(data).join('rect')
            .attr('class', 'bar')
            .attr('y', d => y(d.label))
            .attr('height', y.bandwidth())
            .attr('x', 0)
            .attr('width', d => x(d.count))
            .attr('fill', d => d.color)
            .attr('rx', 4)
            .attr('opacity', 0.9);

        svg.selectAll('.label').data(data).join('text')
            .attr('x', d => x(d.count) + 6)
            .attr('y', d => y(d.label) + y.bandwidth() / 2 + 4)
            .text(d => d.count)
            .attr('font-size', '12px')
            .attr('fill', '#666');

        svg.append('g').call(d3.axisLeft(y).tickSize(0)).selectAll('text')
            .attr('font-size', '11px').attr('fill', '#888');
        svg.selectAll('.domain, .tick line').attr('stroke', 'none');
        svg.selectAll('.tick line').attr('stroke', '#eee').attr('stroke-dasharray', '3,3');
    })();

    // ===== Monthly Trends Line =====
    (function trendsLine() {
        const container = document.getElementById('trends-chart');
        if (!container) return;
        const data = @json(collect($monthlyTotals)->map(fn($v, $k) => ['month' => $k, 'total' => $v])->values());

        if (!data.length) {
            container.innerHTML = '<div class="flex items-center justify-center h-full text-sm opacity-50">No data yet</div>';
            return;
        }

        const margin = {top: 20, right: 20, bottom: 30, left: 40};
        const width = container.clientWidth || 500;
        const height = 260;
        const innerW = width - margin.left - margin.right;
        const innerH = height - margin.top - margin.bottom;

        const svg = d3.select(container).append('svg')
            .attr('width', width).attr('height', height)
            .append('g').attr('transform', `translate(${margin.left},${margin.top})`);

        const x = d3.scalePoint().domain(data.map(d => d.month)).range([0, innerW]);
        const y = d3.scaleLinear().domain([0, d3.max(data, d => d.total) * 1.1 || 5]).range([innerH, 0]);

        const line = d3.line().x(d => x(d.month)).y(d => y(d.total)).curve(d3.curveMonotoneX);

        // Area
        svg.append('path')
            .datum(data)
            .attr('fill', 'url(#trend-gradient)')
            .attr('d', d3.area().x(d => x(d.month)).y0(innerH).y1(d => y(d.total)).curve(d3.curveMonotoneX));

        // Line
        svg.append('path')
            .datum(data)
            .attr('fill', 'none')
            .attr('stroke', '#3b82f6')
            .attr('stroke-width', 2.5)
            .attr('d', line);

        // Dots
        svg.selectAll('.dot').data(data).join('circle')
            .attr('cx', d => x(d.month))
            .attr('cy', d => y(d.total))
            .attr('r', 4)
            .attr('fill', '#3b82f6')
            .attr('stroke', '#fff')
            .attr('stroke-width', 2);

        svg.append('g').call(d3.axisLeft(y).ticks(5)).selectAll('text')
            .attr('font-size', '10px').attr('fill', '#999');
        svg.append('g').attr('transform', `translate(0,${innerH})`)
            .call(d3.axisBottom(x).tickFormat(d => d.slice(5)))
            .selectAll('text').attr('font-size', '10px').attr('fill', '#999');
        svg.selectAll('.domain').attr('stroke', '#ddd');

        // Gradient
        const defs = svg.append('defs');
        defs.append('linearGradient').attr('id', 'trend-gradient').attr('x1', '0%').attr('y1', '0%').attr('x2', '0%').attr('y2', '100%')
            .append('stop').attr('offset', '0%').attr('stop-color', '#3b82f6').attr('stop-opacity', 0.2)
            .append('stop').attr('offset', '100%').attr('stop-color', '#3b82f6').attr('stop-opacity', 0.02);
    })();

    // ===== Employer Growth Bar Chart =====
    (function employerBar() {
        const container = document.getElementById('employer-chart');
        if (!container) return;
        const data = @json(collect($employerGrowth)->map(fn($v, $k) => ['month' => $k, 'total' => $v])->values());

        if (!data.length) {
            container.innerHTML = '<div class="flex items-center justify-center h-full text-sm opacity-50">No data yet</div>';
            return;
        }

        const margin = {top: 20, right: 20, bottom: 30, left: 40};
        const width = container.clientWidth || 500;
        const height = 260;
        const innerW = width - margin.left - margin.right;
        const innerH = height - margin.top - margin.bottom;

        const svg = d3.select(container).append('svg')
            .attr('width', width).attr('height', height)
            .append('g').attr('transform', `translate(${margin.left},${margin.top})`);

        const x = d3.scaleBand().domain(data.map(d => d.month)).range([0, innerW]).padding(0.3);
        const y = d3.scaleLinear().domain([0, d3.max(data, d => d.total) * 1.2 || 5]).range([innerH, 0]);

        svg.selectAll('.bar').data(data).join('rect')
            .attr('x', d => x(d.month))
            .attr('width', x.bandwidth())
            .attr('y', d => y(d.total))
            .attr('height', d => innerH - y(d.total))
            .attr('fill', '#10b981')
            .attr('rx', 3)
            .attr('opacity', 0.85);

        svg.selectAll('.bar-label').data(data).join('text')
            .attr('x', d => x(d.month) + x.bandwidth() / 2)
            .attr('y', d => y(d.total) - 6)
            .text(d => d.total)
            .attr('text-anchor', 'middle')
            .attr('font-size', '11px')
            .attr('fill', '#666');

        svg.append('g').call(d3.axisLeft(y).ticks(5)).selectAll('text')
            .attr('font-size', '10px').attr('fill', '#999');
        svg.append('g').attr('transform', `translate(0,${innerH})`)
            .call(d3.axisBottom(x).tickFormat(d => d.slice(5)))
            .selectAll('text').attr('font-size', '10px').attr('fill', '#999');
        svg.selectAll('.domain').attr('stroke', '#ddd');
    })();
})();
</script>
@endpush