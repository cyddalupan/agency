@extends('layouts.app')

@section('title', 'Commissions')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>🤝</span> Commissions
            </h2>
            <p class="opacity-60 text-sm mt-1">Track commissions for marketing agencies, agents, and recruitment agents</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('commissions.create') }}" class="btn btn-primary">
                <span>➕</span> Record Commission
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($commissions->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>ID</th>
                            <th>🏢 Employer</th>
                            <th>🏷️ Type</th>
                            <th>💰 Amount</th>
                            <th>💵 Paid</th>
                            <th>⚖️ Balance</th>
                            <th>📊 Status</th>
                            <th>📅 Due</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commissions as $commission)
                        <tr class="hover transition-colors">
                            <td class="font-mono text-sm">#{{ $commission->id }}</td>
                            <td class="text-sm">
                                @if($commission->employer)
                                    <a href="{{ route('employers.show', $commission->employer) }}" class="link link-primary">
                                        {{ $commission->employer->name }}
                                    </a>
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td>
                                @if($commission->commissionable_type)
                                    <span class="badge badge-sm badge-outline">
                                        {{ ucfirst(str_replace('_', ' ', $commission->commissionable_type)) }}
                                    </span>
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td class="font-mono text-sm font-semibold">₱{{ number_format($commission->amount, 2) }}</td>
                            <td class="font-mono text-sm">₱{{ number_format($commission->paid_amount, 2) }}</td>
                            <td class="font-mono text-sm {{ $commission->balance > 0 ? 'text-warning' : 'text-success' }}">
                                ₱{{ number_format($commission->balance, 2) }}
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'badge-warning',
                                        'partial' => 'badge-info',
                                        'paid' => 'badge-success',
                                    ];
                                @endphp
                                <span class="badge badge-sm {{ $statusColors[$commission->status] ?? 'badge-ghost' }}">
                                    {{ ucfirst($commission->status) }}
                                </span>
                            </td>
                            <td class="text-sm">{{ $commission->due_date ? \Carbon\Carbon::parse($commission->due_date)->format('M d, Y') : '—' }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('commissions.show', $commission) }}" class="btn btn-ghost btn-xs btn-square" title="View">👁️</a>
                                    <a href="{{ route('commissions.edit', $commission) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $commissions->links() }}
        </div>
    @else
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">🤝</span>
                <h3 class="text-xl font-bold mb-2">No Commissions Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">
                    Record commissions and track payments for marketing agencies, agents, and recruitment agents.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('commissions.create') }}" class="btn btn-primary btn-lg">
                        <span>➕</span> Record Your First Commission
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
