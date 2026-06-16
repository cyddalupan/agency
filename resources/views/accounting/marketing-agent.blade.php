@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="{{ route('marketing-agencies.index') }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to Marketing Agencies</a>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $marketingAgent->name }}</h1>
            <p class="text-sm text-gray-500">Marketing Agent Accounting</p>
        </div>
        <div class="text-right">
            <span class="text-lg font-semibold {{ $balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                Balance: &#8369;{{ number_format($balance, 2) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total Commissions</p>
            <p class="text-xl font-bold">&#8369;{{ number_format($totalCommissions, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Total Paid</p>
            <p class="text-xl font-bold">&#8369;{{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-{{ $balance > 0 ? 'yellow' : 'green' }}-500">
            <p class="text-sm text-gray-500">Balance</p>
            <p class="text-xl font-bold">&#8369;{{ number_format($balance, 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50">
            <h2 class="font-semibold">Commissions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employer</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($commissions as $commission)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $commission->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm">{{ $commission->employer->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm text-right">&#8369;{{ number_format($commission->amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right">&#8369;{{ number_format($commission->paid_amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right">&#8369;{{ number_format($commission->balance, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($commission->status === 'paid') bg-green-100 text-green-700
                                    @elseif($commission->status === 'pending') bg-yellow-100 text-yellow-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ ucfirst($commission->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-400">No commissions found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
