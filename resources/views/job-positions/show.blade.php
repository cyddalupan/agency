@extends('layouts.app')

@section('title', $jobPosition->name)

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="card bg-gradient-to-br from-accent via-accent/80 to-primary text-accent-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('employers.job-positions.index', $employer) }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1 mb-2">
                <span>←</span> Back to Job Positions
            </a>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-5xl">💼</span>
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold">{{ $jobPosition->name }}</h2>
                        <p class="opacity-80 text-sm mt-1">🏢 <a href="{{ route('employers.show', $employer) }}" class="underline hover:no-underline">{{ $employer->name }}</a></p>
                    </div>
                </div>
                <a href="{{ route('employers.job-positions.edit', [$employer, $jobPosition]) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
                    ✏️ Edit
                </a>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-6">
            {{-- Position Details --}}
            <div>
                <h3 class="card-title flex items-center gap-2">📋 Position Details</h3>
                <dl class="grid grid-cols-2 gap-4 mt-3">
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">🏢 Employer</dt>
                        <dd class="font-medium mt-1">
                            <a href="{{ route('employers.show', $employer) }}" class="link link-primary">{{ $employer->name }}</a>
                        </dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">📊 Status</dt>
                        <dd class="mt-1">
                            @if ($jobPosition->status === 'open')
                                <span class="badge badge-sm badge-success">🟢 Open</span>
                            @elseif ($jobPosition->status === 'filled')
                                <span class="badge badge-sm badge-info">🔵 Filled</span>
                            @else
                                <span class="badge badge-sm badge-ghost">⏸️ {{ ucfirst($jobPosition->status) }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">⚤ Gender Preference</dt>
                        <dd class="font-medium mt-1">{{ ucfirst($jobPosition->gender_preference ?? 'Any') }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">📂 Base Position</dt>
                        <dd class="font-medium mt-1">{{ $jobPosition->position->name ?? '---' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Compensation & Slots --}}
            <div class="pt-4 border-t border-base-200">
                <h3 class="card-title flex items-center gap-2">💰 Compensation & Slots</h3>
                <dl class="grid grid-cols-2 gap-4 mt-3">
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">💵 Salary</dt>
                        <dd class="font-medium mt-1">
                            @if ($jobPosition->salary)
                                <span class="text-lg font-bold text-success">${{ number_format($jobPosition->salary, 2) }}</span>
                            @else
                                <span class="opacity-40">---</span>
                            @endif
                        </dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">🎯 Slots Filled</dt>
                        <dd class="font-medium mt-1">
                            <div class="flex items-center gap-2">
                                <progress class="progress progress-primary w-full max-w-xs" value="{{ $jobPosition->occupied }}" max="{{ $jobPosition->total_slots }}"></progress>
                                <span>{{ $jobPosition->occupied }}/{{ $jobPosition->total_slots }}</span>
                            </div>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Description --}}
            @if ($jobPosition->content)
            <div class="pt-4 border-t border-base-200">
                <h3 class="card-title flex items-center gap-2">📝 Description / Requirements</h3>
                <div class="mt-3 p-4 rounded-lg bg-base-200/50 whitespace-pre-wrap text-sm leading-relaxed">{{ $jobPosition->content }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Back link --}}
    <div class="mt-6">
        <a href="{{ route('employers.job-positions.index', $employer) }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to all positions
        </a>
    </div>
</div>
@endsection