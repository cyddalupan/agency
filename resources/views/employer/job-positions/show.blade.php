@extends('layouts.employer-app')

@section('title', $jobPosition->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('employer.job-positions.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Job Positions
        </a>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <span>💼</span> {{ $jobPosition->name }}
                    </h2>
                    <p class="opacity-60 text-sm mt-1">for 🏢 {{ $employer->name }}</p>
                </div>
                <div class="flex gap-1">
                    <a href="{{ route('employer.job-positions.edit', $jobPosition) }}" class="btn btn-ghost btn-sm">✏️ Edit</a>
                    <form action="{{ route('employer.job-positions.destroy', $jobPosition) }}" method="POST" class="inline" onsubmit="return confirm('🗑️ Delete?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm text-error">🗑️ Delete</button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div class="space-y-4">
                    <div>
                        <span class="opacity-60 text-sm">Status</span>
                        <p>
                            @if ($jobPosition->status === 'open')
                                <span class="badge badge-success">🟢 Open</span>
                            @elseif ($jobPosition->status === 'filled')
                                <span class="badge badge-info">🔵 Filled</span>
                            @else
                                <span class="badge badge-ghost">⏸️ {{ ucfirst($jobPosition->status) }}</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="opacity-60 text-sm">Slots</span>
                        <p class="font-medium">{{ $jobPosition->occupied }} / {{ $jobPosition->total_slots }}</p>
                    </div>
                    <div>
                        <span class="opacity-60 text-sm">Gender Preference</span>
                        <p class="font-medium">{{ ucfirst($jobPosition->gender_preference ?? 'Any') }}</p>
                    </div>
                </div>
                <div class="space-y-4">
                    @if ($jobPosition->salary)
                    <div>
                        <span class="opacity-60 text-sm">Salary</span>
                        <p class="font-medium font-mono">{{ $jobPosition->salary_currency ?? 'USD' }} {{ number_format($jobPosition->salary, 2) }}</p>
                    </div>
                    @endif
                    @if ($jobPosition->position)
                    <div>
                        <span class="opacity-60 text-sm">Base Position</span>
                        <p class="font-medium">{{ $jobPosition->position->name }}</p>
                    </div>
                    @endif
                </div>
            </div>

            @if ($jobPosition->content)
            <div class="mt-6 pt-6 border-t border-base-200">
                <span class="opacity-60 text-sm">📝 Description</span>
                <div class="mt-2 prose prose-sm max-w-none">
                    {{ nl2br(e($jobPosition->content)) }}
                </div>
            </div>
            @endif

            {{-- Card 6b0798fb: the same shareable Apply Now link the staff-side
                 Job Order page shows. Cyd's decision (2026-11-26) was to put it
                 on both surfaces rather than pick one, so an employer contact
                 can also hand the link to their own marketing.

                 Both pages call the same JobPosition::applyUrl(), so the link is
                 identical on both — there is no second token to keep in sync. --}}
            <div class="mt-6 pt-6 border-t border-base-200">
                <span class="opacity-60 text-sm">🔗 Apply Now Link</span>
                <p class="text-sm opacity-70 mt-1">
                    Share this link to collect applications for this position.
                    The position is fixed on the form.
                </p>
                <div class="flex flex-col sm:flex-row gap-2 mt-3">
                    <input type="text" readonly id="emp-apply-url-{{ $jobPosition->id }}"
                           value="{{ $jobPosition->applyUrl() }}"
                           onclick="this.select()"
                           class="input input-bordered input-sm w-full font-mono text-xs">
                    <button type="button" class="btn btn-sm btn-primary whitespace-nowrap"
                            onclick="(function(b){var f=document.getElementById('emp-apply-url-{{ $jobPosition->id }}');var done=function(){b.innerText='✓ Copied';setTimeout(function(){b.innerText='Copy';},2000);};if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(f.value).then(done).catch(function(){f.select();document.execCommand('copy');done();});}else{f.select();document.execCommand('copy');done();}})(this)">
                        Copy
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
