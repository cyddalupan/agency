@extends('layouts.employer-app')

@section('title', 'Applicant Billing')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold">{{ $applicant->first_name }} {{ $applicant->last_name }}</h1>
        <p class="text-base-content/60">Statement of Account</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="stat bg-base-100 rounded-xl shadow-sm border border-base-200">
            <div class="stat-figure text-primary text-3xl">📋</div>
            <div class="stat-title">Total Cost</div>
            <div class="stat-value text-primary">{{ number_format($totalCost, 2) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-xl shadow-sm border border-base-200">
            <div class="stat-figure text-success text-3xl">✅</div>
            <div class="stat-title">Paid</div>
            <div class="stat-value text-success">{{ number_format($totalPaid, 2) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-xl shadow-sm border border-base-200">
            <div class="stat-figure text-warning text-3xl">💰</div>
            <div class="stat-title">Balance</div>
            <div class="stat-value text-warning">{{ number_format($balance, 2) }}</div>
        </div>
    </div>

    {{-- Bills Table --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-lg">Bills</h2>

            @if ($bills->isEmpty())
                <div class="alert bg-base-200 mt-3">
                    <span>No bills</span>
                </div>
            @else
                <div class="overflow-x-auto mt-3">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th class="text-right">Paid</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bills as $bill)
                            @php
                                $paidAmount = $bill->payments->where('status', 'received')->sum('amount');
                            @endphp
                            <tr>
                                <td>{{ $bill->id }}</td>
                                <td class="font-medium">{{ number_format($bill->applicant_cost, 2) }}</td>
                                <td class="text-right">{{ number_format($paidAmount, 2) }}</td>
                                <td>
                                    @if ($bill->status === 'paid')
                                        <span class="badge badge-success badge-sm">Paid</span>
                                    @elseif ($bill->status === 'sent')
                                        <span class="badge badge-info badge-sm">Sent</span>
                                    @elseif ($bill->status === 'pending')
                                        <span class="badge badge-warning badge-sm">Pending</span>
                                    @else
                                        <span class="badge badge-ghost badge-sm">{{ ucfirst($bill->status) }}</span>
                                    @endif
                                </td>
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
