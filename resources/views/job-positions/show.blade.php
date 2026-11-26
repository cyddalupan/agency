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

            {{-- Card 6b0798fb: shareable "Apply Now" link for marketing.
                 The token is issued on first view (JobPosition::applyToken()).
                 readonly + select-on-click rather than a copy button alone,
                 because a copy button needs JS and clipboard permission and
                 silently fails on non-HTTPS or in some embedded browsers. --}}
            <div class="pt-4 border-t border-base-200">
                <h3 class="card-title flex items-center gap-2">🔗 Apply Now Link</h3>
                <p class="text-sm opacity-70 mt-1">
                    Share this on social media or anywhere else. Anyone who opens it gets an
                    application form with <strong>{{ $jobPosition->name ?: ($jobPosition->position->name ?? 'this position') }}</strong> already filled in.
                </p>
                <div class="flex flex-col sm:flex-row gap-2 mt-3">
                    <input type="text" readonly id="apply-url-{{ $jobPosition->id }}"
                           value="{{ $jobPosition->applyUrl() }}"
                           onclick="this.select()"
                           class="input input-bordered input-sm w-full font-mono text-xs">
                    <div class="flex gap-2">
                        <button type="button" class="btn btn-sm btn-primary whitespace-nowrap"
                                onclick="(function(b){var f=document.getElementById('apply-url-{{ $jobPosition->id }}');var done=function(){b.innerText='✓ Copied';setTimeout(function(){b.innerText='Copy';},2000);};if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(f.value).then(done).catch(function(){f.select();document.execCommand('copy');done();});}else{f.select();document.execCommand('copy');done();}})(this)">
                            Copy
                        </button>
                        <a href="{{ $jobPosition->applyUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-ghost whitespace-nowrap">Open ↗</a>
                    </div>
                </div>
                <p class="text-xs opacity-50 mt-2">
                    Anyone with this link can submit an application. To revoke it, ask for a new token — the old link then stops working.
                </p>
            </div>
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