@extends('layouts.app')

@section('title', 'Agents')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 p-4 flex-1">
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>🎯</span> Agents
            </h2>
            <p class="opacity-60 text-sm mt-1">Manage referral agents and commission tracking.</p>
        </div>
        <a href="{{ route('agents.create') }}" class="btn btn-primary ml-4">
            <span>➕</span> New Agent
        </a>
    </div>

    @if (session('success'))
    <div role="alert" class="alert alert-success text-sm shadow-sm mb-4">
        <span>✅</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-0">
            @if ($agents->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Commission Rate</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($agents as $agent)
                        <tr>
                            <td class="font-medium">{{ $agent->name }}</td>
                            <td>{{ $agent->email }}</td>
                            <td>{{ $agent->contact ?? 'N/A' }}</td>
                            <td>{{ $agent->commission_rate ? $agent->commission_rate . '%' : 'N/A' }}</td>
                            <td>
                                @if ($agent->status === 'active')
                                    <span class="badge badge-success badge-sm">Active</span>
                                @else
                                    <span class="badge badge-error badge-sm">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('agents.edit', $agent) }}" class="btn btn-ghost btn-xs">✏️ Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $agents->links() }}
            </div>
            @else
            <div class="text-center py-10 opacity-50">
                <span class="text-4xl block mb-3">📭</span>
                <p class="text-lg">No agents yet.</p>
                <p class="text-sm mt-1">Create an agent to start tracking referrals.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
