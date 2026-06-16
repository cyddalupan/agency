@extends('layouts.app')

@section('title', 'Transaction History')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Transaction History</h1>

    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="card bg-base-100 shadow-md">
            <div class="card-body text-center">
                <p class="text-sm opacity-60">Total Bills</p>
                <p class="text-2xl font-bold">₱{{ number_format($totalBilled, 2) }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body text-center">
                <p class="text-sm opacity-60">Total Payments Received</p>
                <p class="text-2xl font-bold text-success">₱{{ number_format($totalPaid, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Bills --}}
    <div class="card bg-base-100 shadow-md mb-6">
        <div class="card-body">
            <h3 class="card-title mb-2">Recent Bills</h3>
            <p class="text-sm opacity-60 mb-4">{{ $bills->count() }} total</p>
            @if($bills->isEmpty())
                <p class="text-center py-4 opacity-50">No bills recorded.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Entity</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bills as $bill)
                                <tr>
                                    <td class="text-sm">{{ $bill->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($bill->employer_id) Employer @else Worker @endif
                                    </td>
                                    <td>{{ $bill->employer->name ?? $bill->applicant?->first_name . ' ' . $bill->applicant?->last_name ?? '—' }}</td>
                                    <td class="text-right">₱{{ number_format($bill->employer_cost ?? $bill->applicant_cost, 2) }}</td>
                                    <td><span class="badge badge-sm badge-{{ $bill->status === 'paid' ? 'success' : 'ghost' }}">{{ ucfirst($bill->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Payments --}}
    <div class="card bg-base-100 shadow-md">
        <div class="card-body">
            <h3 class="card-title mb-2">Recent Payments</h3>
            <p class="text-sm opacity-60 mb-4">{{ $payments->count() }} total</p>
            @if($payments->isEmpty())
                <p class="text-center py-4 opacity-50">No payments recorded.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Bill #</th>
                                <th>Entity</th>
                                <th>Amount</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td class="text-sm">{{ $payment->created_at->format('M d, Y') }}</td>
                                    <td class="font-mono text-sm">{{ $payment->reference_no ?? '—' }}</td>
                                    <td>#{{ $payment->bill_id }}</td>
                                    <td>{{ $payment->bill->employer->name ?? $payment->bill->applicant?->first_name . ' ' . $payment->bill->applicant?->last_name ?? '—' }}</td>
                                    <td class="text-right text-success">+₱{{ number_format($payment->amount, 2) }}</td>
                                    <td><span class="badge badge-sm badge-outline">{{ $payment->category ?? '—' }}</span></td>
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
