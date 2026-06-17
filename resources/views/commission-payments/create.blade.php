@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-lg">
    <div class="mb-6">
        <a href="{{ route('commissions.commission-payments.index', $commission) }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to Payments</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-bold mb-2">Record Commission Payment</h1>
        <p class="text-sm text-gray-500 mb-6">
            Commission #{{ $commission->id }} &mdash; 
            Balance: &#8369;{{ number_format($commission->balance, 2) }}
        </p>

        <form action="{{ route('commissions.commission-payments.store', $commission) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="amount" class="block text-sm font-medium mb-1">Amount *</label>
                <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                    value="{{ old('amount') }}"
                    class="w-full border rounded-lg px-3 py-2 @error('amount') border-red-500 @enderror"
                    required>
                @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="payment_date" class="block text-sm font-medium mb-1">Payment Date *</label>
                <input type="date" name="payment_date" id="payment_date"
                    value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                    class="w-full border rounded-lg px-3 py-2 @error('payment_date') border-red-500 @enderror"
                    required>
                @error('payment_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="reference_no" class="block text-sm font-medium mb-1">Reference No.</label>
                <input type="text" name="reference_no" id="reference_no"
                    value="{{ old('reference_no') }}"
                    class="w-full border rounded-lg px-3 py-2 @error('reference_no') border-red-500 @enderror">
                @error('reference_no') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium mb-1">Notes</label>
                <textarea name="notes" id="notes" rows="3"
                    class="w-full border rounded-lg px-3 py-2 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Record Payment
            </button>
        </form>
    </div>
</div>
@endsection
