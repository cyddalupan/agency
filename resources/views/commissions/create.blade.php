@extends('layouts.app')

@section('title', 'Record Commission')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('commissions.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Commissions
        </a>
    </div>

    <div class="card bg-gradient-to-br from-purple-500/10 to-violet-500/10 border border-purple-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>➕</span> Record Commission
        </h2>
        <p class="opacity-60 text-sm mt-1">Create a new commission entry</p>
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

    <form action="{{ route('commissions.store') }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏢 Employer</legend>
                <select name="employer_id" class="select w-full">
                    <option value="">Select employer (optional)...</option>
                    @foreach($employers as $employer)
                        <option value="{{ $employer->id }}" @selected(old('employer_id') == $employer->id)>
                            {{ $employer->name }}
                        </option>
                    @endforeach
                </select>
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💰 Amount <span class="text-error">*</span></legend>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 opacity-50">₱</span>
                        <input type="number" name="amount" value="{{ old('amount') }}" required
                            step="0.01" min="0.01" class="input w-full pl-8" placeholder="0.00">
                    </div>
                    @error('amount') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💵 Paid Amount</legend>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 opacity-50">₱</span>
                        <input type="number" name="paid_amount" value="{{ old('paid_amount', 0) }}"
                            step="0.01" min="0" class="input w-full pl-8" placeholder="0.00">
                    </div>
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📊 Status</legend>
                    <select name="status" class="select w-full">
                        <option value="pending" @selected(old('status', 'pending') === 'pending')>⏳ Pending</option>
                        <option value="partial" @selected(old('status') === 'partial')>🔄 Partial</option>
                        <option value="paid" @selected(old('status') === 'paid')>✅ Paid</option>
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📅 Due Date</legend>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="input w-full">
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏷️ Commission Source</legend>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <select name="commissionable_type" class="select w-full">
                        <option value="">Select type...</option>
                        <option value="marketing_agency" @selected(old('commissionable_type') === 'marketing_agency')>Marketing Agency</option>
                        <option value="marketing_agent" @selected(old('commissionable_type') === 'marketing_agent')>Marketing Agent</option>
                        <option value="recruitment_agent" @selected(old('commissionable_type') === 'recruitment_agent')>Recruitment Agent</option>
                    </select>
                    <input type="number" name="commissionable_id" value="{{ old('commissionable_id') }}"
                        class="input w-full" placeholder="Source ID">
                </div>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📝 Notes</legend>
                <textarea name="notes" class="textarea w-full" rows="3" placeholder="Optional notes...">{{ old('notes') }}</textarea>
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Record Commission
                </button>
                <a href="{{ route('commissions.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
