@extends('layouts.employer-app')

@section('title', 'Statement of Account')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Statement of Account</h1>
            <p class="text-base-content/60">{{ $employer->name }}</p>
        </div>
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            🖨️ Print
        </button>
    </div>

    {{-- Outstanding Balance --}}
    <div class="stat bg-base-100 rounded-xl shadow-sm border border-base-200">
        <div class="stat-figure text-warning text-3xl">💰</div>
        <div class="stat-title">Outstanding Balance</div>
        <div class="stat-value text-warning">{{ number_format($outstandingBalance, 2) }}</div>
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
                                <th>Description</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right">Paid</th>
                                <th class="text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bills as $bill)
                            @php
                                $paidAmount = $bill->payments->where('status', 'received')->sum('amount');
                                $billBalance = $bill->employer_cost - $paidAmount;
                            @endphp
                            <tr>
                                <td>{{ $bill->id }}</td>
                                <td>{{ $bill->notes ?? 'Bill #' . $bill->id }}</td>
                                <td class="text-right">{{ number_format($bill->employer_cost, 2) }}</td>
                                <td class="text-right">{{ number_format($paidAmount, 2) }}</td>
                                <td class="text-right font-medium">{{ number_format($billBalance, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-bold">
                                <td colspan="2">Total</td>
                                <td class="text-right">{{ number_format($bills->sum('employer_cost'), 2) }}</td>
                                <td class="text-right">{{ number_format($bills->sum(fn($b) => $b->payments->where('status', 'received')->sum('amount')), 2) }}</td>
                                <td class="text-right">{{ number_format($outstandingBalance, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
