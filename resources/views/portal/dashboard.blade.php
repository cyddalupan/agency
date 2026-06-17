@extends('portal.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Welcome header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6">
        <div class="card-body p-8">
            <h1 class="text-2xl lg:text-3xl font-bold">Welcome, {{ $applicant->first_name }}! 👋</h1>
            <p class="opacity-80 mt-2">Here's a summary of your application.</p>
        </div>
    </div>

    {{-- Quick info cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs opacity-60 uppercase tracking-wider">Status</p>
                        @if($applicant->statusCode)
                            <p class="text-lg font-bold mt-1"
                               style="color: {{ $applicant->statusCode->color ?? '#374151' }}">
                                {{ $applicant->statusCode->labelForCountry($applicant->country?->name) }}
                            </p>
                        @else
                            <p class="text-lg font-bold mt-1 opacity-50">Pending</p>
                        @endif
                    </div>
                    <span class="text-3xl">📊</span>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs opacity-60 uppercase tracking-wider">Position</p>
                        <p class="text-lg font-bold mt-1">{{ $applicant->position?->name ?? $applicant->job?->title ?? 'Not specified' }}</p>
                    </div>
                    <span class="text-3xl">💼</span>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs opacity-60 uppercase tracking-wider">Country</p>
                        <p class="text-lg font-bold mt-1">{{ $applicant->country?->name ?? 'Not specified' }}</p>
                    </div>
                    <span class="text-3xl">🌍</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Personal details card --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="card-title flex items-center gap-2">
                    <span>📋</span> My Details
                </h3>
                <a href="{{ route('portal.profile') }}" class="btn btn-outline btn-sm">
                    View Full Profile →
                </a>
            </div>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Full Name</dt>
                    <dd class="font-medium mt-1">{{ $applicant->full_name }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Email</dt>
                    <dd class="font-medium mt-1">{{ $applicant->email ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Contact</dt>
                    <dd class="font-medium mt-1">{{ $applicant->contact ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Gender</dt>
                    <dd class="font-medium mt-1">{{ $applicant->gender ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Status History --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-6">
            <h3 class="card-title flex items-center gap-2 mb-4">
                <span>📜</span> Status History
            </h3>
            @if($logs->isNotEmpty())
                <ul class="relative">
                    <div class="absolute left-4 top-2 bottom-2 w-0.5 bg-base-300"></div>
                    @foreach($logs as $log)
                        <li class="relative pl-10 pb-6 last:pb-0">
                            {{-- Timeline dot --}}
                            <div class="absolute left-2.5 top-1.5 w-3 h-3 rounded-full border-2 border-primary bg-base-100"></div>

                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    <span class="badge badge-sm">{{ $log->new_status }}</span>
                                    @if($log->notes)
                                        <p class="text-sm mt-1 opacity-70">{{ $log->notes }}</p>
                                    @endif
                                </div>
                                <span class="text-xs opacity-50 whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm opacity-50 text-center py-4">No status changes recorded yet.</p>
            @endif
        </div>
    </div>

    {{-- Browse jobs CTA --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-6 text-center">
            <h3 class="card-title justify-center mb-2">📋 Looking for a Job?</h3>
            <p class="opacity-60 text-sm mb-4">Browse open positions available for you.</p>
            <a href="{{ route('portal.jobs.index') }}" class="btn btn-primary">
                Browse Available Positions →
            </a>
        </div>
    </div>
</div>
@endsection
