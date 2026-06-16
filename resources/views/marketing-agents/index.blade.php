@extends('layouts.app')

@section('title', 'Agents - ' . $marketingAgency->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('marketing-agencies.show', $marketingAgency) }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to {{ $marketingAgency->name }}
        </a>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>👥</span> Marketing Agents
            </h2>
            <p class="opacity-60 text-sm mt-1">
                Under <strong>{{ $marketingAgency->name }}</strong>
                @if($marketingAgency->commission_rate > 0)
                    · 💰 Commission rate: {{ $marketingAgency->commission_rate }}%
                @endif
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('marketing-agencies.marketing-agents.create', $marketingAgency) }}" class="btn btn-primary">
                <span>➕</span> Add Agent
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($agents->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>Name</th>
                            <th>📱 Contact</th>
                            <th>✉️ Email</th>
                            <th>📊 Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agents as $agent)
                        <tr class="hover transition-colors">
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">👤</span>
                                    <span class="font-medium">{{ $agent->name }}</span>
                                </div>
                            </td>
                            <td class="text-sm">{{ $agent->contact ?? '---' }}</td>
                            <td class="text-sm">{{ $agent->email ?? '---' }}</td>
                            <td>
                                @if($agent->status === 'active')
                                    <span class="badge badge-sm badge-success">✅ Active</span>
                                @else
                                    <span class="badge badge-sm badge-ghost">⏸️ {{ ucfirst($agent->status) }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('marketing-agencies.marketing-agents.edit', [$marketingAgency, $agent]) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $agents->links() }}
        </div>
    @else
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">👤</span>
                <h3 class="text-xl font-bold mb-2">No Agents Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">
                    Individual marketing agents work under {{ $marketingAgency->name }}. Assign agents to track their referrals.
                </p>
                <a href="{{ route('marketing-agencies.marketing-agents.create', $marketingAgency) }}" class="btn btn-primary btn-lg">
                    <span>➕</span> Add First Agent
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
