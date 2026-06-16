@extends('layouts.app')

@section('title', $employer->name)

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="card bg-gradient-to-br from-secondary via-secondary/80 to-accent text-secondary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('employers.index') }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1 mb-2">
                <span>←</span> Back to Employers
            </a>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-5xl">🏢</span>
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold">{{ $employer->name }}</h2>
                        @if($employer->contact_person)
                            <p class="opacity-80 text-sm mt-1">👤 {{ $employer->contact_person }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('employers.job-positions.index', $employer) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
                        💼 Positions
                    </a>
                    <a href="{{ route('employers.edit', $employer) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
                        ✏️ Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm mb-6 card-lift">
        <div class="card-body space-y-6">
            {{-- Company Info --}}
            <div>
                <h3 class="card-title flex items-center gap-2">🏛️ Company Information</h3>
                <dl class="grid grid-cols-2 gap-4 mt-3">
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">Company No.</dt>
                        <dd class="font-medium mt-1">{{ $employer->company_no ?? '---' }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">📊 Status</dt>
                        <dd class="mt-1">
                            @if(($employer->status ?? 'active') === 'active')
                                <span class="badge badge-sm badge-success">✅ Active</span>
                            @else
                                <span class="badge badge-sm badge-ghost">⏸️ {{ ucfirst($employer->status) }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Contact --}}
            <div class="pt-4 border-t border-base-200">
                <h3 class="card-title flex items-center gap-2">📞 Contact Information</h3>
                <dl class="grid grid-cols-2 gap-4 mt-3">
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">👤 Contact Person</dt>
                        <dd class="font-medium mt-1">{{ $employer->contact_person ?? '---' }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">📱 Phone</dt>
                        <dd class="font-medium mt-1">{{ $employer->contact ?? '---' }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">✉️ Email</dt>
                        <dd class="font-medium mt-1">{{ $employer->email ?? '---' }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">🌍 Country</dt>
                        <dd class="font-medium mt-1">{{ $employer->country->name ?? '---' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Address --}}
            @if ($employer->address)
            <div class="pt-4 border-t border-base-200">
                <h3 class="card-title flex items-center gap-2">🏠 Address</h3>
                <p class="mt-2 p-3 rounded-lg bg-base-200/50">{{ $employer->address }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Custom Fields --}}
    <div class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body">
            @include('partials.custom-fields-display', ['model' => $employer, 'modelType' => 'Employer'])
        </div>
    </div>

    {{-- Job Positions --}}
    <div class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <h3 class="card-title flex items-center gap-2">
                    <span>💼</span> Job Positions ({{ $employer->jobPositions->count() }})
                </h3>
                <a href="{{ route('employers.job-positions.create', $employer) }}" class="btn btn-primary btn-sm">
                    ➕ Add Position
                </a>
            </div>

            @if ($employer->jobPositions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-sm table-zebra">
                        <thead>
                            <tr class="bg-base-200/80">
                                <th>Position</th>
                                <th>💰 Salary</th>
                                <th>🎯 Slots</th>
                                <th>📊 Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employer->jobPositions as $job)
                                <tr class="hover transition-colors">
                                    <td class="font-medium">{{ $job->name }}</td>
                                    <td class="text-sm">{{ $job->salary ? '$'.number_format($job->salary, 2) : '—' }}</td>
                                    <td class="text-sm">{{ $job->occupied }}/{{ $job->total_slots }}</td>
                                    <td>
                                        @if ($job->status === 'open')
                                            <span class="badge badge-sm badge-success">🟢 Open</span>
                                        @elseif ($job->status === 'filled')
                                            <span class="badge badge-sm badge-info">🔵 Filled</span>
                                        @else
                                            <span class="badge badge-sm badge-ghost">⏸️ {{ ucfirst($job->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('employers.job-positions.show', [$employer, $job]) }}" class="link link-primary text-sm">View →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 opacity-50">
                    <span class="text-4xl block mb-2">💼</span>
                    <p class="text-sm">No job positions assigned yet.</p>
                    <a href="{{ route('employers.job-positions.create', $employer) }}" class="btn btn-outline btn-sm mt-3">➕ Add First Position</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection