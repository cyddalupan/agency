@extends('layouts.app')

@section('title', 'Payment #' . $payment->id)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card bg-gradient-to-br from-green-600 via-green-500/90 to-emerald-600 text-white shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('payments.index') }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1 mb-2">
                <span>←</span> Back to Payments
            </a>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-5xl">💳</span>
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold">Payment #{{ $payment->id }}</h2>
                        <p class="opacity-80 text-sm mt-1">
                            ₱{{ number_format($payment->amount, 2) }}
                            @if($payment->bill)
                                · Bill #{{ $payment->bill->id }}
                                @if($payment->bill->employer)
                                    · {{ $payment->bill->employer->name }}
                                @endif
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('payments.edit', $payment) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
                        ✏️ Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body">
                <h3 class="card-title text-lg flex items-center gap-2">
                    <span>💰</span> Payment Details
                </h3>
                <div class="overflow-x-auto mt-2">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="font-medium opacity-60">Amount</td>
                                <td class="text-right font-mono font-bold text-lg">₱{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Category</td>
                                <td class="text-right">{{ ucfirst(str_replace('_', ' ', $payment->category)) }}</td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Type</td>
                                <td class="text-right">{{ ucfirst(str_replace('_', ' ', $payment->type)) }}</td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Reference No.</td>
                                <td class="text-right font-mono">{{ $payment->reference_no ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Payment Date</td>
                                <td class="text-right">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('F d, Y') : '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body">
                <h3 class="card-title text-lg flex items-center gap-2">
                    <span>📄</span> Bill
                </h3>
                @if($payment->bill)
                    <p class="font-semibold">
                        <a href="{{ route('bills.show', $payment->bill) }}" class="link link-primary">
                            Bill #{{ $payment->bill->id }}
                        </a>
                    </p>
                    <p class="text-sm opacity-70 mt-1">{{ $payment->bill->employer->name ?? 'No employer' }}</p>
                @else
                    <p class="opacity-40">No bill linked</p>
                @endif
            </div>
        </div>
    </div>

    @if($payment->officialReceipt)
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title text-lg flex items-center gap-2 mb-4">
                <span>🧾</span> Official Receipt
            </h3>
            <div class="overflow-x-auto">
                <table class="table">
                    <tbody>
                        <tr>
                            <td class="font-medium opacity-60">OR No.</td>
                            <td class="font-mono">{{ $payment->officialReceipt->or_no }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium opacity-60">Amount</td>
                            <td class="font-mono">₱{{ number_format($payment->officialReceipt->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium opacity-60">Issued To</td>
                            <td>{{ $payment->officialReceipt->issued_to_name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($payment->notes)
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title text-lg flex items-center gap-2 mb-2">
                <span>📝</span> Notes
            </h3>
            <p class="opacity-70">{{ $payment->notes }}</p>
        </div>
    </div>
    @endif

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body flex-row items-center justify-between py-4">
            <div class="flex items-center gap-4">
                @php
                    $statusColors = [
                        'pending' => 'badge-warning',
                        'confirmed' => 'badge-success',
                        'failed' => 'badge-error',
                        'refunded' => 'badge-info',
                    ];
                @endphp
                <span class="text-sm opacity-60">Status:</span>
                <span class="badge badge-lg {{ $statusColors[$payment->status] ?? 'badge-ghost' }}">
                    {{ ucfirst($payment->status) }}
                </span>
            </div>
            <form action="{{ route('payments.destroy', $payment) }}" method="POST"
                  onsubmit="return confirm('Delete this payment?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-sm text-error">
                    🗑️ Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
