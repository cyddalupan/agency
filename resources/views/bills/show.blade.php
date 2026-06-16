@extends('layouts.app')

@section('title', 'Bill #' . $bill->id)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card bg-gradient-to-br from-blue-600 via-blue-500/90 to-indigo-600 text-white shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('bills.index') }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1 mb-2">
                <span>←</span> Back to Bills
            </a>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-5xl">📄</span>
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold">Bill #{{ $bill->id }}</h2>
                        <p class="opacity-80 text-sm mt-1">
                            {{ $bill->employer->name ?? 'No employer' }}
                            @if($bill->applicant)
                                · {{ $bill->applicant->full_name }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('bills.edit', $bill) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
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
                    <span>🏢</span> Employer
                </h3>
                @if($bill->employer)
                    <p class="font-semibold">{{ $bill->employer->name }}</p>
                    @if($bill->employer->contact_person)
                        <p class="text-sm opacity-60">👤 {{ $bill->employer->contact_person }}</p>
                    @endif
                @else
                    <p class="opacity-40 text-sm">Not specified</p>
                @endif
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body">
                <h3 class="card-title text-lg flex items-center gap-2">
                    <span>👤</span> Applicant
                </h3>
                @if($bill->applicant)
                    <p class="font-semibold">{{ $bill->applicant->full_name }}</p>
                @else
                    <p class="opacity-40 text-sm">Not specified</p>
                @endif
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title text-lg flex items-center gap-2 mb-4">
                <span>💰</span> Costs & Deposits
            </h3>
            <div class="overflow-x-auto">
                <table class="table">
                    <tbody>
                        <tr>
                            <td class="font-medium">💰 Employer Cost</td>
                            <td class="text-right font-mono">₱{{ number_format($bill->employer_cost, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium">💵 Applicant Cost</td>
                            <td class="text-right font-mono">₱{{ number_format($bill->applicant_cost, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium">🏦 Employer Deposit</td>
                            <td class="text-right font-mono">₱{{ number_format($bill->employer_deposit, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium">🏦 Applicant Deposit</td>
                            <td class="text-right font-mono">₱{{ number_format($bill->applicant_deposit, 2) }}</td>
                        </tr>
                        <tr class="font-bold">
                            <td>Total</td>
                            <td class="text-right font-mono">
                                ₱{{ number_format($bill->employer_cost + $bill->applicant_cost + $bill->employer_deposit + $bill->applicant_deposit, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($bill->payments->count())
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title text-lg flex items-center gap-2 mb-4">
                <span>💳</span> Payments
            </h3>
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bill->payments as $payment)
                        <tr>
                            <td class="font-mono">₱{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->category)) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->type)) }}</td>
                            <td>
                                <span class="badge badge-sm badge-ghost">{{ $payment->status }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($bill->notes)
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title text-lg flex items-center gap-2 mb-2">
                <span>📝</span> Notes
            </h3>
            <p class="opacity-70">{{ $bill->notes }}</p>
        </div>
    </div>
    @endif

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body flex-row items-center justify-between py-4">
            <div class="flex items-center gap-4">
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
                <span class="text-sm opacity-60">Status:</span>
                <span class="badge badge-lg {{ $statusColors[$bill->status] ?? 'badge-ghost' }}">
                    {{ $statusIcons[$bill->status] ?? '' }} {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                </span>
            </div>
            <form action="{{ route('bills.destroy', $bill) }}" method="POST"
                  onsubmit="return confirm('Delete this bill?')">
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
