@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">📄 Reports</h1>

    @if(isset($templates) && $templates->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-lg font-semibold mb-4">📋 Report Templates</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($templates as $t)
            <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="card-body">
                    <h3 class="card-title text-base">{{ $t->name }}</h3>
                    <p class="text-xs opacity-60">Type: {{ $t->type }}</p>
                    <div class="card-actions mt-3">
                        <a href="{{ route('reports.preview', $t) }}" class="btn btn-primary btn-sm">
                            🚀 Generate
                        </a>
                        <a href="{{ route('report-templates.edit', $t) }}" class="btn btn-ghost btn-sm">
                            Edit
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-2">
            <a href="{{ route('report-templates.index') }}" class="text-sm link link-primary">Manage Templates →</a>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('reports.applicants') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">👥 Applicant Reports</h2>
                <p class="text-sm opacity-70">Status reports filtered by country, status, and date range.</p>
            </div>
        </a>

        <a href="{{ route('reports.statistics') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">📊 Statistics Dashboard</h2>
                <p class="text-sm opacity-70">Deployment statistics and overview charts.</p>
            </div>
        </a>

        <a href="{{ route('transactions.index') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">💳 Transactions</h2>
                <p class="text-sm opacity-70">Transaction history across all accounts.</p>
            </div>
        </a>

        <a href="{{ route('accounting.employer', ['employer' => 0]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow opacity-60 pointer-events-none">
            <div class="card-body">
                <h2 class="card-title text-lg">🏢 Employer Accounting</h2>
                <p class="text-sm opacity-70">Bills, payments, and balances per employer.</p>
            </div>
        </a>

        <a href="{{ route('accounting.worker', ['applicant' => 0]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow opacity-60 pointer-events-none">
            <div class="card-body">
                <h2 class="card-title text-lg">👤 Worker Accounting</h2>
                <p class="text-sm opacity-70">Per-worker statement of account.</p>
            </div>
        </a>
    </div>

    <div class="mt-8">
        <h2 class="text-lg font-semibold mb-4">📋 Print Reports</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('reports.bill', ['bill' => 1]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow opacity-60 pointer-events-none">
                <div class="card-body">
                    <h2 class="card-title text-lg">🧾 Bill</h2>
                    <p class="text-sm opacity-70">Print a bill or invoice.</p>
                </div>
            </a>

            <a href="{{ route('reports.or', ['or' => 1]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow opacity-60 pointer-events-none">
                <div class="card-body">
                    <h2 class="card-title text-lg">📄 Official Receipt</h2>
                    <p class="text-sm opacity-70">Print an official receipt.</p>
                </div>
            </a>

            <a href="{{ route('reports.resume', ['applicant' => 1]) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow opacity-60 pointer-events-none">
                <div class="card-body">
                    <h2 class="card-title text-lg">📝 Resume</h2>
                    <p class="text-sm opacity-70">Print applicant resume with full details.</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
