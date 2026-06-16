@extends('portal.layouts.app')

@section('title', $job->name)

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Back link --}}
    <a href="{{ route('portal.jobs.index') }}" class="btn btn-ghost btn-sm mb-4">
        ← Back to Job Listings
    </a>

    {{-- Job card --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ $job->name }}</h1>
                    <p class="text-base opacity-70 mt-1">{{ $job->employer?->name ?? 'Unknown Employer' }}</p>
                </div>
                @if($job->status === 'open')
                <span class="badge badge-success gap-1">✅ Open</span>
                @else
                <span class="badge badge-ghost gap-1">{{ ucfirst($job->status) }}</span>
                @endif
            </div>

            {{-- Details grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @if($job->salary)
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Salary</dt>
                    <dd class="font-medium mt-1">{{ number_format($job->salary, 2) }} {{ $job->salary_currency ?? 'PHP' }}</dd>
                </div>
                @endif
                @if($job->total_slots > 0)
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Available Slots</dt>
                    <dd class="font-medium mt-1">{{ $job->total_slots - $job->occupied }} / {{ $job->total_slots }}</dd>
                </div>
                @endif
                @if($job->gender_preference && $job->gender_preference !== 'any')
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Gender Preference</dt>
                    <dd class="font-medium mt-1">{{ ucfirst($job->gender_preference) }}</dd>
                </div>
                @endif
                @if($job->position)
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Category</dt>
                    <dd class="font-medium mt-1">{{ $job->position->name }}</dd>
                </div>
                @endif
                @if($job->employer)
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Employer</dt>
                    <dd class="font-medium mt-1">{{ $job->employer->name }}</dd>
                </div>
                @if($job->employer->address)
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Location</dt>
                    <dd class="font-medium mt-1">{{ $job->employer->address }}</dd>
                </div>
                @endif
                @endif
            </div>

            {{-- Job description --}}
            @if($job->content)
            <div class="mb-6">
                <h3 class="font-semibold text-sm opacity-60 uppercase tracking-wider mb-2">Job Description</h3>
                <div class="prose prose-sm max-w-none">
                    {{ nl2br(e($job->content)) }}
                </div>
            </div>
            @endif

            {{-- Action --}}
            <div class="border-t pt-4 flex justify-end gap-2">
                <a href="{{ route('portal.jobs.index') }}" class="btn btn-outline">← Other Jobs</a>
            </div>
        </div>
    </div>
</div>
@endsection
