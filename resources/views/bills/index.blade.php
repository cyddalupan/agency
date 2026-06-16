@extends('layouts.app')

@section('title', 'Bills')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>📄</span> Bills
            </h2>
            <p class="opacity-60 text-sm mt-1">Track employer and applicant costs, deposits, and payments</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('bills.create') }}" class="btn btn-primary">
                <span>➕</span> Create Bill
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($bills->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>ID</th>
                            <th>🏢 Employer</th>
                            <th>👤 Applicant</th>
                            <th>💰 Employer Cost</th>
                            <th>💵 Applicant Cost</th>
                            <th>📊 Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bills as $bill)
                        <tr class="hover transition-colors">
                            <td class="font-mono text-sm">#{{ $bill->id }}</td>
                            <td>
                                <a href="{{ route('bills.show', $bill) }}" class="link link-primary font-medium">
                                    {{ $bill->employer->name ?? '—' }}
                                </a>
                            </td>
                            <td class="text-sm">
                                {{ $bill->applicant->full_name ?? '—' }}
                            </td>
                            <td class="text-sm font-mono">
                                ₱{{ number_format($bill->employer_cost, 2) }}
                            </td>
                            <td class="text-sm font-mono">
                                ₱{{ number_format($bill->applicant_cost, 2) }}
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'badge-warning',
                                        'less' => 'badge-error',
                                        'partially_paid' => 'badge-info',
                                        'paid' => 'badge-success',
                                        'over_paid' => 'badge-secondary',
                                    ];
                                    $statusIcons = [
                                        'pending' => '⏳',
                                        'less' => '⚠️',
                                        'partially_paid' => '🔄',
                                        'paid' => '✅',
                                        'over_paid' => '🔄',
                                    ];
                                @endphp
                                <span class="badge badge-sm {{ $statusColors[$bill->status] ?? 'badge-ghost' }}">
                                    {{ $statusIcons[$bill->status] ?? '' }} {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('bills.show', $bill) }}" class="btn btn-ghost btn-xs btn-square" title="View">👁️</a>
                                    <a href="{{ route('bills.edit', $bill) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $bills->links() }}
        </div>
    @else
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">📄</span>
                <h3 class="text-xl font-bold mb-2">No Bills Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">
                    Create a bill to start tracking employer costs, applicant costs, deposits, and payment statuses.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('bills.create') }}" class="btn btn-primary btn-lg">
                        <span>➕</span> Create Your First Bill
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">🏢</span>
                    <h4 class="font-semibold text-sm">Employer Costs</h4>
                    <p class="text-xs opacity-60 mt-1">Track fees charged to employers</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">💵</span>
                    <h4 class="font-semibold text-sm">Applicant Costs</h4>
                    <p class="text-xs opacity-60 mt-1">Track fees paid by applicants</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">📊</span>
                    <h4 class="font-semibold text-sm">Payment Status</h4>
                    <p class="text-xs opacity-60 mt-1">Pending → Partially Paid → Paid</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
