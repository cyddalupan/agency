@extends('layouts.app')

@section('title', 'Job Positions')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>💼</span> Job Positions
            </h2>
            <p class="opacity-60 text-sm mt-1">for <a href="{{ route('employers.show', $employer) }}" class="link link-primary font-semibold">🏢 {{ $employer->name }}</a></p>
        </div>
        <a href="{{ route('employers.job-positions.create', $employer) }}" class="btn btn-primary">
            <span>➕</span> Add Job Position
        </a>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="card bg-base-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra">
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
                    @forelse ($jobPositions as $job)
                    <tr class="hover transition-colors">
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="text-xl">💼</span>
                                <a href="{{ route('employers.job-positions.show', [$employer, $job]) }}" class="link link-primary font-medium">{{ $job->name }}</a>
                            </div>
                        </td>
                        <td class="text-sm">
                            @if ($job->salary)
                                <span class="font-mono font-medium">${{ number_format($job->salary, 2) }}</span>
                            @else
                                <span class="opacity-40">—</span>
                            @endif
                        </td>
                        <td class="text-sm">
                            <div class="flex items-center gap-1">
                                <span>{{ $job->occupied }}</span>
                                <span class="opacity-40">/</span>
                                <span class="font-medium">{{ $job->total_slots }}</span>
                                @if($job->occupied >= $job->total_slots)
                                    <span class="text-xs ml-1">✅</span>
                                @endif
                            </div>
                        </td>
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
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('employers.job-positions.show', [$employer, $job]) }}" class="btn btn-ghost btn-xs btn-square" title="View">👁️</a>
                                <a href="{{ route('employers.job-positions.edit', [$employer, $job]) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                <form action="{{ route('employers.job-positions.destroy', [$employer, $job]) }}" method="POST" class="inline" onsubmit="return confirm('🗑️ Delete this position?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-12 opacity-60">
                            <span class="text-4xl block mb-2">💼</span>
                            No job positions yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <div>
            {{ $jobPositions->links() }}
        </div>
        <div>
            <a href="{{ route('employers.show', $employer) }}" class="link link-secondary text-sm flex items-center gap-1">
                <span>←</span> Back to Employer
            </a>
        </div>
    </div>
</div>
@endsection