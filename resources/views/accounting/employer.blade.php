@extends('layouts.app')

@section('title', 'Accounting Overview — ' . $employer->name)

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('employers.show', $employer) }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1 mb-2">
                <span>&larr;</span> Back to {{ $employer->name }}
            </a>
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Accounting Overview</h1>
                    <p class="opacity-80 mt-1">{{ $employer->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold">
                        @if($balance > 0)
                            <span class="text-warning">₱{{ number_format($balance, 2) }}</span>
                        @elseif($balance < 0)
                            <span class="text-info">₱{{ number_format(abs($balance), 2) }} (Credit)</span>
                        @else
                            <span class="text-success">₱0.00</span>
                        @endif
                    </p>
                    <p class="text-sm opacity-70">{{ $balance > 0 ? 'Outstanding Balance' : ($balance < 0 ? 'Credit Balance' : 'Fully Paid') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card bg-base-100 shadow-md">
            <div class="card-body text-center">
                <p class="text-sm opacity-60">Total Billed</p>
                <p class="text-2xl font-bold">₱{{ number_format($totalBilled, 2) }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body text-center">
                <p class="text-sm opacity-60">Total Paid</p>
                <p class="text-2xl font-bold text-success">₱{{ number_format($totalPaid, 2) }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body text-center">
                <p class="text-sm opacity-60">Commissions Paid</p>
                <p class="text-2xl font-bold text-info">₱{{ number_format($totalCommissions, 2) }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body text-center">
                <p class="text-sm opacity-60">Net Balance</p>
                <p class="text-2xl font-bold @if($balance > 0) text-warning @elseif($balance < 0) text-info @else text-success @endif">
                    ₱{{ number_format($balance, 2) }}
                </p>
            </div>
        </div>
    </div>

    {{-- Bills --}}
    <div class="card bg-base-100 shadow-md mb-6">
        <div class="card-body">
            <h3 class="card-title mb-4">Bills</h3>
            @if($bills->isEmpty())
                <p class="text-center py-4 opacity-50">No bills.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Payments</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $runningBal = 0; @endphp
                            @foreach($bills as $bill)
                                @php
                                    $billPayments = $bill->payments->sum('amount');
                                    $runningBal += $bill->employer_cost - $billPayments;
                                @endphp
                                <tr>
                                    <td class="text-sm">{{ $bill->created_at->format('M d, Y') }}</td>
                                    <td class="text-right">₱{{ number_format($bill->employer_cost, 2) }}</td>
                                    <td class="text-right text-success">₱{{ number_format($billPayments, 2) }}</td>
                                    <td class="text-right font-medium">₱{{ number_format($runningBal, 2) }}</td>
                                    <td><span class="badge badge-sm badge-{{ $bill->status === 'paid' ? 'success' : 'ghost' }}">{{ ucfirst($bill->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Commissions --}}
    <div class="card bg-base-100 shadow-md">
        <div class="card-body">
            <h3 class="card-title mb-4">Commissions</h3>
            @if($commissions->isEmpty())
                <p class="text-center py-4 opacity-50">No commissions.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Agent</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commissions as $commission)
                                <tr>
                                    <td class="text-sm">{{ $commission->created_at->format('M d, Y') }}</td>
                                    <td>{{ $commission->commissionable_type }} #{{ $commission->commissionable_id }}</td>
                                    <td class="text-right">₱{{ number_format($commission->amount, 2) }}</td>
                                    <td><span class="badge badge-sm badge-{{ $commission->status === 'paid' ? 'success' : 'ghost' }}">{{ ucfirst($commission->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
