@extends('layouts.app')

@section('title', 'OR ' . $officialReceipt->or_no)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card bg-gradient-to-br from-amber-600 via-amber-500/90 to-yellow-600 text-white shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('official-receipts.index') }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1 mb-2">
                <span>←</span> Back to Official Receipts
            </a>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-5xl">🧾</span>
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold">{{ $officialReceipt->or_no }}</h2>
                        <p class="opacity-80 text-sm mt-1">
                            ₱{{ number_format($officialReceipt->amount, 2) }}
                            · Issued to {{ $officialReceipt->issued_to_name }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('reports.or', $officialReceipt) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
                        📄 PDF
                    </a>
                    <a href="{{ route('official-receipts.edit', $officialReceipt) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
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
                    <span>🧾</span> Receipt Details
                </h3>
                <div class="overflow-x-auto mt-2">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="font-medium opacity-60">OR No.</td>
                                <td class="text-right font-mono font-bold">{{ $officialReceipt->or_no }}</td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Amount</td>
                                <td class="text-right font-mono font-bold text-lg">₱{{ number_format($officialReceipt->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Issue Date</td>
                                <td class="text-right">{{ \Carbon\Carbon::parse($officialReceipt->issue_date)->format('F d, Y') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body">
                <h3 class="card-title text-lg flex items-center gap-2">
                    <span>👤</span> Issued To
                </h3>
                <div class="overflow-x-auto mt-2">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="font-medium opacity-60">Name</td>
                                <td class="text-right">{{ $officialReceipt->issued_to_name }}</td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Type</td>
                                <td class="text-right">
                                    @php
                                        $typeLabels = ['employer' => '🏢', 'applicant' => '👤', 'agent' => '🤝'];
                                    @endphp
                                    {{ $typeLabels[$officialReceipt->issued_to] ?? '' }} {{ ucfirst($officialReceipt->issued_to) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($officialReceipt->payment)
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title text-lg flex items-center gap-2 mb-4">
                <span>💳</span> Related Payment
            </h3>
            <div class="overflow-x-auto">
                <table class="table">
                    <tbody>
                        <tr>
                            <td class="font-medium opacity-60">Payment</td>
                            <td>
                                <a href="{{ route('payments.show', $officialReceipt->payment) }}" class="link link-primary">
                                    Payment #{{ $officialReceipt->payment->id }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-medium opacity-60">Amount</td>
                            <td class="font-mono">₱{{ number_format($officialReceipt->payment->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium opacity-60">Status</td>
                            <td>{{ ucfirst($officialReceipt->payment->status) }}</td>
                        </tr>
                        @if($officialReceipt->payment->bill && $officialReceipt->payment->bill->employer)
                        <tr>
                            <td class="font-medium opacity-60">Bill Employer</td>
                            <td>{{ $officialReceipt->payment->bill->employer->name }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($officialReceipt->notes)
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title text-lg flex items-center gap-2 mb-2">
                <span>📝</span> Notes
            </h3>
            <p class="opacity-70">{{ $officialReceipt->notes }}</p>
        </div>
    </div>
    @endif

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body flex-row items-center justify-between py-4">
            <span class="text-sm opacity-60">Created {{ $officialReceipt->created_at->format('M d, Y \a\t g:i A') }}</span>
            <form action="{{ route('official-receipts.destroy', $officialReceipt) }}" method="POST"
                  onsubmit="return confirm('Delete this official receipt?')">
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
