{{-- D3 Charts: Status Pipeline Bar + Monthly Trends Line + Employer Growth Bar --}}
{{-- Expects: chartStatusData (collection), monthlyTotals (array), employerGrowth (array) --}}

<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <h3 class="card-title text-lg mb-4">📊 Applicant Status Pipeline</h3>
        <div id="status-chart" class="w-full" style="height: 260px;"></div>
    </div>
</div>

<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <h3 class="card-title text-lg mb-4">📈 Monthly Applications (12mo)</h3>
        <div id="trends-chart" class="w-full" style="height: 260px;"></div>
    </div>
</div>

<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <h3 class="card-title text-lg mb-4">🍩 Status Distribution</h3>
        <div id="pipeline-chart" class="w-full" style="height: 260px;"></div>
    </div>
</div>

<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <h3 class="card-title text-lg mb-4">📈 Employer Growth (12mo)</h3>
        <div id="employer-chart" class="w-full" style="height: 260px;"></div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    // ===== Status Bar Chart =====
    (function statusBar() {
        const container = document.getElementById('status-chart');
        if (!container) return;
        const data = @json($chartStatusData ?? collect());
        if (!data || !data.length) {
            container.innerHTML = '<div class="flex items-center justify-center h-full text-sm opacity-50">No data yet</div>';
            return;
        }
        const margin = {top: 20, right: 40, bottom: 30, left: 110};
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
            .attr('y', d => y(d.label))
            .attr('height', y.bandwidth())
            .attr('width', d => x(d.count))
            .attr('fill', d => d.color).attr('rx', 4).attr('opacity', 0.9);
        svg.selectAll('.label').data(data).join('text')
            .attr('x', d => x(d.count) + 6)
            .attr('y', d => y(d.label) + y.bandwidth() / 2 + 4)
            .text(d => d.count).attr('font-size', '12px').attr('fill', '#666');
        svg.append('g').call(d3.axisLeft(y).tickSize(0)).selectAll('text')
            .attr('font-size', '11px').attr('fill', '#888');
        svg.selectAll('.domain, .tick line').attr('stroke', 'none');
    })();

    // ===== Monthly Trends Line =====
    (function trendsLine() {
        const container = document.getElementById('trends-chart');
        if (!container) return;
        const data = @json(collect($monthlyTotals ?? [])->map(fn($v, $k) => ['month' => $k, 'total' => $v])->values());
        if (!data || !data.length) {
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
        svg.append('path').datum(data)
            .attr('fill', 'url(#trend-gradient)')
            .attr('d', d3.area().x(d => x(d.month)).y0(innerH).y1(d => y(d.total)).curve(d3.curveMonotoneX));
        svg.append('path').datum(data)
            .attr('fill', 'none').attr('stroke', '#3b82f6').attr('stroke-width', 2.5)
            .attr('d', d3.line().x(d => x(d.month)).y(d => y(d.total)).curve(d3.curveMonotoneX));
        svg.selectAll('.dot').data(data).join('circle')
            .attr('cx', d => x(d.month)).attr('cy', d => y(d.total))
            .attr('r', 4).attr('fill', '#3b82f6').attr('stroke', '#fff').attr('stroke-width', 2);
        svg.append('g').call(d3.axisLeft(y).ticks(5)).selectAll('text').attr('font-size', '10px').attr('fill', '#999');
        svg.append('g').attr('transform', `translate(0,${innerH})`)
            .call(d3.axisBottom(x).tickFormat(d => d.slice(5)))
            .selectAll('text').attr('font-size', '10px').attr('fill', '#999');
        svg.selectAll('.domain').attr('stroke', '#ddd');
        const defs = svg.append('defs');
        defs.append('linearGradient').attr('id', 'trend-gradient')
            .attr('x1', '0%').attr('y1', '0%').attr('x2', '0%').attr('y2', '100%')
            .append('stop').attr('offset', '0%').attr('stop-color', '#3b82f6').attr('stop-opacity', 0.2)
            .append('stop').attr('offset', '100%').attr('stop-color', '#3b82f6').attr('stop-opacity', 0.02);
    })();

    // ===== Pipeline Donut Chart =====
    (function pipelineDonut() {
        const container = document.getElementById('pipeline-chart');
        if (!container) return;
        const data = @json($chartStatusData ?? collect());
        if (!data || !data.length) {
            container.innerHTML = '<div class="flex items-center justify-center h-full text-sm opacity-50">No data yet</div>';
            return;
        }
        const total = d3.sum(data, d => d.count);
        const width = container.clientWidth || 500;
        const height = 260;
        const radius = Math.min(width, height * 1.2) / 2.2;
        const svg = d3.select(container).append('svg')
            .attr('width', width).attr('height', height)
            .append('g').attr('transform', `translate(${width / 2},${height / 2})`);

        const pie = d3.pie().value(d => d.count).sort(null);
        const arc = d3.arc().innerRadius(radius * 0.55).outerRadius(radius);
        const outerArc = d3.arc().innerRadius(radius * 0.65).outerRadius(radius * 0.65);

        // Arcs
        svg.selectAll('.arc').data(pie(data)).join('path')
            .attr('d', arc)
            .attr('fill', d => d.data.color)
            .attr('stroke', '#fff')
            .attr('stroke-width', 2)
            .attr('opacity', 0.9);

        // Center total
        svg.append('text').attr('text-anchor', 'middle').attr('y', -6)
            .attr('font-size', '24px').attr('font-weight', 'bold').attr('fill', '#333')
            .text(total);
        svg.append('text').attr('text-anchor', 'middle').attr('y', 14)
            .attr('font-size', '11px').attr('fill', '#888')
            .text('Total');

        // Legend
        const legendG = svg.append('g').attr('transform', `translate(${radius + 20}, ${-radius * 0.7})`);
        data.forEach((d, i) => {
            const row = legendG.append('g').attr('transform', `translate(0, ${i * 22})`);
            row.append('rect').attr('width', 12).attr('height', 12).attr('rx', 2)
                .attr('fill', d.color).attr('opacity', 0.9);
            row.append('text').attr('x', 18).attr('y', 10)
                .attr('font-size', '11px').attr('fill', '#555')
                .text(`${d.label} (${d.count})`);
        });
    })();

    // ===== Employer Growth Bar =====
    (function employerBar() {
        const container = document.getElementById('employer-chart');
        if (!container) return;
        const data = @json(collect($employerGrowth ?? [])->map(fn($v, $k) => ['month' => $k, 'total' => $v])->values());
        if (!data || !data.length) {
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
            .attr('x', d => x(d.month)).attr('width', x.bandwidth())
            .attr('y', d => y(d.total)).attr('height', d => innerH - y(d.total))
            .attr('fill', '#10b981').attr('rx', 3).attr('opacity', 0.85);
        svg.selectAll('.bar-label').data(data).join('text')
            .attr('x', d => x(d.month) + x.bandwidth() / 2)
            .attr('y', d => y(d.total) - 6)
            .text(d => d.total).attr('text-anchor', 'middle').attr('font-size', '11px').attr('fill', '#666');
        svg.append('g').call(d3.axisLeft(y).ticks(5)).selectAll('text').attr('font-size', '10px').attr('fill', '#999');
        svg.append('g').attr('transform', `translate(0,${innerH})`)
            .call(d3.axisBottom(x).tickFormat(d => d.slice(5)))
            .selectAll('text').attr('font-size', '10px').attr('fill', '#999');
        svg.selectAll('.domain').attr('stroke', '#ddd');
    })();
})();
</script>
@endpush
