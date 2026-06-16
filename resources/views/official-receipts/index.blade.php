@extends('layouts.app')

@section('title', 'Official Receipts')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>🧾</span> Official Receipts
            </h2>
            <p class="opacity-60 text-sm mt-1">Issue and manage official receipts for payments</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('official-receipts.create') }}" class="btn btn-primary">
                <span>➕</span> Issue Receipt
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($officialReceipts->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>ID</th>
                            <th>🧾 OR No.</th>
                            <th>💰 Amount</th>
                            <th>👤 Issued To</th>
                            <th>📅 Issue Date</th>
                            <th>💳 Payment</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($officialReceipts as $or)
                        <tr class="hover transition-colors">
                            <td class="font-mono text-sm">#{{ $or->id }}</td>
                            <td class="font-mono font-semibold">{{ $or->or_no }}</td>
                            <td class="font-mono text-sm font-semibold">₱{{ number_format($or->amount, 2) }}</td>
                            <td class="text-sm">
                                {{ $or->issued_to_name }}
                                <span class="badge badge-xs badge-ghost ml-1">{{ ucfirst($or->issued_to) }}</span>
                            </td>
                            <td class="text-sm">{{ \Carbon\Carbon::parse($or->issue_date)->format('M d, Y') }}</td>
                            <td>
                                @if($or->payment)
                                    <a href="{{ route('payments.show', $or->payment) }}" class="link link-primary text-sm">
                                        Payment #{{ $or->payment->id }}
                                    </a>
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('official-receipts.show', $or) }}" class="btn btn-ghost btn-xs btn-square" title="View">👁️</a>
                                    <a href="{{ route('official-receipts.edit', $or) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $officialReceipts->links() }}
        </div>
    @else
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">🧾</span>
                <h3 class="text-xl font-bold mb-2">No Official Receipts Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">
                    Issue official receipts against confirmed payments for proper documentation.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('official-receipts.create') }}" class="btn btn-primary btn-lg">
                        <span>➕</span> Issue Your First Receipt
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
