@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>💳</span> Payments
            </h2>
            <p class="opacity-60 text-sm mt-1">Track all payments against bills — cash, bank transfer, GCash, and more</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                <span>➕</span> Record Payment
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($payments->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>ID</th>
                            <th>📄 Bill</th>
                            <th>💰 Amount</th>
                            <th>🏷️ Category</th>
                            <th>💳 Type</th>
                            <th>📊 Status</th>
                            <th>📅 Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr class="hover transition-colors">
                            <td class="font-mono text-sm">#{{ $payment->id }}</td>
                            <td>
                                @if($payment->bill)
                                    <a href="{{ route('bills.show', $payment->bill) }}" class="link link-primary text-sm">
                                        Bill #{{ $payment->bill->id }}
                                        @if($payment->bill->employer)
                                            · {{ $payment->bill->employer->name }}
                                        @endif
                                    </a>
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td class="font-mono text-sm font-semibold">₱{{ number_format($payment->amount, 2) }}</td>
                            <td>
                                <span class="badge badge-sm badge-outline">
                                    {{ ucfirst(str_replace('_', ' ', $payment->category)) }}
                                </span>
                            </td>
                            <td class="text-sm">{{ ucfirst(str_replace('_', ' ', $payment->type)) }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'badge-warning',
                                        'confirmed' => 'badge-success',
                                        'failed' => 'badge-error',
                                        'refunded' => 'badge-info',
                                    ];
                                @endphp
                                <span class="badge badge-sm {{ $statusColors[$payment->status] ?? 'badge-ghost' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="text-sm">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') : '—' }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('payments.show', $payment) }}" class="btn btn-ghost btn-xs btn-square" title="View">👁️</a>
                                    <a href="{{ route('payments.edit', $payment) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    @else
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">💳</span>
                <h3 class="text-xl font-bold mb-2">No Payments Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">
                    Record payments against bills to track cash, bank transfers, checks, and GCash transactions.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('payments.create') }}" class="btn btn-primary btn-lg">
                        <span>➕</span> Record Your First Payment
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">💰</span>
                    <h4 class="font-semibold text-sm">Multiple Categories</h4>
                    <p class="text-xs opacity-60 mt-1">Employer cost, applicant cost, deposit, commission</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">💳</span>
                    <h4 class="font-semibold text-sm">Various Types</h4>
                    <p class="text-xs opacity-60 mt-1">Cash, bank transfer, check, GCash, online</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">✅</span>
                    <h4 class="font-semibold text-sm">Status Tracking</h4>
                    <p class="text-xs opacity-60 mt-1">Pending → Confirmed → Failed or Refunded</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
