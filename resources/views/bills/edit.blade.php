@extends('layouts.app')

@section('title', 'Edit Bill #' . $bill->id)

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('bills.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Bills
        </a>
    </div>

    <div class="card bg-gradient-to-br from-amber-500/10 to-orange-500/10 border border-amber-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>✏️</span> Edit Bill #{{ $bill->id }}
        </h2>
        <p class="opacity-60 text-sm mt-1">Update costs, deposits, and status</p>
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

    <form action="{{ route('bills.update', $bill) }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf
            @method('PUT')

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏢 Employer <span class="text-error">*</span></legend>
                <select name="employer_id" required class="select w-full">
                    <option value="">Select employer...</option>
                    @foreach($employers as $employer)
                        <option value="{{ $employer->id }}" @selected(old('employer_id', $bill->employer_id) == $employer->id)>
                            {{ $employer->name }}
                        </option>
                    @endforeach
                </select>
                @error('employer_id') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">👤 Applicant</legend>
                <select name="applicant_id" class="select w-full">
                    <option value="">Select applicant (optional)...</option>
                    @foreach($applicants as $applicant)
                        <option value="{{ $applicant->id }}" @selected(old('applicant_id', $bill->applicant_id) == $applicant->id)>
                            {{ $applicant->full_name }}
                        </option>
                    @endforeach
                </select>
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💰 Employer Cost <span class="text-error">*</span></legend>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 opacity-50">₱</span>
                        <input type="number" name="employer_cost" value="{{ old('employer_cost', $bill->employer_cost) }}"
                            required step="0.01" min="0" class="input w-full pl-8" placeholder="0.00">
                    </div>
                    @error('employer_cost') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💵 Applicant Cost</legend>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 opacity-50">₱</span>
                        <input type="number" name="applicant_cost" value="{{ old('applicant_cost', $bill->applicant_cost) }}"
                            step="0.01" min="0" class="input w-full pl-8" placeholder="0.00">
                    </div>
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🏦 Employer Deposit</legend>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 opacity-50">₱</span>
                        <input type="number" name="employer_deposit" value="{{ old('employer_deposit', $bill->employer_deposit) }}"
                            step="0.01" min="0" class="input w-full pl-8" placeholder="0.00">
                    </div>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🏦 Applicant Deposit</legend>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 opacity-50">₱</span>
                        <input type="number" name="applicant_deposit" value="{{ old('applicant_deposit', $bill->applicant_deposit) }}"
                            step="0.01" min="0" class="input w-full pl-8" placeholder="0.00">
                    </div>
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📊 Status</legend>
                <select name="status" class="select w-full">
                    <option value="pending" @selected(old('status', $bill->status) === 'pending')>⏳ Pending</option>
                    <option value="less" @selected(old('status', $bill->status) === 'less')>⚠️ Less</option>
                    <option value="partially_paid" @selected(old('status', $bill->status) === 'partially_paid')>🔄 Partially Paid</option>
                    <option value="paid" @selected(old('status', $bill->status) === 'paid')>✅ Paid</option>
                    <option value="over_paid" @selected(old('status', $bill->status) === 'over_paid')>🔄 Over Paid</option>
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📝 Notes</legend>
                <textarea name="notes" class="textarea w-full" rows="3" placeholder="Optional notes...">{{ old('notes', $bill->notes) }}</textarea>
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Update Bill
                </button>
                <a href="{{ route('bills.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
