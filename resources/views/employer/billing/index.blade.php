@extends('layouts.employer-app')

@section('title', 'Billing')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Billing</h1>
        <a href="{{ route('employer.billing.soa') }}" class="btn btn-primary btn-sm">
            Statement of Account
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="stat bg-base-100 rounded-xl shadow-sm border border-base-200">
            <div class="stat-figure text-primary text-3xl">📋</div>
            <div class="stat-title">Total Billed</div>
            <div class="stat-value text-primary">{{ number_format($totalBilled, 2) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-xl shadow-sm border border-base-200">
            <div class="stat-figure text-success text-3xl">✅</div>
            <div class="stat-title">Total Paid</div>
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
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bills as $bill)
                            <tr>
                                <td>{{ $bill->id }}</td>
                                <td class="font-medium">{{ number_format($bill->employer_cost, 2) }}</td>
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
                                <td>{{ $bill->created_at->format('M d, Y') }}</td>
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
