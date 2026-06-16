@extends('portal.layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6">
        <div class="card-body p-6">
            <div class="flex items-center gap-4">
                <div class="avatar placeholder">
                    <div class="w-16 rounded-full bg-white/20 text-white text-2xl font-bold">
                        {{ strtoupper(substr($applicant->first_name, 0, 1)) }}{{ strtoupper(substr($applicant->last_name, 0, 1)) }}
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold">{{ $applicant->full_name }}</h1>
                    @if($applicant->email)
                        <p class="opacity-80 text-sm mt-1">✉️ {{ $applicant->email }}</p>
                    @endif
                    @if($applicant->contact)
                        <p class="opacity-80 text-sm">📱 {{ $applicant->contact }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Status --}}
    @if($applicant->statusCode)
    <div class="mb-6">
        <span class="badge badge-lg"
            style="background-color: {{ $applicant->statusCode->color ?? '#e5e7eb' }}20; color: {{ $applicant->statusCode->color ?? '#374151' }}">
            📊 {{ $applicant->statusCode->label }}
        </span>
    </div>
    @endif

    {{-- Personal Information --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title flex items-center gap-2 mb-4">
                <span>📋</span> Personal Information
            </h3>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Full Name</dt>
                    <dd class="font-medium mt-1">{{ $applicant->full_name }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">⚤ Gender</dt>
                    <dd class="font-medium mt-1">{{ $applicant->gender ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">🎂 Birthdate</dt>
                    <dd class="font-medium mt-1">{{ $applicant->birthdate?->format('M d, Y') ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Age</dt>
                    <dd class="font-medium mt-1">{{ $applicant->birthdate ? $applicant->birthdate->age . ' years old' : '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">🌍 Preferred Country</dt>
                    <dd class="font-medium mt-1">{{ $applicant->country?->name ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">💼 Preferred Position</dt>
                    <dd class="font-medium mt-1">{{ $applicant->position?->name ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">📱 Source</dt>
                    <dd class="font-medium mt-1">{{ $applicant->source ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50 md:col-span-2">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">🏠 Address</dt>
                    <dd class="font-medium mt-1">{{ $applicant->address ?? '—' }}</dd>
                </div>
            </dl>
            @if($applicant->remarks)
            <div class="mt-4 pt-4 border-t border-base-200">
                <dt class="text-sm opacity-60">📝 Remarks</dt>
                <dd class="mt-1">{{ $applicant->remarks }}</dd>
            </div>
            @endif
        </div>
    </div>

    {{-- Employment Details --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title flex items-center gap-2 mb-4">
                <span>🏢</span> Employment Details
            </h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Employer</dt>
                    <dd class="font-medium mt-1">{{ $applicant->employer?->name ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Job Title</dt>
                    <dd class="font-medium mt-1">{{ $applicant->job?->title ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Expected Salary</dt>
                    <dd class="font-medium mt-1">{{ $applicant->expected_salary ? '₱' . number_format($applicant->expected_salary, 2) : '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Type</dt>
                    <dd class="font-medium mt-1">{{ $applicant->source ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="text-center mt-8">
        <a href="{{ route('portal.dashboard') }}" class="btn btn-ghost">
            ← Back to Dashboard
        </a>
    </div>
</div>
@endsection
