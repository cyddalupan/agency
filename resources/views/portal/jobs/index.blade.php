@extends('portal.layouts.app')

@section('title', 'Job Listings')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">📋 Available Positions</h1>
            <p class="text-sm opacity-60 mt-1">Browse open job opportunities</p>
        </div>
        <a href="{{ route('portal.dashboard') }}" class="btn btn-ghost btn-sm">
            ← Back to Dashboard
        </a>
    </div>

    {{-- Job cards --}}
    @forelse($jobs as $job)
    <div class="card bg-base-100 shadow-sm mb-4 hover:shadow-md transition-shadow">
        <div class="card-body p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <h3 class="card-title text-lg">
                        <a href="{{ route('portal.jobs.show', $job) }}" class="hover:text-primary transition-colors">
                            {{ $job->employer?->name ?? 'Unknown Employer' }}
                        </a>
                    </h3>
                    <p class="text-sm font-medium mt-1">{{ $job->name }}</p>

                    <div class="flex flex-wrap gap-3 mt-3">
                        @if($job->salary)
                        <span class="badge badge-outline gap-1">
                            💰 {{ number_format($job->salary, 2) }}
                        </span>
                        @endif
                        @if($job->total_slots > 0)
                        <span class="badge badge-outline gap-1">
                            👥 {{ $job->total_slots - $job->occupied }} / {{ $job->total_slots }} slots
                        </span>
                        @endif
                        @if($job->gender_preference && $job->gender_preference !== 'any')
                        <span class="badge badge-outline gap-1">
                            ⚤ {{ ucfirst($job->gender_preference) }}
                        </span>
                        @endif
                        @if($job->position)
                        <span class="badge badge-ghost gap-1">
                            🏷️ {{ $job->position->name }}
                        </span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('portal.jobs.show', $job) }}" class="btn btn-primary btn-sm">
                    View Details →
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-8 text-center">
            <div class="text-4xl mb-4">📭</div>
            <h3 class="text-lg font-semibold">No Open Positions</h3>
            <p class="text-sm opacity-60 mt-1">There are no available job openings right now. Check back later!</p>
        </div>
    </div>
    @endforelse
</div>
@endsection
