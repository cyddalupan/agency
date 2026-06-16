@extends('layouts.employer-app')

@section('title', 'Employer Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Welcome Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Welcome, {{ $user->name }} 👋</h1>
            <p class="text-base-content/60">{{ $employer->name }}</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="stat bg-base-100 rounded-xl shadow-sm border border-base-200">
            <div class="stat-figure text-primary text-3xl">📋</div>
            <div class="stat-title">Active Job Positions</div>
            <div class="stat-value text-primary">{{ $totalJobs }}</div>
            <div class="stat-desc">{{ $totalJobs > 0 ? 'Click to view job details' : 'No job positions yet' }}</div>
        </div>
        <div class="stat bg-base-100 rounded-xl shadow-sm border border-base-200">
            <div class="stat-figure text-secondary text-3xl">👥</div>
            <div class="stat-title">Total Applicants</div>
            <div class="stat-value text-secondary">{{ $totalApplicants }}</div>
            <div class="stat-desc">Applicants assigned to your jobs</div>
        </div>
    </div>

    {{-- Company Info --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-lg">🏢 {{ $employer->name }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3 text-sm">
                @if ($employer->contact_person)
                <div>
                    <span class="opacity-60">Contact Person:</span>
                    <p class="font-medium">{{ $employer->contact_person }}</p>
                </div>
                @endif
                @if ($employer->contact)
                <div>
                    <span class="opacity-60">Contact:</span>
                    <p class="font-medium">{{ $employer->contact }}</p>
                </div>
                @endif
                @if ($employer->email)
                <div>
                    <span class="opacity-60">Email:</span>
                    <p class="font-medium">{{ $employer->email }}</p>
                </div>
                @endif
                @if ($employer->address)
                <div>
                    <span class="opacity-60">Address:</span>
                    <p class="font-medium">{{ $employer->address }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Job Positions --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-lg">📋 Job Positions</h2>
            @if ($jobPositions->isEmpty())
                <div class="alert bg-base-200 mt-3">
                    <span>No job positions yet. Contact your agency to add job positions.</span>
                </div>
            @else
                <div class="overflow-x-auto mt-3">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Applicants</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jobPositions as $job)
                            <tr>
                                <td class="font-medium">{{ $job->name }}</td>
                                <td>
                                    @if ($job->status === 'active')
                                        <span class="badge badge-success badge-sm">Active</span>
                                    @elseif ($job->status === 'filled')
                                        <span class="badge badge-info badge-sm">Filled</span>
                                    @else
                                        <span class="badge badge-ghost badge-sm">{{ ucfirst($job->status ?? 'open') }}</span>
                                    @endif
                                </td>
                                <td>{{ $job->applicants_count ?? 0 }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
