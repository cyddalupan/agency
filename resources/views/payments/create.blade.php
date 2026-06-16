@extends('layouts.app')

@section('title', 'Record Payment')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('payments.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Payments
        </a>
    </div>

    <div class="card bg-gradient-to-br from-green-500/10 to-emerald-500/10 border border-green-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>➕</span> Record Payment
        </h2>
        <p class="opacity-60 text-sm mt-1">Log a payment against a bill</p>
    </div>

    @if($errors->any())
        <div role="alert" class="alert alert-error mb-6 shadow-sm">
            <span>❌</span>
            <ul class="list-disc pl-4 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('payments.store') }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📄 Bill</legend>
                <select name="bill_id" class="select w-full">
                    <option value="">Select bill (optional)...</option>
                    @foreach($bills as $bill)
                        <option value="{{ $bill->id }}" @selected(old('bill_id') == $bill->id)>
                            #{{ $bill->id }} · {{ $bill->employer->name ?? 'No employer' }} · ₱{{ number_format($bill->employer_cost, 2) }}
                        </option>
                    @endforeach
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">💰 Amount <span class="text-error">*</span></legend>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 opacity-50">₱</span>
                    <input type="number" name="amount" value="{{ old('amount') }}" required
                        step="0.01" min="0.01" class="input w-full pl-8" placeholder="0.00">
                </div>
                @error('amount') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🏷️ Category <span class="text-error">*</span></legend>
                    <select name="category" required class="select w-full">
                        <option value="">Select category...</option>
                        <option value="employer_cost" @selected(old('category') === 'employer_cost')>Employer Cost</option>
                        <option value="applicant_cost" @selected(old('category') === 'applicant_cost')>Applicant Cost</option>
                        <option value="deposit" @selected(old('category') === 'deposit')>Deposit</option>
                        <option value="commission" @selected(old('category') === 'commission')>Commission</option>
                    </select>
                    @error('category') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💳 Type</legend>
                    <select name="type" class="select w-full">
                        <option value="">Select type...</option>
                        <option value="cash" @selected(old('type') === 'cash')>Cash</option>
                        <option value="bank_transfer" @selected(old('type') === 'bank_transfer')>Bank Transfer</option>
                        <option value="check" @selected(old('type') === 'check')>Check</option>
                        <option value="gcash" @selected(old('type') === 'gcash')>GCash</option>
                        <option value="online" @selected(old('type') === 'online')>Online</option>
                    </select>
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📇 Reference No.</legend>
                    <input type="text" name="reference_no" value="{{ old('reference_no') }}"
                        class="input w-full" placeholder="e.g. OR-001, GCash-XXXX">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📅 Payment Date</legend>
                    <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}"
                        class="input w-full">
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📊 Status</legend>
                <select name="status" class="select w-full">
                    <option value="pending" @selected(old('status', 'pending') === 'pending')>⏳ Pending</option>
                    <option value="confirmed" @selected(old('status') === 'confirmed')>✅ Confirmed</option>
                    <option value="failed" @selected(old('status') === 'failed')>❌ Failed</option>
                    <option value="refunded" @selected(old('status') === 'refunded')>🔄 Refunded</option>
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📝 Notes</legend>
                <textarea name="notes" class="textarea w-full" rows="3" placeholder="Optional notes...">{{ old('notes') }}</textarea>
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Record Payment
                </button>
                <a href="{{ route('payments.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
