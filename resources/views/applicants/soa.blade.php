@extends('layouts.app')

@section('title', 'Statement of Account - ' . $applicant->first_name . ' ' . $applicant->last_name)

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="card bg-gradient-to-br from-secondary via-secondary/80 to-accent text-secondary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('applicants.show', $applicant) }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1 mb-2">
                <span>←</span> Back to {{ $applicant->first_name }} {{ $applicant->last_name }}
            </a>
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Statement of Account</h1>
                    <p class="opacity-80 mt-1">{{ $applicant->first_name }} {{ $applicant->last_name }}</p>
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-md">
            <div class="card-body text-center">
                <p class="text-sm opacity-60">Total Cost</p>
                <p class="text-2xl font-bold">₱{{ number_format($totalCost, 2) }}</p>
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
                <p class="text-sm opacity-60">Balance</p>
                <p class="text-2xl font-bold @if($balance > 0) text-warning @elseif($balance < 0) text-info @else text-success @endif">
                    ₱{{ number_format($balance, 2) }}
                </p>
            </div>
        </div>
    </div>

    {{-- Bills Detail --}}
    <div class="card bg-base-100 shadow-md mb-6">
        <div class="card-body">
            <h3 class="card-title mb-4">Bill History</h3>

            @if($bills->isEmpty())
                <p class="text-center py-6 opacity-50">No bills found for this applicant.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Ref #</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Deposit</th>
                                <th>Payments</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bills as $bill)
                                @php $billPayments = $bill->payments->sum('amount'); @endphp
                                <tr>
                                    <td class="text-sm">{{ $bill->created_at->format('M d, Y') }}</td>
                                    <td class="text-sm font-mono">#{{ $bill->id }}</td>
                                    <td>{{ $bill->notes ?? '—' }}</td>
                                    <td class="text-right">₱{{ number_format($bill->applicant_cost, 2) }}</td>
                                    <td class="text-right">
                                        @if($bill->applicant_deposit > 0)
                                            ₱{{ number_format($bill->applicant_deposit, 2) }}
                                        @else
                                            <span class="opacity-40">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($billPayments > 0)
                                            ₱{{ number_format($billPayments, 2) }}
                                        @else
                                            <span class="opacity-40">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $bill->status === 'paid' ? 'success' : ($bill->status === 'sent' ? 'warning' : 'ghost') }} badge-sm">
                                            {{ ucfirst($bill->status) }}
                                        </span>
                                    </td>
                                </tr>
                                {{-- Show payments under this bill --}}
                                @foreach($bill->payments as $payment)
                                    <tr class="bg-base-200/50">
                                        <td class="text-xs pl-8" colspan="2">
                                            Payment — {{ $payment->created_at->format('M d, Y') }}
                                            @if($payment->reference_no)
                                                <span class="opacity-50">({{ $payment->reference_no }})</span>
                                            @endif
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-right text-success text-sm">+₱{{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $payment->status === 'received' ? 'success' : 'ghost' }} badge-sm">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-bold">
                                <td colspan="3" class="text-right">Totals</td>
                                <td class="text-right">₱{{ number_format($totalCost, 2) }}</td>
                                <td class="text-right">—</td>
                                <td class="text-right text-success">₱{{ number_format($totalPaid, 2) }}</td>
                                <td class="text-right @if($balance > 0) text-warning @elseif($balance < 0) text-info @else text-success @endif">
                                    ₱{{ number_format($balance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
