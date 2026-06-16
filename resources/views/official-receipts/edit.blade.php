@extends('layouts.app')

@section('title', 'Edit ' . $officialReceipt->or_no)

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('official-receipts.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Official Receipts
        </a>
    </div>

    <div class="card bg-gradient-to-br from-amber-500/10 to-orange-500/10 border border-amber-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>✏️</span> Edit {{ $officialReceipt->or_no }}
        </h2>
        <p class="opacity-60 text-sm mt-1">Update official receipt details</p>
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

    <form action="{{ route('official-receipts.update', $officialReceipt) }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf
            @method('PUT')

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🧾 OR No. <span class="text-error">*</span></legend>
                <input type="text" name="or_no" value="{{ old('or_no', $officialReceipt->or_no) }}" required
                    class="input w-full font-mono" placeholder="e.g. OR-2026-0001">
                @error('or_no') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">💳 Payment</legend>
                <select name="payment_id" class="select w-full">
                    <option value="">Select payment (optional)...</option>
                    @foreach($payments as $payment)
                        <option value="{{ $payment->id }}" @selected(old('payment_id', $officialReceipt->payment_id) == $payment->id)>
                            Payment #{{ $payment->id }} · ₱{{ number_format($payment->amount, 2) }}
                            @if($payment->bill && $payment->bill->employer)
                                · {{ $payment->bill->employer->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">💰 Amount <span class="text-error">*</span></legend>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 opacity-50">₱</span>
                    <input type="number" name="amount" value="{{ old('amount', $officialReceipt->amount) }}" required
                        step="0.01" min="0.01" class="input w-full pl-8" placeholder="0.00">
                </div>
                @error('amount') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📅 Issue Date <span class="text-error">*</span></legend>
                <input type="date" name="issue_date"
                    value="{{ old('issue_date', \Carbon\Carbon::parse($officialReceipt->issue_date)->format('Y-m-d')) }}"
                    required class="input w-full">
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏷️ Issued To Type <span class="text-error">*</span></legend>
                <select name="issued_to" required class="select w-full">
                    <option value="employer" @selected(old('issued_to', $officialReceipt->issued_to) === 'employer')>Employer</option>
                    <option value="applicant" @selected(old('issued_to', $officialReceipt->issued_to) === 'applicant')>Applicant</option>
                    <option value="agent" @selected(old('issued_to', $officialReceipt->issued_to) === 'agent')>Agent</option>
                </select>
                @error('issued_to') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">👤 Issued To Name <span class="text-error">*</span></legend>
                <input type="text" name="issued_to_name" value="{{ old('issued_to_name', $officialReceipt->issued_to_name) }}" required
                    class="input w-full" placeholder="Full name">
                @error('issued_to_name') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📝 Notes</legend>
                <textarea name="notes" class="textarea w-full" rows="3" placeholder="Optional notes...">{{ old('notes', $officialReceipt->notes) }}</textarea>
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Update Receipt
                </button>
                <a href="{{ route('official-receipts.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
