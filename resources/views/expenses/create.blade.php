@extends('layouts.app')

@section('title', 'Create Request')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('expenses.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Expenses and Payments
        </a>
    </div>

    <div class="card bg-gradient-to-br from-red-500/10 to-orange-500/10 border border-red-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>💸</span> Create Request
        </h2>
        <p class="opacity-60 text-sm mt-1">Log a payment out of the agency, classified under an account.</p>
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

    <form action="{{ route('expenses.store') }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📂 Account <span class="text-error">*</span></legend>
                <select name="account_id" class="select w-full" required>
                    <option value="">— Select account —</option>
                    @foreach($accounts as $main)
                        <optgroup label="{{ $main->name }}">
                            @if($main->children->count())
                                @foreach($main->children as $sub)
                                    <option value="{{ $sub->id }}" @selected(old('account_id') == $sub->id)>{{ $sub->name }}</option>
                                @endforeach
                            @else
                                <option value="{{ $main->id }}" @selected(old('account_id') == $main->id)>{{ $main->name }}</option>
                            @endif
                        </optgroup>
                    @endforeach
                </select>
                @error('account_id') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💰 Amount (₱) <span class="text-error">*</span></legend>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required
                        class="input w-full" placeholder="0.00">
                    @error('amount') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📅 Date <span class="text-error">*</span></legend>
                    <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required class="input w-full">
                    @error('date') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏢 Payee</legend>
                <input type="text" name="payee" value="{{ old('payee') }}" class="input w-full" placeholder="Who was paid? e.g. Meralco">
                @error('payee') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💳 Payment Method</legend>
                    <select name="method" class="select w-full">
                        @foreach(['cash','bank_transfer','check','gcash','online'] as $m)
                            <option value="{{ $m }}" @selected(old('method', 'cash') === $m)>{{ ucwords(str_replace('_',' ',$m)) }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🔖 Reference No.</legend>
                    <input type="text" name="reference_no" value="{{ old('reference_no') }}" class="input w-full" placeholder="OR # or bank ref">
                    @error('reference_no') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📝 Notes</legend>
                <textarea name="notes" rows="3" class="textarea w-full" placeholder="Optional context">{{ old('notes') }}</textarea>
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Create Request
                </button>
                <a href="{{ route('expenses.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
