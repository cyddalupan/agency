@extends('layouts.app')

@section('title', $marketingAgency->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card bg-gradient-to-br from-violet-600 via-violet-500/90 to-purple-600 text-white shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('marketing-agencies.index') }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1 mb-2">
                <span>←</span> Back to Marketing Agencies
            </a>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-5xl">📢</span>
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold">{{ $marketingAgency->name }}</h2>
                        @if($marketingAgency->contact_person)
                            <p class="opacity-80 text-sm mt-1">👤 {{ $marketingAgency->contact_person }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('marketing-agencies.marketing-agents.index', $marketingAgency) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
                        👥 Agents
                    </a>
                    <a href="{{ route('marketing-agencies.edit', $marketingAgency) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
                        ✏️ Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm mb-6 card-lift">
        <div class="card-body space-y-6">
            <div>
                <h3 class="card-title flex items-center gap-2">🏛️ Agency Information</h3>
                <dl class="grid grid-cols-2 gap-4 mt-3">
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">📊 Status</dt>
                        <dd class="mt-1">
                            @if($marketingAgency->status === 'active')
                                <span class="badge badge-sm badge-success">✅ Active</span>
                            @else
                                <span class="badge badge-sm badge-ghost">⏸️ {{ ucfirst($marketingAgency->status) }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">💰 Commission Rate</dt>
                        <dd class="font-medium mt-1 text-lg">{{ $marketingAgency->commission_rate }}%</dd>
                    </div>
                </dl>
            </div>

            <div class="pt-4 border-t border-base-200">
                <h3 class="card-title flex items-center gap-2">📞 Contact Information</h3>
                <dl class="grid grid-cols-2 gap-4 mt-3">
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">👤 Contact Person</dt>
                        <dd class="font-medium mt-1">{{ $marketingAgency->contact_person ?? '---' }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">📱 Phone</dt>
                        <dd class="font-medium mt-1">{{ $marketingAgency->contact ?? '---' }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">✉️ Email</dt>
                        <dd class="font-medium mt-1">{{ $marketingAgency->email ?? '---' }}</dd>
                    </div>
                </dl>
            </div>

            @if ($marketingAgency->address)
            <div class="pt-4 border-t border-base-200">
                <h3 class="card-title flex items-center gap-2">🏠 Address</h3>
                <p class="mt-2 p-3 rounded-lg bg-base-200/50">{{ $marketingAgency->address }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <h3 class="card-title flex items-center gap-2">
                    <span>👥</span> Marketing Agents ({{ $marketingAgency->marketingAgents->count() }})
                </h3>
                <a href="{{ route('marketing-agencies.marketing-agents.create', $marketingAgency) }}" class="btn btn-primary btn-sm">
                    ➕ Add Agent
                </a>
            </div>

            @if ($marketingAgency->marketingAgents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-sm table-zebra">
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
                            @foreach ($marketingAgency->marketingAgents as $agent)
                                <tr class="hover transition-colors">
                                    <td class="font-medium">{{ $agent->name }}</td>
                                    <td class="text-sm">{{ $agent->contact ?? '---' }}</td>
                                    <td class="text-sm">{{ $agent->email ?? '---' }}</td>
                                    <td>
                                        @if ($agent->status === 'active')
                                            <span class="badge badge-sm badge-success">✅ Active</span>
                                        @else
                                            <span class="badge badge-sm badge-ghost">⏸️ {{ ucfirst($agent->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('marketing-agencies.marketing-agents.edit', [$marketingAgency, $agent]) }}" class="link link-primary text-sm">✏️ Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 opacity-50">
                    <span class="text-4xl block mb-2">👤</span>
                    <p class="text-sm">No marketing agents assigned yet.</p>
                    <a href="{{ route('marketing-agencies.marketing-agents.create', $marketingAgency) }}" class="btn btn-outline btn-sm mt-3">➕ Add First Agent</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
