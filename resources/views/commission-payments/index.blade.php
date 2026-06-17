@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Commission Payments</h1>
            <p class="text-sm text-gray-500">
                Commission #{{ $commission->id }} &mdash; 
                &#8369;{{ number_format($commission->amount, 2) }} 
                <span class="ml-2 px-2 py-0.5 text-xs rounded-full
                    @if($commission->status === 'paid') bg-green-100 text-green-700
                    @elseif($commission->status === 'partial') bg-yellow-100 text-yellow-700
                    @else bg-gray-100 text-gray-700
                    @endif">
                    {{ ucfirst($commission->status) }}
                </span>
            </p>
        </div>
        <a href="{{ route('commissions.commission-payments.create', $commission) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
            + Record Payment
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total Commission</p>
            <p class="text-xl font-bold">&#8369;{{ number_format($commission->amount, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Total Paid</p>
            <p class="text-xl font-bold">&#8369;{{ number_format($commission->paid_amount, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-{{ $commission->balance > 0 ? 'yellow' : 'green' }}-500">
            <p class="text-sm text-gray-500">Balance</p>
            <p class="text-xl font-bold">&#8369;{{ number_format($commission->balance, 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50">
            <h2 class="font-semibold">Payment History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($payments as $payment)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $payment->payment_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-right font-medium">&#8369;{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm">{{ $payment->reference_no ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">{{ $payment->notes ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                <a href="{{ route('commissions.commission-payments.edit', [$commission, $payment]) }}" class="text-blue-600 hover:text-blue-800 mr-2">Edit</a>
                                <form action="{{ route('commissions.commission-payments.destroy', [$commission, $payment]) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Delete this payment record?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-400">No payments recorded yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('commissions.index') }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to Commissions</a>
    </div>
</div>
@endsection
